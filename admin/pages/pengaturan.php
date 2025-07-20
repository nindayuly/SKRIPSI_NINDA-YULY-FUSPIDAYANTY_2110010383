<?php
// Ninda/admin/pages/pengaturan.php

// Ambil data pengaturan saat ini untuk ditampilkan di form
// ☢️ Query Tidak Aman
$sql_pengaturan = "SELECT * FROM meta WHERE id_meta = 1";
$result_pengaturan = $koneksi->query($sql_pengaturan);
$pengaturan = $result_pengaturan->fetch_assoc();

// Proses form jika ada data yang dikirim (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data teks dan bersihkan
    $nama_instansi = $koneksi->real_escape_string($_POST['nama_instansi']);
    $pimpinan = $koneksi->real_escape_string($_POST['pimpinan']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    
    // Siapkan bagian query untuk logo
    $logo_query_part = "";

    // Cek apakah ada file logo baru yang di-upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $upload_dir = 'assets/images/'; // PENTING: Path dari index.php ke folder upload
        
        // Buat nama file yang unik untuk menghindari penimpaan
        $nama_file_baru = uniqid() . '-' . basename($file['name']);
        $target_file = $upload_dir . $nama_file_baru;

        // Validasi tipe file (opsional tapi disarankan)
        $tipe_file = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        if (in_array($tipe_file, $allowed_types)) {
            // Hapus logo lama jika ada
            if (!empty($pengaturan['logo']) && file_exists($upload_dir . $pengaturan['logo'])) {
                unlink($upload_dir . $pengaturan['logo']);
            }
            
            // Pindahkan file yang di-upload ke direktori tujuan
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $logo_query_part = ", logo = '$nama_file_baru'";
            } else {
                set_sweetalert('error', 'Gagal!', 'Gagal mengupload logo baru.');
                header("Location: index.php?page=pengaturan");
                exit();
            }
        } else {
            set_sweetalert('error', 'Gagal!', 'Tipe file logo tidak diizinkan. Gunakan JPG, PNG, GIF, atau SVG.');
            header("Location: index.php?page=pengaturan");
            exit();
        }
    }

    // ☢️ Query UPDATE (Tidak Aman)
    $sql_update = "UPDATE meta SET 
                        nama_instansi = '$nama_instansi',
                        pimpinan = '$pimpinan',
                        telepon = '$telepon',
                        email = '$email',
                        alamat = '$alamat'
                        $logo_query_part
                   WHERE id_meta = 1";

    if ($koneksi->query($sql_update)) {
        set_sweetalert('success', 'Berhasil!', 'Pengaturan berhasil disimpan.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
    }
    
    // Redirect untuk refresh halaman dengan data baru
    header("Location: index.php?page=pengaturan");
    exit();
}
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Pengaturan Situs</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Umum</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=pengaturan" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="nama_instansi" class="form-label">Nama Instansi/Toko</label>
                                <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" value="<?= sanitize($pengaturan['nama_instansi'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="pimpinan" class="form-label">Nama Pimpinan</label>
                                <input type="text" class="form-control" id="pimpinan" name="pimpinan" value="<?= sanitize($pengaturan['pimpinan'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon" value="<?= sanitize($pengaturan['telepon'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($pengaturan['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" id="alamat" rows="3"><?= sanitize($pengaturan['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="logo" class="form-label">Ganti Logo</label>
                                <input class="form-control" type="file" id="logo" name="logo">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti logo.</small>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label">Logo Saat Ini</label>
                            <div>
                                <?php if (!empty($pengaturan['logo']) && file_exists('assets/images/' . $pengaturan['logo'])): ?>
                                    <img src="assets/images/<?= sanitize($pengaturan['logo']); ?>" alt="Logo Saat Ini" style="max-height: 80px; background-color: #f0f0f0; padding: 5px; border-radius: 5px;">
                                <?php else: ?>
                                    <span class="text-muted">Logo belum diatur.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>