<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// 1. Validasi Akses & Ambil Data
if (!isset($_SESSION['customer_id'])) {
    header('Location: login_customer.php');
    exit();
}
$id_pesanan_detail = (int)($_GET['id'] ?? 0);
$id_pelanggan = $_SESSION['customer_id'];

// Ambil detail produk yang akan diulas, pastikan milik user yang login dan statusnya 'selesai'
$sql = "SELECT pd.id_pesanan_detail, p.nama_produk, p.gambar_produk 
        FROM pesanan_detail pd 
        JOIN produk p ON pd.id_produk = p.id_produk
        JOIN pesanan ps ON pd.id_pesanan = ps.id_pesanan
        WHERE pd.id_pesanan_detail = $id_pesanan_detail AND ps.id_pengguna = $id_pelanggan AND ps.status_pesanan = 'selesai'";
$result = $koneksi->query($sql);
if ($result->num_rows === 0) {
    set_sweetalert('error', 'Gagal!', 'Produk tidak valid untuk diulas.');
    header("Location: riwayat_pesanan.php");
    exit();
}
$item = $result->fetch_assoc();

// Cek apakah produk ini sudah pernah diulas
$cek_ulasan_sql = "SELECT id_ulasan FROM ulasan WHERE id_pesanan_detail = $id_pesanan_detail";
$hasil_cek = $koneksi->query($cek_ulasan_sql);
if ($hasil_cek->num_rows > 0) {
    set_sweetalert('info', 'Info', 'Anda sudah pernah memberikan ulasan untuk produk ini.');
    header("Location: riwayat_pesanan.php");
    exit();
}
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Beri Ulasan - <?= sanitize($meta['nama_instansi'] ?? 'Toko Anda'); ?></title>

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
    <link rel="stylesheet" href="css/product-details.css"> <style>
        .star-rating{display:inline-flex;flex-direction:row-reverse;}.star-rating input{display:none}.star-rating label{font-size:2rem;color:#ddd;cursor:pointer;padding:0 3px;transition:color .2s}.star-rating input:checked~label,.star-rating label:hover,.star-rating label:hover~label{color:#f90}body{background:#f5f5f5}
    </style>
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

    <section class="inner-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-details-frame"> 
                        <h3 class="frame-title text-center">Beri Ulasan Anda</h3>
                        <hr>
                        <div class="row align-items-center">
                            <div class="col-md-5 text-center">
                                <img src="admin/assets/images/produk/<?= sanitize($item['gambar_produk']); ?>" alt="product" class="img-fluid rounded" style="max-height: 250px;">
                                <h5 class="mt-3"><?= sanitize($item['nama_produk']); ?></h5>
                                <p><a href="riwayat_pesanan.php">&larr; Kembali ke Riwayat Pesanan</a></p>
                            </div>
                            <div class="col-md-7">
                                <form class="review-form" action="ulasan_proses.php" method="POST">
                                    <input type="hidden" name="id_pesanan_detail" value="<?= $item['id_pesanan_detail']; ?>">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group text-center">
                                                <div class="star-rating">
                                                    <input type="radio" name="rating" id="star-5" value="5" required/><label for="star-5" title="Sangat Baik">★</label>
                                                    <input type="radio" name="rating" id="star-4" value="4" /><label for="star-4" title="Baik">★</label>
                                                    <input type="radio" name="rating" id="star-3" value="3" /><label for="star-3" title="Cukup">★</label>
                                                    <input type="radio" name="rating" id="star-2" value="2" /><label for="star-2" title="Kurang">★</label>
                                                    <input type="radio" name="rating" id="star-1" value="1" /><label for="star-1" title="Buruk">★</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <textarea class="form-control" name="komentar" rows="5" placeholder="Bagaimana kualitas produk ini? Apakah sesuai dengan deskripsi?"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button class="btn btn-inline w-100">
                                                <i class="icofont-water-drop"></i>
                                                <span>Kirim Ulasan Anda</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>  
                            </div>
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
    <!-- VENDOR -->
    <script src="vendor/bootstrap/jquery-1.12.4.min.js"></script>
    <script src="vendor/bootstrap/popper.min.js"></script>
    <script src="vendor/bootstrap/bootstrap.min.js"></script>
    <script src="vendor/countdown/countdown.min.js"></script>
    <script src="vendor/niceselect/nice-select.min.js"></script>
    <script src="vendor/slickslider/slick.min.js"></script>
    <script src="vendor/venobox/venobox.min.js"></script>

    <!-- CUSTOM -->
    <script src="js/nice-select.js"></script>
    <script src="js/countdown.js"></script>
    <script src="js/accordion.js"></script>
    <script src="js/venobox.js"></script>
    <script src="js/slick.js"></script>
    <script src="js/main.js"></script> 
    <?php show_sweetalert(); ?>
</body>
</html>