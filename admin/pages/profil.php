<?php
// Ninda/admin/pages/profil.php

// Ambil ID pengguna yang sedang login dari session
$id_pengguna = $_SESSION['pengguna_id'];

// Proses form jika ada data yang dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =================================================================
    // PROSES UNTUK UPDATE PROFIL
    // =================================================================
    if (isset($_POST['update_profil'])) {
        // Ambil data dan bersihkan sedikit (tidak cukup untuk keamanan penuh)
        $nama_lengkap = $koneksi->real_escape_string($_POST['nama_lengkap']);
        $email = $koneksi->real_escape_string($_POST['email']);
        $telepon = $koneksi->real_escape_string($_POST['telepon']);
        $alamat = $koneksi->real_escape_string($_POST['alamat']);

        if (empty($nama_lengkap) || empty($email)) {
            set_sweetalert('error', 'Gagal!', 'Nama lengkap dan email tidak boleh kosong.');
        } else {
            // ☢️ Cek email duplikat (Query Tidak Aman)
            $check_email_sql = "SELECT id_pengguna FROM pengguna WHERE email = '$email' AND id_pengguna != '$id_pengguna'";
            $result_check = $koneksi->query($check_email_sql);

            if ($result_check->num_rows > 0) {
                set_sweetalert('error', 'Gagal!', 'Email tersebut sudah digunakan oleh pengguna lain.');
            } else {
                // ☢️ Query UPDATE (Tidak Aman)
                $sql = "UPDATE pengguna SET 
                            nama_lengkap = '$nama_lengkap', 
                            email = '$email', 
                            telepon = '$telepon', 
                            alamat = '$alamat' 
                        WHERE id_pengguna = '$id_pengguna'";
                
                if ($koneksi->query($sql)) {
                    $_SESSION['pengguna_nama'] = $nama_lengkap; // Update session
                    set_sweetalert('success', 'Berhasil!', 'Profil Anda telah berhasil diperbarui.');
                } else {
                    set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
                }
            }
        }
    }

    // =================================================================
    // PROSES UNTUK UPDATE PASSWORD (VERSI TIDAK AMAN)
    // =================================================================
    if (isset($_POST['update_password'])) {
        $password_lama = $koneksi->real_escape_string($_POST['password_lama']);
        $password_baru = $koneksi->real_escape_string($_POST['password_baru']);
        $konfirmasi_password = $koneksi->real_escape_string($_POST['konfirmasi_password']);

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            set_sweetalert('error', 'Gagal!', 'Semua kolom password harus diisi.');
        } elseif ($password_baru !== $konfirmasi_password) {
            set_sweetalert('error', 'Gagal!', 'Password baru dan konfirmasi password tidak cocok.');
        } elseif (strlen($password_baru) < 6) {
            set_sweetalert('error', 'Gagal!', 'Password baru minimal harus 6 karakter.');
        } else {
            // ☢️ Ambil password saat ini dari database (Query Tidak Aman)
            $sql_pass = "SELECT password FROM pengguna WHERE id_pengguna = '$id_pengguna'";
            $result_pass = $koneksi->query($sql_pass);
            $password_saat_ini = $result_pass->fetch_assoc()['password'];

            // ☢️ Perbandingan password lama sebagai teks biasa
            if ($password_lama === $password_saat_ini) {
                // ☢️ Update password baru sebagai teks biasa (Query Tidak Aman)
                $sql_update = "UPDATE pengguna SET password = '$password_baru' WHERE id_pengguna = '$id_pengguna'";

                if ($koneksi->query($sql_update)) {
                    set_sweetalert('success', 'Berhasil!', 'Password Anda telah berhasil diubah.');
                } else {
                    set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah password.');
                }
            } else {
                set_sweetalert('error', 'Gagal!', 'Password lama yang Anda masukkan salah.');
            }
        }
    }
    // Refresh halaman untuk menghindari resubmission form dan menampilkan data terbaru
    header("Location: index.php?page=profil");
    exit();
}

// Ambil data terbaru pengguna untuk ditampilkan di form
// ☢️ Query Tidak Aman
$sql_user = "SELECT nama_lengkap, email, telepon, alamat FROM pengguna WHERE id_pengguna = '$id_pengguna'";
$result_user = $koneksi->query($sql_user);
$user = $result_user->fetch_assoc();

?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Profil Saya</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Profil</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=profil" method="POST">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= sanitize($user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" value="<?= sanitize($user['telepon']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= sanitize($user['alamat']); ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ubah Password</h5>
            </div>
            <div class="card-body"> 
                <form action="index.php?page=profil" method="POST">
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="update_password" class="btn btn-danger">Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>