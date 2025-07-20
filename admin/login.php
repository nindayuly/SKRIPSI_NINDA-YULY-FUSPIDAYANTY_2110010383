<?php
// Ninda/admin/login.php

// Memuat file koneksi dan helper
require_once 'inc/koneksi.php';
require_once 'inc/helper.php';

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

// ☢️ PERINGATAN: Kode login ini TIDAK AMAN karena tidak menggunakan prepared statement.
// Ini dibuat HANYA karena permintaan spesifik Anda. Sangat disarankan untuk kembali
// menggunakan versi yang aman dengan prepared statement.

$meta_query = "SELECT * FROM meta WHERE id_meta = 1";
$meta_result = $koneksi->query($meta_query);
$meta = $meta_result->fetch_assoc();

// Proses form jika ada data yang dikirim (method POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_sweetalert('error', 'Gagal', 'Email dan password tidak boleh kosong!');
    } else {
        // Membersihkan input (sedikit perlindungan, tidak cukup)
        $email_safe = $koneksi->real_escape_string($email);
        $password_safe = $koneksi->real_escape_string($password);

        // 🚨 Query TIDAK AMAN, rentan SQL Injection
        $sql = "SELECT id_pengguna, nama_lengkap, email, password, peran 
                FROM pengguna 
                WHERE email = '$email_safe' AND password = '$password_safe'";
        
        $result = $koneksi->query($sql);

        // Cek apakah ada persis satu pengguna yang cocok
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Cek peran untuk akses backend
            if ($user['peran'] === 'admin' || $user['peran'] === 'kurir') {
                // Buat session
                $_SESSION['pengguna_id'] = $user['id_pengguna'];
                $_SESSION['pengguna_nama'] = $user['nama_lengkap'];
                $_SESSION['pengguna_peran'] = $user['peran'];

                // BARIS BARU: Atur notifikasi sukses di sini
                set_sweetalert('success', 'Login Berhasil!', 'Selamat datang kembali, ' . $user['nama_lengkap']);

                // 7. Arahkan ke halaman dashboard
                header("Location: index.php");
                exit;
            } else {
                set_sweetalert('error', 'Akses Ditolak', 'Halaman ini hanya untuk admin atau kurir.');
            }
        } else {
            // Jika tidak ada data yang cocok
            set_sweetalert('error', 'Login Gagal', 'Email atau password yang Anda masukkan salah.');
        }
    }
}
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-image="img-1" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light" data-theme-color="0">

<head>
    <meta charset="utf-8">
    <title>Login | <?= sanitize($meta['nama_instansi'] ?? ''); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Minimal Admin & Dashboard Template" name="description">
    <meta content="Themesbrand" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <script src="assets/js/layout.js"></script>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css">
    <script src="assets/sweetalert2@11.js"></script>
</head>

<body>
    <section class="auth-page-wrapper p-2 p-lg-4 position-relative d-flex align-items-center justify-content-center min-vh-100">
        <div class="card mb-0 w-100 p-3 p-lg-2" style="background-image: url('assets/bglogin.jpg');background-size: cover;background-position: center;">
            <div class="effect-one"></div>
            <div class="row g-0 align-items-center">
                <div class="col-xxl-8 order-last order-xl-first">
                    <div class="card auth-card border-0 shadow-none mb-0 bg-transparent">
                        <div class="card-body p-4 p-xl-5 d-flex justify-content-between flex-column h-100">
                            <div class="text-center mt-auto">
                                <p class="mb-0 mt-3 text-white">
                                    &copy; <script>document.write(new Date().getFullYear())</script> Adong Classic. Dibuat oleh Ninda
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xxl-4 mx-auto order-first order-xl-last">
                    <div class="card shadow-lg border-none m-lg-5">
                        <div class="card-body">
                            <div class="text-center mt-4">
                                <div class="mb-4 pb-2">
                                    <a href="#" class="auth-logo">
                                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="30" class="auth-logo-dark mx-auto">
                                        <img src="assets/images/<?= sanitize($meta['logo'] ?? ''); ?>" alt="" height="30" class="auth-logo-light mx-auto">
                                    </a>
                                </div>
                                <h5 class="fs-3xl">Selamat Datang Kembali</h5>
                                <p class="text-muted">Login untuk melanjutkan ke Panel Admin.</p>
                            </div>
                            <div class="p-2 mt-4">
                                <form action="login.php" method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <div class="position-relative ">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password-input">Password <span class="text-danger">*</span></label>
                                        <div class="position-relative auth-pass-inputgroup mb-3">
                                            <input type="password" class="form-control pe-5 password-input" name="password" placeholder="Masukkan password" id="password-input" required>
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button class="btn btn-primary w-100" type="submit">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div></div></div>
                </div>
            </div>
    </section>

    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/pages/password-addon.init.js"></script>
    
    <?php
    // Memanggil fungsi untuk menampilkan notifikasi SweetAlert
    show_sweetalert(); 
    ?>
</body>
</html>