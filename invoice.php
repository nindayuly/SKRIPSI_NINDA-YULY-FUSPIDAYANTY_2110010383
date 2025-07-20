<?php
// NINDA/invoice.php (Diperbaiki dengan Fitur Ulasan di Tabel)
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// 1. Validasi Akses & Ambil ID
if (!isset($_SESSION['customer_id'])) {
    set_sweetalert('warning', 'Akses Ditolak!', 'Anda harus login untuk melihat invoice.');
    header('Location: login_customer.php');
    exit();
}

$id_pesanan = (int)($_GET['id'] ?? 0);
if ($id_pesanan === 0) {
    set_sweetalert('error', 'Gagal!', 'Nomor pesanan tidak valid.');
    header("Location: riwayat_pesanan.php");
    exit();
}

$id_pelanggan = $_SESSION['customer_id'];

// 2. Ambil Data dari Database
// Ambil data pesanan utama, pastikan pesanan ini milik pelanggan yang login
$sql_pesanan = "SELECT p.*, py.metode_bayar, u.nama_lengkap AS nama_pelanggan 
                FROM pesanan p
                LEFT JOIN pembayaran py ON p.id_pesanan = py.id_pesanan
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                WHERE p.id_pesanan = $id_pesanan AND p.id_pengguna = $id_pelanggan";
$result_pesanan = $koneksi->query($sql_pesanan);
if ($result_pesanan->num_rows === 0) {
    set_sweetalert('error', 'Tidak Ditemukan!', 'Pesanan tidak ditemukan atau bukan milik Anda.');
    header("Location: riwayat_pesanan.php");
    exit();
}
$pesanan = $result_pesanan->fetch_assoc();

// **PERBAIKAN QUERY SQL DETAIL**
// Ambil detail item yang dipesan dan cek status ulasannya
$sql_detail = "SELECT 
                   pd.id_pesanan_detail, pd.id_produk, pd.harga_saat_pesan, pd.jumlah, pd.subtotal,
                   pr.nama_produk, pr.merk, pr.gambar_produk,
                   u.id_ulasan, u.rating, u.komentar 
               FROM pesanan_detail pd 
               JOIN produk pr ON pd.id_produk = pr.id_produk
               LEFT JOIN ulasan u ON pd.id_pesanan_detail = u.id_pesanan_detail
               WHERE pd.id_pesanan = $id_pesanan";
$result_detail = $koneksi->query($sql_detail);
$detail_items = [];
if ($result_detail) {
    while ($row = $result_detail->fetch_assoc()) {
        $detail_items[] = $row;
    }
}

// Ambil data toko
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Invoice #<?= sanitize($pesanan['nomor_pesanan']); ?> - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">

    <link rel="stylesheet" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">

    <!-- VENDOR -->
    <link rel="stylesheet" href="vendor/venobox/venobox.min.css">
    <link rel="stylesheet" href="vendor/slickslider/slick.min.css">
    <link rel="stylesheet" href="vendor/niceselect/nice-select.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">

    <!-- CUSTOM -->
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
                                    <?php if (!empty($kategori_list_nav)): foreach ($kategori_list_nav as $kategori): ?>
                                        <li><a href="index.php?kategori=<?= $kategori['slug']; ?>"><?= sanitize($kategori['nama_kategori']); ?></a></li>
                                    <?php endforeach; else: ?>
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

    <br><br>
    

    <section class="inner-section invoice-part">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert-info"><p>Terima kasih! Ini adalah rincian lengkap pesanan Anda.</p></div>
                </div>
                <div class="col-lg-12">
                    <div class="account-card">
                        <div class="account-title"><h4>Detail Invoice</h4></div>
                        <div class="account-content">
                            <div class="invoice-recieved">
                                <h6>Nomor Pesanan <span><?= sanitize($pesanan['nomor_pesanan']); ?></span></h6>
                                <h6>Tanggal Pesanan <span><?= tgl_indo($pesanan['tanggal_pesanan']); ?></span></h6>
                                <h6>Total Pembayaran <span><?= format_rupiah($pesanan['total_final']); ?></span></h6>
                                <h6>Metode Pembayaran <span><?= sanitize(ucwords(str_replace('_', ' ', $pesanan['metode_bayar']))); ?></span></h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="account-card">
                        <div class="account-title"><h4>Daftar Produk Dipesan</h4></div>
                        <div class="account-content">
                            <div class="table-scroll">
                                <table class="table-list">
                                    <thead>
                                        <tr><th scope="col">#</th><th scope="col">Produk</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($detail_items as $item): ?>
                                        <tr>
                                            <td class="table-serial"><h6><?= $i++; ?></h6></td>
                                            <td class="table-name">
                                                <h6><?= sanitize($item['nama_produk']); ?></h6>
                                                <?php if ($pesanan['status_pesanan'] == 'selesai'): ?>
                                                <div class="invoice-review">
                                                    <?php if(is_null($item['id_ulasan'])): ?>
                                                        <p class="text-muted small">Anda belum memberikan ulasan untuk produk ini.</p>
                                                        <a href="ulasan.php?id=<?= $item['id_pesanan_detail']; ?>" class="btn btn-sm btn-outline">Beri Ulasan Sekarang</a>
                                                    <?php else: ?>
                                                        <p class="small mb-1"><strong>Ulasan Anda:</strong></p>
                                                        <p class="rating-display">
                                                            <?php for($s=1; $s<=5; $s++): ?>
                                                                <i class="icofont-star<?= ($s <= $item['rating']) ? '' : '-alt'; ?>"></i>
                                                            <?php endfor; ?>
                                                        </p>
                                                        <p class="text-muted small fst-italic">"<?= sanitize($item['komentar']); ?>"</p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="table-price"><h6><?= format_rupiah($item['harga_saat_pesan']); ?></h6></td>
                                            <td class="table-quantity"><h6>x<?= $item['jumlah']; ?></h6></td>
                                            <td class="table-brand text-end"><h6><?= format_rupiah($item['subtotal']); ?></h6></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 text-center mt-4">
                    <a class="btn btn-inline" href="invoice_cetak.php?id=<?= $pesanan['id_pesanan']; ?>" target="_blank"><i class="icofont-download"></i><span>Cetak Invoice</span></a>
                    <div class="back-home mt-3"><a href="riwayat_pesanan.php">Kembali ke Riwayat Pesanan</a></div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-part">
        <div class="container">
            <div class="row"><div class="col-12"><div class="footer-bottom">
                <p class="footer-copytext">&copy; All Copyrights Reserved by <a href="#"><?= sanitize($meta['nama_instansi'] ?? 'Tim Anda'); ?></a></p>
            </div></div></div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>