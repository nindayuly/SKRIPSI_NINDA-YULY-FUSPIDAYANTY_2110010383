<?php
// Ninda/admin/pages/kurir.php

$action = $_GET['action'] ?? 'list';
$id_kurir = (int)($_GET['id'] ?? null);

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kurir = $koneksi->real_escape_string($_POST['nama_kurir']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $nomor_polisi = $koneksi->real_escape_string($_POST['nomor_polisi']);
    $password = $koneksi->real_escape_string($_POST['password']);

    // Validasi dasar
    if (empty($nama_kurir) || empty($email) || empty($telepon)) {
        set_sweetalert('error', 'Gagal!', 'Nama, Email, dan Telepon wajib diisi.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    $koneksi->begin_transaction();
    try {
        if ($action === 'tambah') {
            if (empty($password)) {
                throw new Exception("Password wajib diisi untuk kurir baru.");
            }
            // Cek email duplikat
            $sql_check = "SELECT id_pengguna FROM pengguna WHERE email = '$email'";
            if ($koneksi->query($sql_check)->num_rows > 0) {
                throw new Exception("Email sudah terdaftar. Gunakan email lain.");
            }

            // 1. Insert ke tabel pengguna dengan peran 'kurir'
            $sql_pengguna = "INSERT INTO pengguna (nama_lengkap, email, telepon, password, peran) VALUES ('$nama_kurir', '$email', '$telepon', '$password', 'kurir')";
            $koneksi->query($sql_pengguna);
            $id_pengguna_baru = $koneksi->insert_id;

            // 2. Insert ke tabel kurir_internal
            $sql_kurir = "INSERT INTO kurir_internal (id_pengguna, nama_kurir, telepon_kurir, nomor_polisi) VALUES ('$id_pengguna_baru', '$nama_kurir', '$telepon', '$nomor_polisi')";
            $koneksi->query($sql_kurir);

            set_sweetalert('success', 'Berhasil!', 'Kurir baru berhasil ditambahkan.');
        } 
        elseif ($action === 'edit' && $id_kurir) {
            $id_pengguna_edit = (int)$_POST['id_pengguna'];
            
            // 1. Update tabel kurir_internal
            $sql_kurir = "UPDATE kurir_internal SET nama_kurir = '$nama_kurir', telepon_kurir = '$telepon', nomor_polisi = '$nomor_polisi' WHERE id_kurir = '$id_kurir'";
            $koneksi->query($sql_kurir);

            // 2. Update tabel pengguna
            $password_part = !empty($password) ? ", password = '$password'" : "";
            $sql_pengguna = "UPDATE pengguna SET nama_lengkap = '$nama_kurir', email = '$email', telepon = '$telepon' $password_part WHERE id_pengguna = '$id_pengguna_edit'";
            $koneksi->query($sql_pengguna);

            set_sweetalert('success', 'Berhasil!', 'Data kurir berhasil diperbarui.');
        }
        $koneksi->commit();
    } catch (Exception $e) {
        $koneksi->rollback();
        set_sweetalert('error', 'Gagal!', $e->getMessage());
    }
    
    header("Location: index.php?page=kurir");
    exit();
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_kurir) {
    // ☢️ Query DELETE (Tidak Aman)
    // Menghapus dari tabel kurir_internal akan otomatis menghapus dari tabel pengguna karena relasi ON DELETE CASCADE
    $sql = "DELETE FROM kurir_internal WHERE id_kurir = '$id_kurir'";
    if ($koneksi->query($sql)) {
        set_sweetalert('success', 'Berhasil!', 'Kurir telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus kurir. Mungkin terhubung dengan data pesanan.');
    }
    header("Location: index.php?page=kurir");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Kurir Baru";
$data_edit = null;

if ($action === 'edit' && $id_kurir) {
    $form_title = "Edit Data Kurir";
    $sql_edit = "SELECT ki.*, pg.email FROM kurir_internal ki JOIN pengguna pg ON ki.id_pengguna = pg.id_pengguna WHERE ki.id_kurir = '$id_kurir'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

// Ambil semua data kurir untuk ditampilkan di tabel
$sql_list = "SELECT ki.*, pg.email FROM kurir_internal ki JOIN pengguna pg ON ki.id_pengguna = pg.id_pengguna ORDER BY ki.nama_kurir ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Kurir</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Kurir</li>
        </ol>
    </div>
</div>

<?php if ($action === 'tambah' || $action === 'edit'): ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $form_title; ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=kurir&action=<?= $action ?><?= $id_kurir ? '&id='.$id_kurir : '' ?>" method="POST">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id_pengguna" value="<?= $data_edit['id_pengguna']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="nama_kurir" class="form-label">Nama Lengkap Kurir</label>
                        <input type="text" class="form-control" id="nama_kurir" name="nama_kurir" value="<?= sanitize($data_edit['nama_kurir'] ?? ''); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="email" class="form-label">Email (untuk login)</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($data_edit['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon" value="<?= sanitize($data_edit['telepon_kurir'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomor_polisi" class="form-label">Nomor Polisi Kendaraan</label>
                                <input type="text" class="form-control" id="nomor_polisi" name="nomor_polisi" value="<?= sanitize($data_edit['nomor_polisi'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" <?= ($action === 'tambah') ? 'required' : ''; ?>>
                                <?php if ($action === 'edit'): ?>
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=kurir" class="btn btn-secondary">Batal</a>
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
        <h5 class="card-title float-start">Daftar Kurir Internal</h5>
        <a href="index.php?page=kurir&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Kurir</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th>Nama Kurir</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>No. Polisi</th>
                        <th>Status</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td width="5%" class="text-center"><?= $no++; ?></td>
                        <td><?= sanitize($row['nama_kurir']); ?></td>
                        <td><?= sanitize($row['email']); ?></td>
                        <td><?= sanitize($row['telepon_kurir']); ?></td>
                        <td><?= sanitize($row['nomor_polisi']); ?></td>
                        <td>
                            <?php 
                                $status = $row['status'];
                                $badge_class = 'bg-success-subtle text-success';
                                if ($status == 'bertugas') $badge_class = 'bg-warning-subtle text-warning';
                                if ($status == 'nonaktif') $badge_class = 'bg-secondary-subtle text-secondary';
                            ?>
                            <span class="badge <?= $badge_class; ?>"><?= ucfirst($status); ?></span>
                        </td>
                        <td width="10%" class="text-center">
                            <a href="index.php?page=kurir&action=edit&id=<?= $row['id_kurir']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=kurir&action=hapus&id=<?= $row['id_kurir']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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
    document.querySelectorAll('.tombol-hapus').forEach(function(tombol) {
        tombol.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data kurir ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
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