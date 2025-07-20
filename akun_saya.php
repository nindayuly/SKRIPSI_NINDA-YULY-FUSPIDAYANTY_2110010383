<?php
// NINDA/akun_saya.php
session_start();

require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Cek apakah pelanggan sudah login, jika belum, redirect ke halaman login
if (!isset($_SESSION['customer_id'])) {
    set_sweetalert('warning', 'Akses Ditolak!', 'Anda harus login terlebih dahulu untuk mengakses halaman ini.');
    header('Location: login_customer.php');
    exit();
}

$id_pelanggan = $_SESSION['customer_id'];

// Proses update profil
if (isset($_POST['update_profil'])) {
    $nama_lengkap = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);

    // Cek apakah email diubah dan sudah digunakan oleh orang lain
    $sql_cek_email = "SELECT id_pengguna FROM pengguna WHERE email = '$email' AND id_pengguna != $id_pelanggan";
    $result_cek_email = $koneksi->query($sql_cek_email);

    if ($result_cek_email->num_rows > 0) {
        set_sweetalert('error', 'Gagal!', 'Email sudah digunakan oleh akun lain.');
    } else {
        $sql_update = "UPDATE pengguna SET nama_lengkap = '$nama_lengkap', email = '$email', telepon = '$telepon', alamat = '$alamat' WHERE id_pengguna = $id_pelanggan";
        if ($koneksi->query($sql_update)) {
            $_SESSION['customer_nama'] = $nama_lengkap; // Update nama di session juga
            set_sweetalert('success', 'Berhasil!', 'Profil Anda telah diperbarui.');
        } else {
            set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }
    header('Location: akun_saya.php');
    exit();
}

// Proses ganti password
if (isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    $sql_user = "SELECT password FROM pengguna WHERE id_pengguna = $id_pelanggan";
    $user = $koneksi->query($sql_user)->fetch_assoc();

    if ($password_lama !== $user['password']) { // Ganti dengan password_verify jika menggunakan hash
        set_sweetalert('error', 'Gagal!', 'Password lama Anda salah.');
    } elseif ($password_baru !== $konfirmasi_password) {
        set_sweetalert('error', 'Gagal!', 'Password baru dan konfirmasi tidak cocok.');
    } else {
        $sql_update_pass = "UPDATE pengguna SET password = '$password_baru' WHERE id_pengguna = $id_pelanggan";
        if ($koneksi->query($sql_update_pass)) {
            set_sweetalert('success', 'Berhasil!', 'Password Anda telah diubah.');
        } else {
            set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah password.');
        }
    }
    header('Location: akun_saya.php');
    exit();
}


// Ambil data terbaru pelanggan untuk ditampilkan di form
$sql_pelanggan = "SELECT * FROM pengguna WHERE id_pengguna = $id_pelanggan";
$result_pelanggan = $koneksi->query($sql_pelanggan);
$pelanggan = $result_pelanggan->fetch_assoc();

// Ambil data meta
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Akun Saya - <?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?></title>
    <link rel="icon" href="admin/assets/images/<?= sanitize($meta['logo'] ?? 'logo.png'); ?>">
    <link rel="stylesheet" href="fonts/icofont/icofont.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/fontawesome.min.css">
    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/user-auth.css">
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
                            <h2>Akun Saya</h2>
                            <p>Kelola informasi profil Anda untuk mengontrol akun Anda.</p>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <form class="user-form" method="POST">
                                    <h4 class="mb-3">Edit Profil</h4>
                                    <div class="form-group">
                                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" value="<?= sanitize($pelanggan['nama_lengkap']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= sanitize($pelanggan['email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="telepon" class="form-control" placeholder="Telepon" value="<?= sanitize($pelanggan['telepon']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="alamat" placeholder="Alamat Lengkap"><?= sanitize($pelanggan['alamat']); ?></textarea>
                                    </div>
                                    <div class="form-button">
                                        <button type="submit" name="update_profil">Simpan Perubahan Profil</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-lg-6">
                                <form class="user-form" method="POST">
                                    <h4 class="mb-3">Ubah Password</h4>
                                    <div class="form-group">
                                        <input type="password" name="password_lama" class="form-control" placeholder="Password Lama" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password_baru" class="form-control" placeholder="Password Baru" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi Password Baru" required>
                                    </div>
                                    <div class="form-button">
                                        <button type="submit" name="update_password">Ubah Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="user-form-remind">
                        <p>Kembali ke <a href="index.php">Beranda</a></p>
                    </div>
                    <div class="user-form-footer">
                        <p><?= sanitize($meta['nama_instansi'] ?? 'Greeny'); ?> | &copy; Copyright by <a href="#">Tim Anda</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="vendor/bootstrap/jquery-1.12.4.min.js"></script>
    <script src="vendor/bootstrap/popper.min.js"></script>
    <script src="vendor/bootstrap/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <?php show_sweetalert(); ?>
</body>
</html>