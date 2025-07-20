<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// 1. Validate Access & Get Initial Data
if (!isset($_SESSION['customer_id'])) {
    set_sweetalert('warning', 'Access Denied!', 'You must be logged in to view your order history.');
    header('Location: login_customer.php');
    exit();
}
$id_pelanggan = $_SESSION['customer_id'];

// Get meta and category data for header/navbar
$meta_result = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1");
$meta = $meta_result ? $meta_result->fetch_assoc() : [];

$sql_kategori_nav = "SELECT * FROM kategori_produk ORDER BY nama_kategori ASC";
$result_kategori_nav = $koneksi->query($sql_kategori_nav);
$kategori_list_nav = [];
if ($result_kategori_nav) {
    while($row = $result_kategori_nav->fetch_assoc()) {
        $kategori_list_nav[] = $row;
    }
}

// 2. Status Filter Logic
$status_filter = $_GET['status'] ?? 'all';
$filter_query = "";
if ($status_filter != 'all') {
    $allowed_statuses = ['menunggu_pembayaran', 'diproses', 'menunggu_kurir', 'sedang_diantar', 'selesai', 'dibatalkan'];
    if (in_array($status_filter, $allowed_statuses)) {
        $escaped_status = $koneksi->real_escape_string($status_filter);
        $filter_query = "AND p.status_pesanan = '$escaped_status'";
    }
}

// 3. Fetch Order Data and Their Details
$sql_pesanan = "SELECT 
                    p.id_pesanan, p.nomor_pesanan, p.tanggal_pesanan, p.total_final, p.status_pesanan, p.alamat_kirim,
                    p.total_harga_produk, p.nilai_diskon, p.biaya_kirim,
                    (SELECT COUNT(*) FROM pesanan_detail pd WHERE pd.id_pesanan = p.id_pesanan) as jumlah_item
                FROM pesanan p 
                WHERE p.id_pengguna = $id_pelanggan $filter_query 
                ORDER BY p.tanggal_pesanan DESC";

$result_pesanan = $koneksi->query($sql_pesanan);
$pesanan_list = [];
if ($result_pesanan) {
    while ($row = $result_pesanan->fetch_assoc()) {
        $id_pesanan_current = $row['id_pesanan'];
        
        // Corrected Query for fetching product details and checking for reviews
        $sql_detail = "SELECT 
                           pd.id_pesanan_detail, 
                           pd.id_produk, 
                           pr.nama_produk, 
                           pr.gambar_produk,
                           (SELECT id_ulasan FROM ulasan u WHERE u.id_pesanan_detail = pd.id_pesanan_detail LIMIT 1) as id_ulasan
                       FROM pesanan_detail pd
                       JOIN produk pr ON pd.id_produk = pr.id_produk
                       WHERE pd.id_pesanan = $id_pesanan_current";

        $result_detail = $koneksi->query($sql_detail);
        $items = [];
        if ($result_detail) {
            while($item_row = $result_detail->fetch_assoc()){
                $items[] = $item_row;
            }
        }
        $row['items'] = $items;
        $pesanan_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Riwayat Pesanan - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
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

    <section class="inner-section single-banner" style="background: url(admin/assets/bgcover.jpg) no-repeat center;">
        <div class="container">
            <h2>Riwayat Pesanan</h2> 
        </div>
    </section>

    <section class="inner-section orderlist-part">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="orderlist-filter">
                        <h5>Total Pesanan <span>- (<?= count($pesanan_list); ?>)</span></h5>
                        <div class="filter-short">
                            <label class="form-label">Status:</label>
                            <form method="GET" id="filterForm" class="d-inline-block">
                                <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                                    <option value="all" <?= ($status_filter == 'all') ? 'selected' : ''; ?>>Semua Pesanan</option>
                                    <option value="menunggu_pembayaran" <?= ($status_filter == 'menunggu_pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                    <option value="diproses" <?= ($status_filter == 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="sedang_diantar" <?= ($status_filter == 'sedang_diantar') ? 'selected' : ''; ?>>Sedang Diantar</option>
                                    <option value="selesai" <?= ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="dibatalkan" <?= ($status_filter == 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?php if (!empty($pesanan_list)): foreach ($pesanan_list as $pesanan): ?>
                    <div class="orderlist">
                        <div class="orderlist-head">
                            <h5>Nomor Pesanan: <?= sanitize($pesanan['nomor_pesanan']); ?></h5>
                            <h5>Status: <?= str_replace('_', ' ', sanitize(ucwords($pesanan['status_pesanan']))); ?></h5>
                        </div>
                        <div class="orderlist-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="order-track">
                                        <?php
                                            $statuses = ['menunggu_pembayaran', 'diproses', 'menunggu_kurir', 'sedang_diantar', 'selesai'];
                                            $current_status_index = array_search($pesanan['status_pesanan'], $statuses);
                                            // Jika status dibatalkan, anggap tidak ada yang aktif
                                            if ($pesanan['status_pesanan'] == 'dibatalkan') $current_status_index = -1;
                                        ?>
                                        <ul class="order-track-list">
                                            <?php foreach ($statuses as $index => $status_item): ?>
                                                <li class="order-track-item <?= ($current_status_index >= $index) ? 'active' : ''; ?>">
                                                    <i class="icofont-check"></i>
                                                    <span><?= str_replace('_', ' ', ucfirst($status_item)); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <ul class="orderlist-details">
                                        <li><h6>Nomor Pesanan</h6><p><?= sanitize($pesanan['nomor_pesanan']); ?></p></li>
                                        <li><h6>Total Item</h6><p><?= $pesanan['jumlah_item']; ?> Item</p></li>
                                        <li><h6>Tanggal Pesan</h6><p><?= tgl_indo($pesanan['tanggal_pesanan']); ?></p></li>
                                    </ul>
                                </div>
                                <div class="col-lg-4">
                                    <ul class="orderlist-details">
                                        <li><h6>Subtotal</h6><p><?= format_rupiah($pesanan['total_harga_produk']); ?></p></li>
                                        <li><h6>Diskon</h6><p><?= format_rupiah(abs($pesanan['nilai_diskon'])); ?></p></li>
                                        <li><h6>Biaya Kirim</h6><p><?= format_rupiah($pesanan['biaya_kirim']); ?></p></li>
                                        <li><h6>Total Akhir</h6><p><?= format_rupiah($pesanan['total_final']); ?></p></li>
                                    </ul>
                                </div>
                                <div class="col-lg-3">
                                    <div class="orderlist-deliver"><h6>Alamat Pengiriman</h6><p><?= sanitize($pesanan['alamat_kirim']); ?></p></div>
                                </div>

                                
                                <div class="col-lg-12 d-flex justify-content-end mb-4">
                                    <div class="orderlist-action">
                                        <a class="btn btn-inline" href="invoice.php?id=<?= $pesanan['id_pesanan']; ?>">Detail & Invoice</a> 

                                        <?php if ($pesanan['status_pesanan'] == 'menunggu_pembayaran'): ?>
                                            <a class="btn btn-inline" href="pembayaran.php?id=<?= $pesanan['id_pesanan']; ?>">Pembayaran</a>
                                        <?php elseif ($pesanan['status_pesanan'] == 'sedang_diantar'): ?>
                                            <a class="btn btn-inline btn-success" href="pesanan_aksi.php?aksi=selesai&id=<?= $pesanan['id_pesanan']; ?>" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan pesanan ini?')">Pesanan Diterima</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                        <div class="text-center my-5">
                            <img src="images/empty.png" alt="empty" style="width: 150px;">
                            <h4 class="mt-4">Anda belum memiliki riwayat pesanan.</h4>
                            <p>Mari mulai berbelanja di toko kami!</p>
                            <a href="index.php" class="btn btn-inline">Mulai Belanja</a>
                        </div>
                    <?php endif; ?>
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