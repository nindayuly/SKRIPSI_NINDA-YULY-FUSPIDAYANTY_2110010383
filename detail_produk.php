<?php
// NINDA/detail_produk.php (Template Baru - dengan input Qty number)
session_start();

// Memuat file koneksi dan helper
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// --- Ambil Data dari Database ---

// 1. Validasi dan ambil ID produk dari URL
$id_produk = (int)($_GET['id'] ?? 0);
if ($id_produk === 0) {
    set_sweetalert('error', 'Gagal!', 'Produk tidak valid.');
    header("Location: index.php");
    exit();
}

// 2. Ambil data meta (info toko, kontak, dll)
$sql_meta = "SELECT * FROM meta WHERE id_meta = 1";
$result_meta = $koneksi->query($sql_meta);
$meta = $result_meta ? $result_meta->fetch_assoc() : [];

// 3. Ambil data kategori produk untuk menu navbar
$sql_kategori_nav = "SELECT * FROM kategori_produk ORDER BY nama_kategori ASC";
$result_kategori_nav = $koneksi->query($sql_kategori_nav);
$kategori_list_nav = [];
if ($result_kategori_nav) {
    while($row = $result_kategori_nav->fetch_assoc()) {
        $kategori_list_nav[] = $row;
    }
}

// 4. Ambil data produk yang dipilih
$sql_produk = "SELECT p.*, k.nama_kategori, k.slug AS kategori_slug 
               FROM produk p 
               LEFT JOIN kategori_produk k ON p.id_kategori = k.id_kategori 
               WHERE p.id_produk = $id_produk";
$result_produk = $koneksi->query($sql_produk);

if ($result_produk->num_rows === 0) {
    set_sweetalert('error', 'Gagal!', 'Produk tidak ditemukan.');
    header("Location: index.php");
    exit();
}
$produk = $result_produk->fetch_assoc();

// 5. Ambil data ulasan untuk produk ini
$sql_ulasan = "SELECT u.*, pg.nama_lengkap 
               FROM ulasan u 
               JOIN pengguna pg ON u.id_pengguna = pg.id_pengguna
               WHERE u.id_produk = '$id_produk' 
               ORDER BY u.tanggal_ulasan DESC";
$result_ulasan = $koneksi->query($sql_ulasan);
$ulasan_list = [];
if($result_ulasan) {
    while($row = $result_ulasan->fetch_assoc()){
        $ulasan_list[] = $row;
    }
}

// 6. Ambil produk terkait (dalam kategori yang sama)
$id_kategori_produk = $produk['id_kategori'];
$sql_terkait = "SELECT * FROM produk WHERE id_kategori = $id_kategori_produk AND id_produk != $id_produk LIMIT 5";
$result_terkait = $koneksi->query($sql_terkait);
$produk_terkait_list = [];
if($result_terkait) {
    while($row = $result_terkait->fetch_assoc()){
        $produk_terkait_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= sanitize($produk['nama_produk']); ?> - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <!-- FONTS -->
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
    <link rel="stylesheet" href="css/product-details.css">
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
    
    <section class="inner-section single-banner" style="background: url(admin/assets/bgcover.jpg) no-repeat center;">
        <div class="container">
            <h2>Detail Produk</h2> 
        </div>
    </section>

    <section class="inner-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="details-gallery">
                        <ul class="details-preview">
                            <?php 
                                $gambar_path = 'admin/assets/images/produk/' . sanitize($produk['gambar_produk']);
                                $gambar_path = (empty($produk['gambar_produk']) || !file_exists($gambar_path)) ? 'admin/assets/images/produk/default.png' : $gambar_path;
                            ?>
                            <li><img src="<?= $gambar_path; ?>" alt="<?= sanitize($produk['nama_produk']); ?>"></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="details-content">
                        <h3 class="details-name"><a href="#"><?= sanitize($produk['nama_produk']); ?></a></h3>
                        <div class="details-meta">
                            <p>KODE:<span><?= sanitize($produk['kode_produk']); ?></span></p>
                            <p>MERK:<a href="#"><?= sanitize($produk['merk']); ?></a></p>
                        </div>
                        <h3 class="details-price"><span><?= format_rupiah($produk['harga_jual']); ?></span></h3>
                        <p class="details-desc"><?= sanitize(limit_words($produk['deskripsi'], 30)); ?></p>
                        
                        <form method="GET" action="keranjang_proses.php">
                            <input type="hidden" name="aksi" value="tambah">
                            <input type="hidden" name="id" value="<?= $produk['id_produk']; ?>">
                            <div class="details-add-group">
                                <div class="form-group">
                                    <input class="form-control" type="number" name="kuantitas" value="1" min="1" max="<?= $produk['stok']; ?>">
                                </div>
                                <button type="submit" class="product-add" title="Tambah ke Keranjang">
                                    <i class="fas fa-shopping-basket"></i>
                                    <span>tambah ke keranjang</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="inner-section">
        <div class="container">
            <div class="row"><div class="col-lg-12"><ul class="nav nav-tabs">
                <li><a href="#tab-desc" class="tab-link active" data-bs-toggle="tab">Deskripsi</a></li>
                <li><a href="#tab-spec" class="tab-link" data-bs-toggle="tab">Spesifikasi</a></li>
                <li><a href="#tab-reve" class="tab-link" data-bs-toggle="tab">Ulasan (<?= count($ulasan_list); ?>)</a></li>
            </ul></div></div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-desc"><div class="row"><div class="col-lg-12"><div class="product-details-frame"><div class="tab-descrip"><p><?= nl2br(sanitize($produk['deskripsi'])); ?></p></div></div></div></div></div>
                <div class="tab-pane fade" id="tab-spec"><div class="row"><div class="col-lg-12"><div class="product-details-frame"><table class="table table-bordered"><tbody>
                    <tr><th scope="row">Kode Produk</th><td><?= sanitize($produk['kode_produk']); ?></td></tr>
                    <tr><th scope="row">Merk</th><td><?= sanitize($produk['merk']); ?></td></tr>
                    <tr><th scope="row">Berat</th><td><?= sanitize($produk['berat_gram']); ?> gram</td></tr>
                    <tr><th scope="row">Kondisi</th><td><?= sanitize($produk['kondisi']); ?></td></tr>
                    <tr><th scope="row">Stok</th><td><?= sanitize($produk['stok']); ?></td></tr>
                </tbody></table></div></div></div></div>
                <div class="tab-pane fade" id="tab-reve"><div class="row"><div class="col-lg-12"><div class="product-details-frame">
                    <ul class="review-list">
                        <?php if (!empty($ulasan_list)): foreach($ulasan_list as $ulasan): ?>
                        <li class="review-item"><div class="review-media"><a class="review-avatar" href="#"><img src="images/avatar/01.jpg" alt="review"></a><h5 class="review-meta"><a href="#"><?= sanitize($ulasan['nama_lengkap']); ?></a><span><?= date('F d, Y', strtotime($ulasan['tanggal_ulasan'])); ?></span></h5></div><ul class="review-rating"><?php for($i=0; $i < 5; $i++): echo ($i < $ulasan['rating']) ? '<li class="icofont-ui-rating"></li>' : '<li class="icofont-ui-rate-blank"></li>'; endfor; ?></ul><p class="review-desc"><?= sanitize($ulasan['komentar']); ?></p></li>
                        <?php endforeach; else: ?><li class="review-item"><p>Belum ada ulasan untuk produk ini.</p></li><?php endif; ?>
                    </ul>
                </div></div></div></div>
            </div>
        </div>
    </section>

    <?php if(!empty($produk_terkait_list)): ?>
    <section class="inner-section">
        <div class="container">
            <div class="row"><div class="col"><div class="section-heading"><h2>Produk Terkait</h2></div></div></div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                <?php foreach($produk_terkait_list as $terkait): ?>
                <div class="col">
                    <div class="product-card">
                        <div class="product-media"><a class="product-image" href="detail_produk.php?id=<?= $terkait['id_produk']; ?>">
                            <img src="admin/assets/images/produk/<?= sanitize($terkait['gambar_produk']); ?>" alt="<?= sanitize($terkait['nama_produk']); ?>">
                        </a></div>
                        <div class="product-content">
                            <h6 class="product-name"><a href="detail_produk.php?id=<?= $terkait['id_produk']; ?>"><?= sanitize($terkait['nama_produk']); ?></a></h6>
                            <h6 class="product-price"><span><?= format_rupiah($terkait['harga_jual']); ?></span></h6>
                            <a href="keranjang_proses.php?aksi=tambah&id=<?= $terkait['id_produk']; ?>" class="product-add" title="Tambah ke Keranjang"><i class="fas fa-shopping-basket"></i><span>add</span></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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