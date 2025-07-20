<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// 1. Validasi Akses & Ambil Data
// ===================================
if (!isset($_SESSION['customer_id'])) {
    set_sweetalert('warning', 'Akses Ditolak!', 'Anda harus login untuk melakukan pembayaran.');
    header('Location: login_customer.php');
    exit();
}

$id_pesanan = (int)($_GET['id'] ?? 0);
if ($id_pesanan === 0) {
    header("Location: riwayat_pesanan.php");
    exit();
}

$id_pelanggan = $_SESSION['customer_id'];

// Ambil data pesanan, pastikan milik pelanggan yang login & statusnya valid
// Kita juga mengambil data pelanggan untuk notifikasi WA
$sql_pesanan = "SELECT p.*, py.metode_bayar, py.status_bayar, u.nama_lengkap, u.telepon
                FROM pesanan p
                JOIN pembayaran py ON p.id_pesanan = py.id_pesanan
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                WHERE p.id_pesanan = $id_pesanan AND p.id_pengguna = $id_pelanggan";
$result_pesanan = $koneksi->query($sql_pesanan);

if ($result_pesanan->num_rows === 0) {
    set_sweetalert('error', 'Tidak Ditemukan!', 'Pesanan tidak ditemukan atau bukan milik Anda.');
    header("Location: riwayat_pesanan.php");
    exit();
}
$pesanan = $result_pesanan->fetch_assoc();

// Jika status bukan menunggu pembayaran, tidak bisa diakses
if ($pesanan['status_pesanan'] !== 'menunggu_pembayaran') {
    set_sweetalert('info', 'Info', 'Pesanan ini sudah diproses atau dibayar.');
    header("Location: riwayat_pesanan.php");
    exit();
}

// 2. Proses Upload Bukti Bayar
// ===================================
if (isset($_POST['upload_bukti'])) {
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bukti_bayar'];
        $upload_dir = 'admin/assets/images/bukti_bayar/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2 MB

        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $nama_file = uniqid() . '-' . basename($file['name']);
            $path_tujuan = $upload_dir . $nama_file;

            if (move_uploaded_file($file['tmp_name'], $path_tujuan)) {
                $koneksi->begin_transaction();
                try {
                    // Update tabel pembayaran
                    $sql_update_bayar = "UPDATE pembayaran SET bukti_bayar = '$nama_file', tanggal_bayar = NOW(), status_bayar = 'success' WHERE id_pesanan = $id_pesanan";
                    if (!$koneksi->query($sql_update_bayar)) throw new Exception("Gagal update pembayaran.");
                    
                    // Update status pesanan menjadi 'diproses' karena pembayaran sudah diterima
                    $sql_update_pesanan = "UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = $id_pesanan";
                    if (!$koneksi->query($sql_update_pesanan)) throw new Exception("Gagal update status pesanan.");

                    $koneksi->commit();
                    
                    // --- MULAI BLOK NOTIFIKASI WHATSAPP ---
                    $meta_toko = $koneksi->query("SELECT nama_instansi FROM meta WHERE id_meta = 1")->fetch_assoc();
                    $nama_toko = $meta_toko['nama_instansi'] ?? 'Toko Anda';
                    
                    $pesan_wa = "Halo " . sanitize($pesanan['nama_lengkap']) . ",\n\n" .
                                "Konfirmasi pembayaran untuk pesanan *#{$pesanan['nomor_pesanan']}* telah kami terima. ✅\n\n" .
                                "Tim kami akan segera melakukan verifikasi. Anda akan menerima notifikasi selanjutnya setelah pesanan Anda kami siapkan untuk pengiriman.\n\n" .
                                "Terima kasih telah berbelanja di *" . $nama_toko . "*!";
                                
                    // Kirim notifikasi ke pelanggan
                    kirim_wa($pesanan['telepon'], $pesan_wa);
                    // --- SELESAI BLOK NOTIFIKASI WHATSAPP ---

                    set_sweetalert('success', 'Upload Berhasil!', 'Terima kasih, pembayaran Anda akan segera kami verifikasi.');
                    header("Location: riwayat_pesanan.php");
                    exit();

                } catch (Exception $e) {
                    $koneksi->rollback();
                    set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan pada database.');
                }
            } else {
                set_sweetalert('error', 'Gagal!', 'Gagal memindahkan file yang di-upload.');
            }
        } else {
            set_sweetalert('error', 'Gagal!', 'File tidak valid. Pastikan file adalah gambar (JPG/PNG) dan ukuran kurang dari 2MB.');
        }
    } else {
        set_sweetalert('error', 'Gagal!', 'Anda belum memilih file untuk di-upload.');
    }
    header("Location: pembayaran.php?id=$id_pesanan");
    exit();
}

// Ambil data toko
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
$kategori_list_nav = $koneksi->query("SELECT nama_kategori, slug FROM kategori_produk ORDER BY nama_kategori ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Konfirmasi Pembayaran - <?= sanitize($meta['nama_instansi'] ?? 'Toko Online'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <link rel="stylesheet" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">

    <link rel="stylesheet" href="vendor/venobox/venobox.min.css">
    <link rel="stylesheet" href="vendor/slickslider/slick.min.css">
    <link rel="stylesheet" href="vendor/niceselect/nice-select.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">

    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/orderlist.css">
    <script src="admin/assets/sweetalert2@11.js"></script>
</head>
<body>
    <header class="header-part">
        <div class="container">
            <div class="header-content">
                <div class="header-media-group">
                    <button class="header-user"><img src="images/user.png" alt="user"></button>
                    <a href="index.php"><img src="images/logo.png" alt="logo"></a>
                    <button class="header-src"><i class="fas fa-search"></i></button>
                </div>
                <a class="header-logo" href="index.php"><img src="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>" alt="logo"></a>
                <a class="header-widget" href="<?= isset($_SESSION['customer_id']) ? 'akun_saya.php' : 'login_customer.php' ?>" title="Akun Saya">
                    <img src="images/user.png" alt="user">
                    <span><?= isset($_SESSION['customer_id']) ? sanitize(explode(' ', $_SESSION['customer_nama'])[0]) : 'Join'; ?></span>
                </a>
                <form class="header-form" method="GET" action="index.php">
                    <input type="text" name="q" placeholder="Cari produk..." value="<?= sanitize($_GET['q'] ?? ''); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div class="header-widget-group">
                    <a class="header-widget" href="keranjang.php" title="Keranjang"><i class="fas fa-shopping-basket"></i><sup><?= count($_SESSION['cart'] ?? []); ?></sup></a> 
                </div>
            </div>
        </div>
    </header>

    <nav class="navbar-part">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar-content">
                        <ul class="navbar-list">
                            <li class="navbar-item"><a class="navbar-link" href="index.php">Beranda</a></li>
                            <li class="navbar-item dropdown">
                                <a class="navbar-link dropdown-arrow" href="#">Kategori</a>
                                <ul class="dropdown-position-list">
                                    <?php if ($kategori_list_nav->num_rows > 0): while($kategori = $kategori_list_nav->fetch_assoc()): ?>
                                        <li><a href="index.php?kategori=<?= $kategori['slug']; ?>"><?= sanitize($kategori['nama_kategori']); ?></a></li>
                                    <?php endwhile; else: ?>
                                        <li><a href="#">Tidak ada kategori</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                             <li class="navbar-item dropdown">
                                <a class="navbar-link dropdown-arrow" href="#">Akun</a>
                                <ul class="dropdown-position-list">
                                    <?php if (isset($_SESSION['customer_id'])): ?>
                                        <li><a href="akun_saya.php">Akun Saya</a></li>
                                        <li><a href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                        <li><a href="logout_customer.php">Logout</a></li>
                                    <?php else: ?>
                                        <li><a href="login_customer.php">Login</a></li>
                                        <li><a href="register_customer.php">Register</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        </ul>
                        <div class="navbar-info-group">
                            <div class="navbar-info"><i class="icofont-ui-touch-phone"></i><p><small>call us</small><span><?= sanitize($meta['telepon'] ?? 'N/A'); ?></span></p></div>
                            <div class="navbar-info"><i class="icofont-ui-email"></i><p><small>email us</small><span><?= sanitize($meta['email'] ?? 'N/A'); ?></span></p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <section class="inner-section single-banner" style="background: url(admin/assets/bgcover.jpg) no-repeat center;">
        <div class="container"><h2>Konfirmasi Pembayaran</h2></div>
    </section>

    <section class="inner-section invoice-part">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-info"><p>Selesaikan pembayaran Anda untuk pesanan <strong>#<?= sanitize($pesanan['nomor_pesanan']); ?></strong></p></div>
                </div>
                <div class="col-lg-6">
                    <div class="account-card">
                        <div class="account-title"><h4>Instruksi Pembayaran</h4></div>
                        <div class="account-content">
                            <div class="invoice-details">
                                <p>Silakan lakukan transfer sejumlah <strong><?= rupiah($pesanan['total_final']); ?></strong> ke salah satu rekening berikut:</p>
                                <ul class="mt-3">
                                    <li><strong>Bank BCA:</strong> 1234-5678-90 a.n. <?= sanitize($meta['nama_instansi']); ?></li>
                                    <li><strong>Bank Mandiri:</strong> 098-765-4321 a.n. <?= sanitize($meta['nama_instansi']); ?></li>
                                </ul>
                                <p class="mt-3">Setelah melakukan transfer, mohon unggah bukti pembayaran pada formulir di samping.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="account-card">
                        <div class="account-title"><h4>Upload Bukti Pembayaran</h4></div>
                        <div class="account-content">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="form-label">Pilih file (JPG/PNG, maks 2MB)</label>
                                    <input type="file" name="bukti_bayar" class="form-control" required>
                                </div>
                                <div class="form-button">
                                    <button type="submit" name="upload_bukti" class="btn btn-inline">Kirim Konfirmasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-part">
        <div class="container">
            <div class="row"><div class="col-12"><div class="footer-bottom"><p class="footer-copytext">© All Copyrights Reserved by <a href="#"><?= sanitize($meta['nama_instansi'] ?? 'Tim Anda'); ?></a></p></div></div></div>
        </div>
    </footer>
    <script src="vendor/bootstrap/jquery-1.12.4.min.js"></script>
    <script src="vendor/bootstrap/popper.min.js"></script>
    <script src="vendor/bootstrap/bootstrap.min.js"></script>
    <script src="vendor/countdown/countdown.min.js"></script>
    <script src="vendor/niceselect/nice-select.min.js"></script>
    <script src="vendor/slickslider/slick.min.js"></script>
    <script src="vendor/venobox/venobox.min.js"></script>

    <script src="js/nice-select.js"></script>
    <script src="js/countdown.js"></script>
    <script src="js/accordion.js"></script>
    <script src="js/venobox.js"></script>
    <script src="js/slick.js"></script>
    <script src="js/main.js"></script> 
    <?php show_sweetalert(); ?>
</body>
</html>