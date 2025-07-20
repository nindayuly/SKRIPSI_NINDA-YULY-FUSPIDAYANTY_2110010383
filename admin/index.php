<?php
// Ninda/admin/index.php

ob_start();

// Memuat file konfigurasi dasar
require_once 'inc/koneksi.php';
require_once 'inc/helper.php';

// 1. Keamanan: Cek session, jika tidak ada, redirect ke login
if (!is_admin() && !is_kurir()) { // Hanya admin dan kurir yang boleh masuk
    set_sweetalert('warning', 'Akses Ditolak!', 'Anda harus login sebagai admin atau kurir.');
    header("Location: login.php");
    exit();
}

// 2. Ambil data global
// Ambil data dari tabel meta
$meta_query = "SELECT * FROM meta WHERE id_meta = 1";
$meta_result = $koneksi->query($meta_query);
$meta = $meta_result->fetch_assoc();

// Ambil data pengguna yang login
$nama_pengguna = $_SESSION['pengguna_nama'] ?? 'Pengguna'; 
$peran_pengguna = $_SESSION['pengguna_peran'] ?? 'Pelanggan';

// 3. Pengaturan Routing dan Whitelist Halaman
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Daftar semua halaman yang valid untuk dimuat
$allowed_pages = [
    'beranda', 'pesanan', 'pesanan_detail', 'produk_data', 'produk_kategori', 
    'stok_pemasok', 'stok_pembelian', 'stok_pembelian_detail', 'stok_opname', 'kurir', 
    'ulasan', 'promosi', 'laporan', 'pengguna', 'pengaturan', 'profil',

    'laporan_penjualan', 
    'laporan_pengiriman',
    'laporan_pelanggan',
    'laporan_ulasan',
    'laporan_promosi',
    'laporan_terlaris',
    'laporan_retur',
    'laporan_stok_habis',
    'laporan_stok_barang',
    'laporan_restok',
    'laporan_pendapatan',
    'laporan_produk_tidak_laku'
];

$page_title = ucfirst($currentPage);
?> 

<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-image="img-1" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light" data-theme-color="0">


<!-- Mirrored from themesbrand.com/dosix/layouts/ by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 11 Jun 2025 04:19:07 GMT -->
<head>

    <meta charset="utf-8">
    <title><?= $page_title; ?> | <?= sanitize($meta['nama_instansi'] ?? ''); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Minimal Admin & Dashboard Template" name="description">
    <meta content="Themesbrand" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>">
    
    <!-- Fonts css load -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

    <!--datatable css-->
    <link rel="stylesheet" href="assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" >
    <!--datatable responsive css-->
    <link rel="stylesheet" href="assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" >

    <link rel="stylesheet" href="assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">
    <!-- custom Css-->
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css">

    <script src="assets/sweetalert2@11.js"></script>

    <style>
        .avatar-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4A6CF8; /* Warna latar belakang avatar, bisa diubah */
            color: #ffffff; /* Warna teks inisial */
            font-weight: 600;
            font-size: 14px; /* Ukuran font inisial */
            width: 32px;   /* Lebar avatar, sesuaikan dengan template */
            height: 32px;  /* Tinggi avatar, sesuaikan dengan template */
        }
    </style>
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="55">
                    </span>
                </a>
                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="55">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
                <div class="vertical-menu-btn-wrapper header-item vertical-icon">
                    <button type="button" class="btn btn-sm px-0 fs-xl vertical-menu-btn topnav-hamburger shadow hamburger-icon" id="topnav-hamburger-icon">
                        <i class='bx bx-chevrons-right'></i>
                        <i class='bx bx-chevrons-left'></i>
                    </button>
                </div>
            </div>
        
        
            <div id="scrollbar">
                <div class="container-fluid">
        
                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
        
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'beranda') ? 'active' : ''; ?>" href="index.php?page=beranda">
                                <i class="bx bx-home-alt"></i> <span data-key="t-beranda">Beranda</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'pesanan') ? 'active' : ''; ?>" href="index.php?page=pesanan">
                                <i class="bx bx-cart-alt"></i> <span data-key="t-pesanan">Pesanan</span>
                            </a>
                        </li>

                        <li class="menu-title"><span data-key="t-manajemen">Manajemen</span></li>
                        
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarProduk" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProduk">
                                <i class="bx bx-box"></i> <span data-key="t-produk">Produk</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarProduk">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item"><a href="index.php?page=produk_data" class="nav-link" data-key="t-produk-data">Data Produk</a></li>
                                    <li class="nav-item"><a href="index.php?page=produk_kategori" class="nav-link" data-key="t-produk-kategori">Kategori Produk</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarStok" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStok">
                                <i class="bx bx-buildings"></i> <span data-key="t-stok">Stok</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarStok">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item"><a href="index.php?page=stok_pemasok" class="nav-link" data-key="t-pemasok">Data Pemasok</a></li>
                                    <li class="nav-item"><a href="index.php?page=stok_pembelian" class="nav-link" data-key="t-pembelian">Pembelian/Restok</a></li>
                                    <li class="nav-item"><a href="index.php?page=stok_opname" class="nav-link" data-key="t-opname">Stok Opname</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'kurir') ? 'active' : ''; ?>" href="index.php?page=kurir">
                                <i class="bx bxs-truck"></i> <span data-key="t-kurir">Manajemen Kurir</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'ulasan') ? 'active' : ''; ?>" href="index.php?page=ulasan">
                                <i class="bx bx-star"></i> <span data-key="t-ulasan">Ulasan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'promosi') ? 'active' : ''; ?>" href="index.php?page=promosi">
                                <i class="bx bxs-discount"></i> <span data-key="t-promosi">Promosi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'pengguna') ? 'active' : ''; ?>" href="index.php?page=pengguna">
                                <i class="bx bx-user-circle"></i> <span data-key="t-pengguna">Pengguna</span>
                            </a>
                        </li>

                        <li class="menu-title"><span data-key="t-sistem">Sistem</span></li>

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarLaporan" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarLaporan">
                                <i class="bx bx-bar-chart-alt-2"></i> <span data-key="t-laporan">Laporan</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarLaporan">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_penjualan" class="nav-link" data-key="t-laporan-penjualan">
                                            Laporan Penjualan
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_pengiriman" class="nav-link" data-key="t-laporan-pengiriman">
                                            Laporan Pengiriman
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_pelanggan" class="nav-link" data-key="t-laporan-pelanggan">
                                            Laporan Pelanggan
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_ulasan" class="nav-link" data-key="t-laporan-ulasan">
                                             Ulasan
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_promosi" class="nav-link" data-key="t-laporan-promosi">
                                            Laporan Promosi
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_terlaris" class="nav-link" data-key="t-laporan-terlaris">
                                            Laporan Produk Terlaris
                                        </a>
                                    </li> 
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_stok_habis" class="nav-link" data-key="t-laporan-stok-habis">
                                            Laporan Stok Hampir Habis
                                        </a>
                                    </li>
                                     <li class="nav-item">
                                        <a href="index.php?page=laporan_stok_barang" class="nav-link" data-key="t-laporan-stok-barang">
                                            Laporan Stok Barang
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_produk_tidak_laku" class="nav-link" data-key="t-laporan-produk-tidak-laku">
                                            Laporan Produk Tidak Laku
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_restok" class="nav-link" data-key="t-laporan-produk-tidak-laku">
                                            Laporan Restok
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php?page=laporan_pendapatan" class="nav-link" data-key="t-laporan-produk-tidak-laku">
                                            Laporan Pendapatan
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li> 

                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($currentPage == 'pengaturan') ? 'active' : ''; ?>" href="index.php?page=pengaturan">
                                <i class="bx bx-cog"></i> <span data-key="t-pengaturan">Pengaturan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#" id="tombol-logout">
                                <i class="bx bx-power-off"></i> <span data-key="t-pengaturan">Logout</span>
                            </a>
                        </li>
        
                    </ul>
                </div>
                <!-- Sidebar -->
            </div> 
        
            <div class="dropdown sidebar-user mt-4">
                <button type="button" class="btn sidebar-user-button shadow-none w-100" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="d-flex align-items-center overflow-hidden">
                        <div class="rounded-circle header-profile-user avatar-initials">
                            <?= get_initials($nama_pengguna); ?>
                        </div> 
                        <span class="text-start ms-xl-2 overflow-hidden flex-grow-1 sideba-user-content">
                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text text-truncate mb-0" data-key="t-dixie-allen"><?= sanitize($nama_pengguna); ?></span>
                            <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text" data-key="t-founder"><?= ucfirst(sanitize($peran_pengguna)); ?></span>
                        </span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <h6 class="dropdown-header">Selamat Datang, <?= sanitize($nama_pengguna); ?>!</h6>
                    <a class="dropdown-item" href="index.php?page=profil"><i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Profil</span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" id="tombol-logout1"><i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                </div>
            </div>
        
            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>
        <div class="topbar-wrapper shadow">
            <header id="page-topbar">
                <div class="layout-width">
                    <div class="navbar-header">
                        <div class="d-flex">
                            <!-- LOGO -->
                            <div class="navbar-brand-box horizontal-logo">
                                <a href="index.php" class="logo logo-dark">
                                    <span class="logo-sm">
                                        <img src="assets/images/logo-sm.png" alt="" height="22">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="assets/images/logo-dark.png" alt="" height="22">
                                    </span>
                                </a>
                                <a href="index.php" class="logo logo-light">
                                    <span class="logo-sm">
                                        <img src="assets/images/logo-sm.png" alt="" height="22">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="assets/images/logo-light.png" alt="" height="22">
                                    </span>
                                </a>
                            </div>
            
                            <div class="header-item flex-shrink-0 me-3 vertical-btn-wrapper">
                                <button type="button" class="btn btn-sm px-0 fs-xl vertical-menu-btn topnav-hamburger border hamburger-icon" id="topnav-hamburger-icon">
                                    <i class='bx bx-chevrons-right arrow-right'></i>
                                    <i class='bx bx-chevrons-left arrow-left'></i>
                                </button>
                            </div>
            
                            <h4 class="mb-sm-0 header-item page-title lh-base"><?= sanitize($meta['nama_instansi'] ?? 'Adong Classic'); ?></h4>
                        </div>
            
                        <div class="d-flex align-items-center"> 
            
                            <div class="dropdown topbar-head-dropdown ms-1 header-item">
                                <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle mode-layout" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-sun align-middle fs-3xl"></i>
                                </button>
                                <div class="dropdown-menu p-2 dropdown-menu-end" id="light-dark-mode">
                                    <a href="#!" class="dropdown-item" data-mode="light"><i class="bx bx-sun align-middle me-2"></i> Default (light mode)</a>
                                    <a href="#!" class="dropdown-item" data-mode="dark"><i class="bx bx-moon align-middle me-2"></i> Dark</a>
                                    <a href="#!" class="dropdown-item" data-mode="auto"><i class="bx bx-desktop align-middle me-2"></i> Auto (system default)</a>
                                </div>
                            </div>
            
                            <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                                <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                                    <i class='bx bx-notification fs-3xl'></i>
                                    <span class="position-absolute topbar-badge fs-3xs translate-middle badge rounded-pill bg-danger"><span class="notification-badge">3</span><span class="visually-hidden">unread messages</span></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown"> 
            
                                    <div class="pb-2 ps-0" id="notificationItemsTabContent">
                                        <div data-simplebar style="max-height: 300px;" class="pe-0">
                                            <h6 class="text-overflow text-muted fs-sm my-2 notification-title px-3">Today</h6>
            
                                            <div class="text-reset notification-item d-block dropdown-item position-relative border-dashed border-bottom">
                                                <div class="d-flex">
                                                    <div class="position-relative me-3 flex-shrink-0">
                                                        <img src="assets/images/users/32/avatar-3.jpg" class="rounded-circle avatar-xs" alt="user-pic">
                                                        <span class="active-badge position-absolute start-100 translate-middle p-1 bg-danger rounded-circle">
                                                            <span class="visually-hidden">Un Active</span>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fs-md text-muted">
                                                            <p class="mb-1 text-muted"><b>Angela Bernier</b> mentioned you in <a href="#!">This Project</a></p>
                                                        </div>
                                                        <p class="mb-0 fs-xs fw-medium text-uppercase text-muted">
                                                            <span><i class="mdi mdi-clock-outline"></i> 48 min ago</span>
                                                        </p>
                                                    </div>
                                                    <div class="px-2 fs-base">
                                                        <div class="form-check notification-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="all-notification-check02">
                                                            <label class="form-check-label" for="all-notification-check02"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                </div>
                            </div>
            
                            <div class="dropdown ms-sm-3 header-item topbar-user">
                                <button type="button" class="btn shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="d-flex align-items-center">
                                        <img class="rounded-circle header-profile-user" src="assets/images/users/32/avatar-1.jpg" alt="Header Avatar">
                                        <span class="text-start ms-xl-2"> 
                                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= sanitize($nama_pengguna); ?></span>
                                            <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text"><?= ucfirst(sanitize($peran_pengguna)); ?></span>
                                        </span>
                                    </span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <h6 class="dropdown-header">Selamat Datang, <?= sanitize($nama_pengguna); ?>!</h6>
                                    <a class="dropdown-item" href="index.php?page=profil"><i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i> <span class="align-middle">Profil</span></a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" id="tombol-logout"><i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header> 

        </div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content wrapper">
                <div class="container-fluid">

                    <?php
                    // Logika untuk memuat konten halaman
                    if (in_array($currentPage, $allowed_pages)) {
                        $page_file = 'pages/' . $currentPage . '.php';
                        if (file_exists($page_file)) {
                            include_once($page_file);
                        } else {
                            // Jika file tidak ada, tampilkan pesan error
                            echo "<div class='alert alert-danger'>Error 404: File halaman <strong>{$page_file}</strong> tidak ditemukan.</div>";
                        }
                    } else {
                        // Jika halaman tidak ada di whitelist, tampilkan pesan error
                        echo "<div class='alert alert-danger'>Error 404: Halaman <strong>{$currentPage}</strong> tidak diizinkan atau tidak ada.</div>";
                    }
                    ?>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © <?= sanitize($meta['nama_instansi'] ?? 'Adong Classic'); ?>.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Design & Develop by Ninda Yuly Fuspidayanty
                            </div>
                        </div>
                    </div>
                </div>
            </footer>        

        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->


    <!--start back-to-top-->
    <button class="btn btn-dark btn-icon" id="back-to-top">
        <i class="bi bi-caret-up fs-3xl"></i>
    </button>
    <!--end back-to-top-->
    
    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    
    <div class="customizer-setting d-none d-md-block">
        <div class="btn btn-info p-2 text-uppercase rounded-end-0 shadow-lg" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
            <i class="bi bi-gear mb-1"></i> Customizer
        </div>
    </div>
    
    <!-- Theme Settings -->
    <?php include "tema.php" ?>
    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="assets/js/plugins.js"></script>    
    <script src="assets/libs/list.js/dist/list.min.js"></script>

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>

    <!--dashboard doctor init js-->
    <script src="assets/js/pages/dashboard-doctor.init.js"></script>

    <script src="assets/code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <?php
    // Memanggil fungsi untuk menampilkan notifikasi SweetAlert
    show_sweetalert(); 
    ?>

    <script>
        // Menambahkan event listener saat seluruh dokumen HTML sudah dimuat
        document.addEventListener('DOMContentLoaded', function() {

            // Cari tombol logout berdasarkan ID yang kita buat tadi
            const tombolLogout = document.getElementById('tombol-logout');

            // Jika tombolnya ada, tambahkan event klik
            if (tombolLogout) {
                tombolLogout.addEventListener('click', function(event) {
                    // Mencegah link default berjalan
                    event.preventDefault();

                    // Tampilkan pop-up konfirmasi SweetAlert2
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: "Apakah Anda yakin ingin keluar dari sesi ini?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        // Jika pengguna menekan tombol "Ya, Logout!"
                        if (result.isConfirmed) {
                            // Arahkan browser ke file logout.php
                            window.location.href = 'logout.php';
                        }
                    });
                });
            }
        });

        // Menambahkan event listener saat seluruh dokumen HTML sudah dimuat
        document.addEventListener('DOMContentLoaded', function() {

            // Cari tombol logout berdasarkan ID yang kita buat tadi
            const tombolLogout = document.getElementById('tombol-logout1');

            // Jika tombolnya ada, tambahkan event klik
            if (tombolLogout) {
                tombolLogout.addEventListener('click', function(event) {
                    // Mencegah link default berjalan
                    event.preventDefault();

                    // Tampilkan pop-up konfirmasi SweetAlert2
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: "Apakah Anda yakin ingin keluar dari sesi ini?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        // Jika pengguna menekan tombol "Ya, Logout!"
                        if (result.isConfirmed) {
                            // Arahkan browser ke file logout.php
                            window.location.href = 'logout.php';
                        }
                    });
                });
            }
        });
    </script>
</body>


<!-- Mirrored from themesbrand.com/dosix/layouts/ by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 11 Jun 2025 04:19:47 GMT -->
</html>

<?php 
ob_end_flush();
?>