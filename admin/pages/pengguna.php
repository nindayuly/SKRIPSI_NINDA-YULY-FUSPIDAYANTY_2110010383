<?php
// Ninda/admin/pages/pengguna.php

// Logika untuk menangani aksi (tambah, edit, hapus)
$action = $_GET['action'] ?? 'list'; // Default action adalah 'list'
$id_pengguna = $_GET['id'] ?? null;

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $peran = $koneksi->real_escape_string($_POST['peran']);
    $password = $koneksi->real_escape_string($_POST['password']);

    if ($action === 'tambah') {
        // Cek email duplikat
        $sql_check = "SELECT id_pengguna FROM pengguna WHERE email = '$email'";
        if ($koneksi->query($sql_check)->num_rows > 0) {
            set_sweetalert('error', 'Gagal!', 'Email sudah terdaftar.');
        } elseif (empty($password)) {
            set_sweetalert('error', 'Gagal!', 'Password wajib diisi untuk pengguna baru.');
        } else {
            // ☢️ Query INSERT (Tidak Aman)
            $sql = "INSERT INTO pengguna (nama_lengkap, email, telepon, peran, password) VALUES ('$nama_lengkap', '$email', '$telepon', '$peran', '$password')";
            if ($koneksi->query($sql)) {
                set_sweetalert('success', 'Berhasil!', 'Pengguna baru berhasil ditambahkan.');
                header("Location: index.php?page=pengguna");
                exit();
            } else {
                set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
            }
        }
    } elseif ($action === 'edit' && $id_pengguna) {
        // Cek email duplikat oleh pengguna lain
        $sql_check = "SELECT id_pengguna FROM pengguna WHERE email = '$email' AND id_pengguna != '$id_pengguna'";
        if ($koneksi->query($sql_check)->num_rows > 0) {
            set_sweetalert('error', 'Gagal!', 'Email sudah digunakan oleh pengguna lain.');
        } else {
            // Logika untuk update password: hanya jika diisi
            $password_query_part = "";
            if (!empty($password)) {
                $password_query_part = ", password = '$password'";
            }
            
            // ☢️ Query UPDATE (Tidak Aman)
            $sql = "UPDATE pengguna SET 
                        nama_lengkap = '$nama_lengkap', 
                        email = '$email', 
                        telepon = '$telepon', 
                        peran = '$peran' 
                        $password_query_part 
                    WHERE id_pengguna = '$id_pengguna'";

            if ($koneksi->query($sql)) {
                set_sweetalert('success', 'Berhasil!', 'Data pengguna berhasil diperbarui.');
                header("Location: index.php?page=pengguna");
                exit();
            } else {
                set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
            }
        }
    }
    // Redirect untuk menghindari resubmit
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_pengguna) {
    // Mencegah pengguna menghapus dirinya sendiri
    if ($id_pengguna == $_SESSION['pengguna_id']) {
        set_sweetalert('error', 'Aksi Ditolak!', 'Anda tidak dapat menghapus akun Anda sendiri.');
    } else {
        // ☢️ Query DELETE (Tidak Aman)
        $sql = "DELETE FROM pengguna WHERE id_pengguna = '$id_pengguna'";
        if ($koneksi->query($sql)) {
            set_sweetalert('success', 'Berhasil!', 'Pengguna telah dihapus.');
        } else {
            set_sweetalert('error', 'Gagal!', 'Gagal menghapus pengguna.');
        }
    }
    header("Location: index.php?page=pengguna");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Pengguna";
$data_edit = null;

if ($action === 'edit' && $id_pengguna) {
    $form_title = "Edit Pengguna";
    // ☢️ Query SELECT (Tidak Aman)
    $sql_edit = "SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

// Ambil semua data pengguna untuk ditampilkan di tabel
$sql_list = "SELECT * FROM pengguna ORDER BY nama_lengkap ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Pengguna</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Pengguna</li>
        </ol>
    </div>
</div>

<?php if ($action === 'tambah' || $action === 'edit'): ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $form_title; ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=pengguna&action=<?= $action ?><?= $id_pengguna ? '&id='.$id_pengguna : '' ?>" method="POST">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= sanitize($data_edit['nama_lengkap'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($data_edit['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" value="<?= sanitize($data_edit['telepon'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="peran" class="form-label">Peran</label>
                        <select class="form-select" id="peran" name="peran" required>
                            <option value="pelanggan" <?= (($data_edit['peran'] ?? '') === 'pelanggan') ? 'selected' : ''; ?>>Pelanggan</option> 
                            <option value="admin" <?= (($data_edit['peran'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" <?= ($action === 'tambah') ? 'required' : ''; ?>>
                        <?php if ($action === 'edit'): ?>
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=pengguna" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title float-start">Daftar Pengguna</h5>
        <a href="index.php?page=pengguna&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Pengguna</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Peran</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= sanitize($row['nama_lengkap']); ?></td>
                        <td><?= sanitize($row['email']); ?></td>
                        <td><?= sanitize($row['telepon']); ?></td>
                        <td><span class="badge bg-primary-subtle text-primary"><?= ucfirst(sanitize($row['peran'])); ?></span></td>
                        <td width="10%" class="text-center">
                            <a href="index.php?page=pengguna&action=edit&id=<?= $row['id_pengguna']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=pengguna&action=hapus&id=<?= $row['id_pengguna']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const tombolHapus = document.querySelectorAll('.tombol-hapus');
    tombolHapus.forEach(function(tombol) {
        tombol.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pengguna ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>