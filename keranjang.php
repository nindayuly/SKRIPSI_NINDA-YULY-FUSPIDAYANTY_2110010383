<?php
// NINDA/keranjang.php (Disederhanakan, tanpa form kupon)
session_start();

require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Ambil data meta dan kategori
$sql_meta = "SELECT * FROM meta WHERE id_meta = 1";
$result_meta = $koneksi->query($sql_meta);
$meta = $result_meta ? $result_meta->fetch_assoc() : [];

$sql_kategori_nav = "SELECT * FROM kategori_produk ORDER BY nama_kategori ASC";
$result_kategori_nav = $koneksi->query($sql_kategori_nav);
$kategori_list_nav = [];
if ($result_kategori_nav) {
    while($row = $result_kategori_nav->fetch_assoc()) {
        $kategori_list_nav[] = $row;
    }
}

// Inisialisasi keranjang
$cart = $_SESSION['cart'] ?? [];
$total_belanja = 0;
foreach ($cart as $item) {
    $total_belanja += $item['harga'] * $item['kuantitas'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Keranjang Belanja - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
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
                                    <?php if (!empty($kategori_list)): foreach ($kategori_list as $kategori): ?>
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
            <h2>Keranjang Belanja</h2> 
        </div>
    </section>

    <section class="inner-section cart-part">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-scroll">
                        <form action="keranjang_proses.php?aksi=update" method="POST">
                            <table class="table-list">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Produk</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Harga</th>
                                        <th scope="col">Kuantitas</th>
                                        <th scope="col">Subtotal</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($cart)): $i = 1; foreach ($cart as $id_produk => $item): ?>
                                    <tr>
                                        <td class="table-serial"><h6><?= $i++; ?></h6></td>
                                        <td class="table-image"><img src="admin/assets/images/produk/<?= sanitize($item['gambar']); ?>" alt="<?= sanitize($item['nama_produk']); ?>"></td>
                                        <td class="table-name"><h6><?= sanitize($item['nama_produk']); ?></h6></td>
                                        <td class="table-price"><span><?= format_rupiah($item['harga']); ?></span></td>
                                        <td class="table-quantity"><input class="form-control text-center" type="number" name="kuantitas[<?= $id_produk ?>]" value="<?= $item['kuantitas']; ?>" min="1"></td>
                                        <td class="table-price"><span><?= format_rupiah($item['harga'] * $item['kuantitas']); ?></span></td>
                                        <td class="table-action"><a class="trash" href="keranjang_proses.php?aksi=hapus&id=<?= $id_produk; ?>" title="Hapus Item"><i class="icofont-trash"></i></a></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="7" class="text-center p-5"><p class="fs-4">Keranjang belanja Anda kosong.</p><a href="index.php" class="btn btn-inline mt-3">Mulai Belanja</a></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (!empty($cart)): ?>
                            <div class="cart-action-group mt-3">
                                <a class="action-btn" href="index.php"><i class="icofont-arrow-left"></i>Lanjut Belanja</a>
                                <button type="submit" class="action-btn"><i class="icofont-refresh"></i>Perbarui Keranjang</button>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <?php if (!empty($cart)): ?>
            <div class="row">
                <div class="col-lg-12">
                     <div class="checkout-charge mt-5">
                        <ul>
                            <li><span>Total Belanja</span><span><?= format_rupiah($total_belanja); ?></span></li>
                        </ul>
                        <a href="checkout.php" class="btn btn-inline">Lanjut ke Checkout</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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