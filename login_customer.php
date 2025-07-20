<?php
// NINDA/login_customer.php
session_start();

// Jika pelanggan sudah login, arahkan ke halaman utama
if (isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $koneksi->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Cari pengguna berdasarkan email dengan peran 'pelanggan'
    $sql = "SELECT id_pengguna, nama_lengkap, email, password FROM pengguna WHERE email = '$email' AND peran = 'pelanggan'";
    $result = $koneksi->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (gantilah dengan password_verify jika Anda menggunakan hash)
        // ☢️ Peringatan Keamanan: Metode verifikasi password ini tidak aman untuk produksi.
        // Sebaiknya gunakan password_hash() saat register dan password_verify() saat login.
        if ($password === $user['password']) { 
            // Login berhasil
            $_SESSION['customer_id'] = $user['id_pengguna'];
            $_SESSION['customer_nama'] = $user['nama_lengkap'];
            set_sweetalert('success', 'Login Berhasil!', 'Selamat datang kembali, ' . $user['nama_lengkap']);
            header('Location: index.php');
            exit();
        } else {
            // Password salah
            set_sweetalert('error', 'Login Gagal!', 'Password yang Anda masukkan salah.');
        }
    } else {
        // Email tidak ditemukan
        set_sweetalert('error', 'Login Gagal!', 'Email tidak terdaftar sebagai pelanggan.');
    }
    header('Location: login_customer.php');
    exit();
}

$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login Pelanggan - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/user-auth.css">
    <script src="admin/assets/sweetalert2@11.js"></script>
</head>
<body>
    <section class="user-form-part">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-12 col-lg-12 col-xl-10">
                    <div class="user-form-logo">
                        <a href="index.php"><img src="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>" alt="logo"></a>
                    </div>
                    <div class="user-form-card">
                        <div class="user-form-title">
                            <h2>Selamat Datang!</h2>
                            <p>Gunakan kredensial Anda untuk masuk.</p>
                        </div>
                        <div class="user-form-group">
                            <form class="user-form" method="POST" action="login_customer.php">
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
                                </div>
                                <div class="form-button">
                                    <button type="submit">login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="user-form-remind">
                        <p>Belum punya akun?<a href="register_customer.php"> register di sini</a></p>
                    </div>
                    <div class="user-form-footer">
                        <p><?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?> | &copy; Copyright by <a href="#">Ninda Yuly Fuspidayanty</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section> 

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