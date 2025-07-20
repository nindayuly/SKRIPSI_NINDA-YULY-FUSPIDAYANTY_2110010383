<?php
// NINDA/checkout.php
session_start();

require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// 1. Validasi Akses Halaman
if (!isset($_SESSION['customer_id'])) {
    set_sweetalert('warning', 'Akses Ditolak!', 'Anda harus login untuk melanjutkan.');
    header('Location: login_customer.php');
    exit();
}
if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

$id_pelanggan = $_SESSION['customer_id'];

// 2. Aksi Hapus Kupon
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_kupon') {
    unset($_SESSION['kupon']);
    set_sweetalert('success', 'Berhasil', 'Kupon telah dihapus.');
    header('Location: checkout.php');
    exit();
}

// 3. Ambil Data Awal
$sql_pelanggan = "SELECT * FROM pengguna WHERE id_pengguna = $id_pelanggan";
$result_pelanggan = $koneksi->query($sql_pelanggan);
$pelanggan = $result_pelanggan->fetch_assoc();

$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();

// Opsi pengiriman statis
$opsi_pengiriman = [
    'reguler' => ['nama' => 'Reguler', 'biaya' => 30000, 'estimasi' => '3 Hari'],
    'cepat'   => ['nama' => 'Cepat',   'biaya' => 25000, 'estimasi' => '2 Hari'],
    'expres'  => ['nama' => 'Expres',  'biaya' => 50000, 'estimasi' => '1 Hari'],
];

// 4. Hitung Total Belanja
$cart = $_SESSION['cart'];
$total_belanja = 0;
foreach ($cart as $item) {
    $total_belanja += $item['harga'] * $item['kuantitas'];
}

// 5. Proses Pengajuan Kupon
if (isset($_POST['apply_coupon'])) {
    $kode_kupon = $koneksi->real_escape_string($_POST['kode_kupon']);
    $sql_kupon = "SELECT * FROM promosi WHERE kode_promo = '$kode_kupon' AND status_aktif = 1 AND tgl_mulai <= NOW() AND tgl_berakhir >= NOW() AND kuota_penggunaan > 0";
    $result_kupon = $koneksi->query($sql_kupon);

    if ($result_kupon && $result_kupon->num_rows > 0) {
        $kupon = $result_kupon->fetch_assoc();
        if ($total_belanja >= $kupon['min_pembelian']) {
            $_SESSION['kupon'] = $kupon;
            set_sweetalert('success', 'Berhasil!', 'Kupon berhasil digunakan.');
        } else {
            unset($_SESSION['kupon']);
            set_sweetalert('error', 'Gagal!', 'Minimal belanja ' . rupiah($kupon['min_pembelian']) . ' untuk kupon ini.');
        }
    } else {
        unset($_SESSION['kupon']);
        set_sweetalert('error', 'Gagal!', 'Kupon tidak valid atau sudah kedaluwarsa.');
    }
    header('Location: checkout.php');
    exit();
}

// 6. Hitung Diskon & Finalisasi Total
$diskon = 0;
$pesan_kupon = '';
if (isset($_SESSION['kupon'])) {
    $kupon = $_SESSION['kupon'];
    if ($total_belanja >= $kupon['min_pembelian']) {
        $diskon = ($kupon['tipe_diskon'] == 'percentage') ? ($kupon['nilai_diskon'] / 100) * $total_belanja : $kupon['nilai_diskon'];
        $pesan_kupon = "Kupon '" . sanitize($kupon['kode_promo']) . "' diterapkan.";
    } else {
        unset($_SESSION['kupon']);
        $diskon = 0;
    }
}

// 7. Proses Buat Pesanan (Place Order)
if (isset($_POST['place_order'])) {
    $nama_penerima = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $telepon_penerima = $koneksi->real_escape_string($_POST['telepon']);
    $email_penerima = $koneksi->real_escape_string($_POST['email']);
    $alamat_kirim = $koneksi->real_escape_string($_POST['alamat']);
    $catatan_pelanggan = $koneksi->real_escape_string($_POST['catatan']);
    $biaya_kirim = (int)($_POST['biaya_kirim'] ?? 0);
    $metode_bayar = $koneksi->real_escape_string($_POST['metode_pembayaran']);

    $total_final = $total_belanja - $diskon + $biaya_kirim;

    if (empty($nama_penerima) || empty($telepon_penerima) || empty($alamat_kirim) || $biaya_kirim === 0) {
        set_sweetalert('error', 'Gagal!', 'Pastikan semua data pengiriman dan metode pengiriman sudah diisi.');
        header('Location: checkout.php');
        exit();
    }

    $koneksi->begin_transaction();
    try {
        $nomor_pesanan = generate_kode_otomatis($koneksi, 'INV', 'pesanan', 'nomor_pesanan');
        $id_promosi_val = isset($_SESSION['kupon']['id_promosi']) ? "'" . $_SESSION['kupon']['id_promosi'] . "'" : "NULL";

        $sql_pesanan = "INSERT INTO pesanan (nomor_pesanan, id_pengguna, id_promosi, total_harga_produk, nilai_diskon, biaya_kirim, total_final, alamat_kirim, catatan_pelanggan, status_pesanan) 
                        VALUES ('$nomor_pesanan', '$id_pelanggan', $id_promosi_val, '$total_belanja', '$diskon', '$biaya_kirim', '$total_final', '$alamat_kirim', '$catatan_pelanggan', 'menunggu_pembayaran')";
        if (!$koneksi->query($sql_pesanan)) throw new Exception("Gagal menyimpan data pesanan.");
        $id_pesanan_baru = $koneksi->insert_id;
        
        $sql_pembayaran = "INSERT INTO pembayaran (id_pesanan, metode_bayar, jumlah_bayar, status_bayar) VALUES ('$id_pesanan_baru', '$metode_bayar', '$total_final', 'pending')";
        if (!$koneksi->query($sql_pembayaran)) throw new Exception("Gagal menyimpan data pembayaran.");

        foreach ($cart as $id_produk_item => $item) {
            $subtotal = $item['harga'] * $item['kuantitas'];
            $kuantitas = $item['kuantitas'];
            $harga_saat_pesan = $item['harga'];
            $sql_detail = "INSERT INTO pesanan_detail (id_pesanan, id_produk, jumlah, harga_saat_pesan, subtotal) VALUES ('$id_pesanan_baru', '$id_produk_item', '$kuantitas', '$harga_saat_pesan', '$subtotal')";
            if (!$koneksi->query($sql_detail)) throw new Exception("Gagal menyimpan detail pesanan.");
            $sql_update_stok = "UPDATE produk SET stok = stok - $kuantitas WHERE id_produk = $id_produk_item";
            if (!$koneksi->query($sql_update_stok)) throw new Exception("Gagal memperbarui stok.");
        }

        if (isset($_SESSION['kupon']['id_promosi'])) {
            $koneksi->query("UPDATE promosi SET kuota_penggunaan = kuota_penggunaan - 1 WHERE id_promosi = {$_SESSION['kupon']['id_promosi']}");
        }

        $koneksi->commit();
        
        // --- MULAI BLOK NOTIFIKASI WHATSAPP ---
        $nama_toko = $meta['nama_instansi'] ?? 'Toko Anda';
        $pesan_wa = "Halo " . sanitize($nama_penerima) . ",\n\n" .
                    "Terima kasih telah berbelanja di *" . $nama_toko . "*! 🙏\n\n" .
                    "Pesanan Anda dengan nomor *#$nomor_pesanan* senilai *" . rupiah($total_final) . "* telah kami terima segera lakukan pembayaran sehingga pesanan anda dapat kami proses.\n\n" .
                    "Terima kasih!";
                    
        // Kirim notifikasi ke pelanggan (pastikan nomor telepon diawali 62)
        kirim_wa($telepon_penerima, $pesan_wa);
        // --- SELESAI BLOK NOTIFIKASI WHATSAPP ---

        unset($_SESSION['cart'], $_SESSION['kupon']);
        set_sweetalert('success', 'Pesanan Berhasil!', 'Pesanan Anda dengan nomor ' . $nomor_pesanan . ' telah kami terima.');
        header('Location: riwayat_pesanan.php');
        exit();

    } catch (Exception $e) {
        $koneksi->rollback();
        // set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage()); // Untuk debugging
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat memproses pesanan.');
        header('Location: checkout.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Checkout - <?= sanitize($meta['nama_instansi'] ?? 'Toko Online'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <link rel="stylesheet" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">

    <link rel="stylesheet" href="vendor/venobox/venobox.min.css">
    <link rel="stylesheet" href="vendor/slickslider/slick.min.css">
    <link rel="stylesheet" href="vendor/niceselect/nice-select.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">

    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/checkout.css">
    <script src="admin/assets/sweetalert2@11.js"></script>
</head>
<body>
    <div class="backdrop"></div>
    <a class="backtop fas fa-arrow-up" href="#"></a>
    
    <header class="header-part">
        <div class="container">
            <div class="header-content">
                <div class="header-media-group">
                    <button class="header-user"><img src="images/user.png" alt="user"></button>
                    <a href="index.php"><img src="images/logo.png" alt="logo"></a>
                    <button class="header-src"><i class="fas fa-search"></i></button>
                </div>

                <a class="header-logo" href="index.php">
                    <img src="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>" alt="logo">
                </a>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a class="header-widget" href="akun_saya.php" title="Akun Saya">
                        <img src="images/user.png" alt="user">
                        <span>Halo, <?= sanitize(explode(' ', $_SESSION['customer_nama'])[0]); ?></span>
                    </a>
                <?php else: ?>
                     <a class="header-widget" href="login_customer.php" title="Login / Register">
                        <img src="images/user.png" alt="user">
                        <span>Login / Register</span>
                    </a>
                <?php endif; ?>
                <form class="header-form" method="GET" action="index.php">
                    <input type="text" name="q" placeholder="Cari produk..." value="<?= sanitize($_GET['q'] ?? ''); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>

                <div class="header-widget-group">
                    <a class="header-widget" href="keranjang.php" title="Keranjang">
                        <i class="fas fa-shopping-basket"></i>
                        <sup><?= count($_SESSION['cart'] ?? []); ?></sup>
                    </a> 
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
                                    <?php 
                                    $kategori_list_result = $koneksi->query("SELECT nama_kategori, slug FROM kategori_produk ORDER BY nama_kategori ASC");
                                    if ($kategori_list_result->num_rows > 0): 
                                        while($kategori = $kategori_list_result->fetch_assoc()): ?>
                                            <li><a href="index.php?kategori=<?= $kategori['slug']; ?>"><?= sanitize($kategori['nama_kategori']); ?></a></li>
                                        <?php endwhile; 
                                    else: ?>
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
        <div class="container"><h2>Checkout</h2></div>
    </section>

    <section class="inner-section checkout-part">
        <div class="container">
            <form method="POST" class="checkout-form">
            <div class="row">

                <div class="col-lg-12">
                    <div class="account-card">
                        <div class="account-title"><h4>Ringkasan Pesanan</h4></div>
                        <div class="account-content">
                            <div class="table-scroll">
                                <table class="table-list">
                                    <thead><tr><th scope="col">#</th><th scope="col">Produk</th><th scope="col">Nama</th><th scope="col">Harga</th><th scope="col">Qty</th><th scope="col">Total</th></tr></thead>
                                    <tbody>
                                        <?php $i=1; foreach($cart as $item): ?>
                                        <tr>
                                            <td class="table-serial"><h6><?= $i++; ?></h6></td>
                                            <td class="table-image"><img src="admin/assets/images/produk/<?= sanitize($item['gambar']); ?>" alt="product"></td>
                                            <td class="table-name"><h6><?= sanitize($item['nama_produk']); ?></h6></td>
                                            <td class="table-price"><h6><?= rupiah($item['harga']); ?></h6></td>
                                            <td class="table-brand"><h6><?= $item['kuantitas']; ?></h6></td>
                                            <td class="table-quantity"><h6><?= rupiah($item['harga'] * $item['kuantitas']); ?></h6></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="checkout-charge">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="chekout-coupon">
                                            <?php if (!isset($_SESSION['kupon'])): ?>
                                                <button type="button" class="coupon-btn">Punya kode kupon?</button>
                                                <form class="coupon-form" method="POST">
                                                    <input type="text" name="kode_kupon" placeholder="Masukkan kode kupon">
                                                    <button type="submit" name="apply_coupon"><span>Terapkan</span></button>
                                                </form>
                                            <?php else: ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <p class="text-success mb-0"><?= $pesan_kupon ?></p>
                                                    <a href="checkout.php?aksi=hapus_kupon" class="text-danger" style="text-decoration: underline;">Hapus Kupon</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-7"> 
                                        <h6>Pilih Metode Pengiriman</h6>
                                        <div class="form-group" hidden><input type="text" name="metode_pembayaran" class="form-check-input" id="transfer" readonly value="transfer" required>
                                            <label for="transfer" class="form-check-label">Transfer Bank</label></div>
                                        <?php foreach($opsi_pengiriman as $kode => $opsi): ?>
                                        <div class="form-group"><input type="radio" name="opsi_kirim" class="form-check-input" id="<?= $kode; ?>" value="<?= $opsi['biaya']; ?>" required><label for="<?= $kode; ?>" class="form-check-label"><?= $opsi['nama']; ?> (Estimasi <?= $opsi['estimasi']; ?>) - <?= rupiah($opsi['biaya']); ?></label></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="col-md-5">
                                        <ul>
                                            <li><span>Subtotal</span><span><?= rupiah($total_belanja); ?></span></li>
                                            <li><span>Diskon</span><span>- <?= rupiah($diskon); ?></span></li>
                                            <li><span>Biaya Kirim</span><span id="biaya-kirim-teks">Rp 0</span></li>
                                            <li><span><strong>Total</strong></span><strong><span id="grand-total"><?= rupiah($total_belanja - $diskon); ?></span></strong></li>
                                        </ul> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="account-card">
                        <div class="account-title"><h4>Kontak & Alamat Pengiriman</h4></div>
                        <div class="account-content">
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Nama Lengkap</label><input class="form-control" type="text" name="nama_lengkap" value="<?= sanitize($pelanggan['nama_lengkap']); ?>" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= sanitize($pelanggan['email']); ?>" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Nomor Telepon</label><input class="form-control" type="text" name="telepon" value="<?= sanitize($pelanggan['telepon']); ?>" required></div></div>
                                <div class="col-12"><div class="form-group"><label class="form-label">Alamat Lengkap</label><textarea class="form-control" name="alamat" required><?= sanitize($pelanggan['alamat']); ?></textarea></div></div>
                                <div class="col-12"><div class="form-group"><label class="form-label">Catatan Tambahan (Opsional)</label><textarea class="form-control" name="catatan"></textarea></div></div>
                            </div>
                        </div>
                        
                        <div class="checkout-proced">
                            <input type="hidden" name="biaya_kirim" id="biaya_kirim_input" value="0">
                            <button type="submit" name="place_order" class="btn btn-inline">Buat Pesanan</button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalBelanja = <?= (float)$total_belanja; ?>;
            const diskon = <?= (float)$diskon; ?>;
            const opsiKirimRadio = document.querySelectorAll('input[name="opsi_kirim"]');
            const biayaKirimTeks = document.getElementById('biaya-kirim-teks');
            const grandTotalTeks = document.getElementById('grand-total');
            const biayaKirimInput = document.getElementById('biaya_kirim_input');
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

            // Menangani tombol kupon
            const couponBtn = document.querySelector('.coupon-btn');
            const couponForm = document.querySelector('.coupon-form');
            if(couponBtn) {
                couponBtn.addEventListener('click', function(){
                    couponBtn.style.display = 'none';
                    couponForm.style.display = 'flex';
                });
            }

            function updateTotal(biayaKirim = 0) {
                const grandTotal = totalBelanja - diskon + biayaKirim;
                biayaKirimTeks.textContent = formatter.format(biayaKirim);
                grandTotalTeks.textContent = formatter.format(grandTotal);
                biayaKirimInput.value = biayaKirim;
            }

            opsiKirimRadio.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        updateTotal(parseInt(this.value));
                    }
                });
            });
        });
    </script>
    <?php show_sweetalert(); ?>
</body>
</html>