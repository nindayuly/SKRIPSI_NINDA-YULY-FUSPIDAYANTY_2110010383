<?php
// NINDA/register_customer.php
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
    // Ambil semua data dari form
    $nama_lengkap = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($password) || empty($konfirmasi_password)) {
        set_sweetalert('error', 'Gagal!', 'Semua kolom wajib diisi.');
    } elseif ($password !== $konfirmasi_password) {
        set_sweetalert('error', 'Gagal!', 'Password dan konfirmasi password tidak cocok.');
    } else {
        // Cek apakah email sudah terdaftar
        $sql_cek = "SELECT id_pengguna FROM pengguna WHERE email = '$email'";
        $result_cek = $koneksi->query($sql_cek);
        if ($result_cek->num_rows > 0) {
            set_sweetalert('error', 'Gagal!', 'Email sudah terdaftar. Silakan gunakan email lain.');
        } else {
            // Hash password untuk keamanan
            // ☢️ Penting: Gunakan password_hash() untuk keamanan di aplikasi produksi
            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database (untuk sekarang tanpa hash sesuai permintaan sebelumnya)
            $sql_insert = "INSERT INTO pengguna (nama_lengkap, email, telepon, alamat, password, peran) 
                           VALUES ('$nama_lengkap', '$email', '$telepon', '$alamat', '$password', 'pelanggan')";

            if ($koneksi->query($sql_insert)) {
                set_sweetalert('success', 'Registrasi Berhasil!', 'Akun Anda telah dibuat. Silakan login.');
                header('Location: login_customer.php');
                exit();
            } else {
                set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat membuat akun.');
            }
        }
    }
    // Redirect kembali ke halaman register jika ada error
    header('Location: register_customer.php');
    exit();
}

$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register Akun - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
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
                            <h2>Daftar Sekarang!</h2>
                            <p>Buat akun baru Anda di toko kami</p>
                        </div>
                        <div class="user-form-group">
                            <form class="user-form" method="POST" action="register_customer.php">
                                <div class="form-group">
                                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="telepon" class="form-control" placeholder="Masukkan nomor telepon (opsional)">
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" name="alamat" placeholder="Masukkan alamat lengkap (opsional)"></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi password" required>
                                </div>
                                <div class="form-button">
                                    <button type="submit">register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="user-form-remind">
                        <p>Sudah punya akun?<a href="login_customer.php"> login di sini</a></p>
                    </div>
                    <div class="user-form-footer">
                        <p><?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?> | &copy; Copyright by <a href="#">Tim Anda</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php show_sweetalert(); ?>
</body>
</html>