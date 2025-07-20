<?php
// NINDA/index.php (Template Baru - dengan Pagination)
session_start();

// Memuat file koneksi dan helper
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// --- Ambil Data dari Database ---

// 1. Data meta (info toko, kontak, dll)
$sql_meta = "SELECT * FROM meta WHERE id_meta = 1";
$result_meta = $koneksi->query($sql_meta);
$meta = $result_meta ? $result_meta->fetch_assoc() : [];

// 2. Data kategori produk
$sql_kategori = "SELECT * FROM kategori_produk ORDER BY nama_kategori ASC";
$result_kategori = $koneksi->query($sql_kategori);
$kategori_list = [];
if ($result_kategori) {
    while($row = $result_kategori->fetch_assoc()) {
        $kategori_list[] = $row;
    }
}

// 3. Data merk/brand
$sql_merk = "SELECT DISTINCT merk FROM produk WHERE merk IS NOT NULL AND merk != '' ORDER BY merk ASC";
$result_merk = $koneksi->query($sql_merk);
$merk_list = [];
if ($result_merk) {
    while ($row = $result_merk->fetch_assoc()) {
        $merk_list[] = $row['merk'];
    }
}

// --- Logika Pagination & Filter ---
$limit = 8; // Jumlah produk per halaman
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$kategori_filter = $_GET['kategori'] ?? null;
$merk_filter = $_GET['merk'] ?? null;
$search_query = $_GET['q'] ?? null;

$base_query = "FROM produk p LEFT JOIN kategori_produk k ON p.id_kategori = k.id_kategori WHERE 1";
$params = [];
$types = '';

if ($kategori_filter) {
    $base_query .= " AND k.slug = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}
if ($merk_filter) {
    $base_query .= " AND p.merk = ?";
    $params[] = $merk_filter;
    $types .= 's';
}
if ($search_query) {
    $base_query .= " AND p.nama_produk LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= 's';
}

// Hitung total produk untuk pagination
$sql_total = "SELECT COUNT(p.id_produk) as total " . $base_query;
$stmt_total = $koneksi->prepare($sql_total);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_produk = $stmt_total->get_result()->fetch_assoc()['total'];
$total_halaman = ceil($total_produk / $limit);


// Ambil data produk sesuai halaman
$sql_produk = "SELECT p.*, k.nama_kategori, k.slug AS kategori_slug " . $base_query . " ORDER BY p.id_produk DESC LIMIT ? OFFSET ?";
$stmt_produk = $koneksi->prepare($sql_produk);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt_produk->bind_param($types, ...$params);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
$produk_list = [];
if ($result_produk) {
    while ($row = $result_produk->fetch_assoc()) {
        $produk_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Toko - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <link rel="stylesheet" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">
    <link rel="stylesheet" href="vendor/venobox/venobox.min.css">
    <link rel="stylesheet" href="vendor/slickslider/slick.min.css">
    <link rel="stylesheet" href="vendor/niceselect/nice-select.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
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
            <h2>Toko Kami</h2> 
        </div>
    </section>

    <section class="inner-section shop-part">
        <div class="container">
            <div class="row content-reverse">
                <div class="col-lg-3">
                    <div class="shop-widget">
                        <h6 class="shop-widget-title">Filter by Kategori</h6>
                        <form><ul class="shop-widget-list shop-widget-scroll">
                            <li><div class="shop-widget-content"><input type="checkbox" onchange="window.location.href='index.php'" <?= (!$kategori_filter) ? 'checked' : ''; ?>><label>Semua Kategori</label></div></li>
                            <?php if (!empty($kategori_list)): foreach($kategori_list as $kategori): ?>
                            <li><div class="shop-widget-content"><input type="checkbox" id="cate-<?= $kategori['id_kategori']; ?>" onchange="window.location.href='index.php?kategori=<?= $kategori['slug']; ?>'" <?= ($kategori_filter == $kategori['slug']) ? 'checked' : ''; ?>><label for="cate-<?= $kategori['id_kategori']; ?>"><?= sanitize($kategori['nama_kategori']); ?></label></div></li>
                            <?php endforeach; endif; ?>
                        </ul></form>
                    </div>
                    <div class="shop-widget">
                        <h6 class="shop-widget-title">Filter by Merk</h6>
                        <form><ul class="shop-widget-list shop-widget-scroll">
                             <li><div class="shop-widget-content"><input type="checkbox" onchange="window.location.href='index.php'" <?= (!$merk_filter) ? 'checked' : ''; ?>><label>Semua Merk</label></div></li>
                            <?php if (!empty($merk_list)): foreach($merk_list as $merk): ?>
                            <li><div class="shop-widget-content"><input type="checkbox" id="brand-<?= sanitize($merk); ?>" onchange="window.location.href='index.php?merk=<?= urlencode($merk); ?>'" <?= ($merk_filter == $merk) ? 'checked' : ''; ?>><label for="brand-<?= sanitize($merk); ?>"><?= sanitize($merk); ?></label></div></li>
                            <?php endforeach; endif; ?>
                        </ul></form>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="row"><div class="col-lg-12"><div class="top-filter"><div class="filter-show"><label class="filter-label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Menampilkan <?= count($produk_list); ?> dari <strong><?= $total_produk; ?></strong> Produk</label></div></div></div></div>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4">
                        <?php if (!empty($produk_list)): foreach($produk_list as $produk): ?>
                        <div class="col">
                            <div class="product-card">
                                <div class="product-media">
                                    <a class="product-image" href="detail_produk.php?id=<?= $produk['id_produk']; ?>">
                                        <?php 
                                            $gambar_path = 'admin/assets/images/produk/' . sanitize($produk['gambar_produk']);
                                            $gambar_path = (empty($produk['gambar_produk']) || !file_exists($gambar_path)) ? 'admin/assets/images/produk/default.png' : $gambar_path;
                                        ?>
                                        <img src="<?= $gambar_path; ?>" alt="<?= sanitize($produk['nama_produk']); ?>">
                                    </a>
                                </div>
                                <div class="product-content">
                                    <h6 class="product-name"><a href="detail_produk.php?id=<?= $produk['id_produk']; ?>"><?= sanitize($produk['nama_produk']); ?></a></h6>
                                    <h6 class="product-price"><span><?= format_rupiah($produk['harga_jual']); ?></span></h6>
                                    <a href="keranjang_proses.php?aksi=tambah&id=<?= $produk['id_produk']; ?>" class="product-add" title="Tambah ke Keranjang"><i class="fas fa-shopping-basket"></i><span>add</span></a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                            <div class="col-12"><p class="text-center fs-5 mt-5">Tidak ada produk yang cocok dengan kriteria Anda.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="bottom-paginate">
                                <p class="page-info">Menampilkan halaman <?= $page; ?> dari <?= $total_halaman; ?></p>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>"><i class="fas fa-long-arrow-alt-left"></i></a></li>
                                    <?php endif; ?>

                                    <?php for($i = 1; $i <= $total_halaman; $i++): ?>
                                    <li class="page-item"><a class="page-link <?= ($i == $page) ? 'active' : ''; ?>" href="?page=<?= $i; ?>"><?= $i; ?></a></li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_halaman): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>"><i class="fas fa-long-arrow-alt-right"></i></a></li>
                                    <?php endif; ?>
                                </ul>
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

    <script src="vendor/bootstrap/jquery-1.12.4.min.js"></script>
    <script src="vendor/bootstrap/popper.min.js"></script>
    <script src="vendor/bootstrap/bootstrap.min.js"></script>
    <script src="vendor/niceselect/nice-select.min.js"></script>
    <script src="vendor/slickslider/slick.min.js"></script>
    <script src="js/nice-select.js"></script>
    <script src="js/slick.js"></script>
    <script src="js/main.js"></script>
    <?php show_sweetalert(); ?>
</body>
</html> 