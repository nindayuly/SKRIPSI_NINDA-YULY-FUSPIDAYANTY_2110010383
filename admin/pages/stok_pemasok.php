<?php
// Ninda/admin/pages/stok_pemasok.php

// Logika untuk menangani aksi (tambah, edit, hapus)
$action = $_GET['action'] ?? 'list';
$id_pemasok = $_GET['id'] ?? null;

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemasok = $koneksi->real_escape_string($_POST['nama_pemasok']);
    $telepon = $koneksi->real_escape_string($_POST['telepon']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $catatan = $koneksi->real_escape_string($_POST['catatan']);

    if (empty($nama_pemasok)) {
        set_sweetalert('error', 'Gagal!', 'Nama Pemasok tidak boleh kosong.');
    } else {
        if ($action === 'tambah') {
            // ☢️ Query INSERT (Tidak Aman)
            $sql = "INSERT INTO pemasok (nama_pemasok, telepon, email, alamat, catatan) VALUES ('$nama_pemasok', '$telepon', '$email', '$alamat', '$catatan')";
            $koneksi->query($sql);
            set_sweetalert('success', 'Berhasil!', 'Pemasok baru berhasil ditambahkan.');
        } elseif ($action === 'edit' && $id_pemasok) {
            // ☢️ Query UPDATE (Tidak Aman)
            $sql = "UPDATE pemasok SET 
                        nama_pemasok = '$nama_pemasok', 
                        telepon = '$telepon', 
                        email = '$email', 
                        alamat = '$alamat',
                        catatan = '$catatan'
                    WHERE id_pemasok = '$id_pemasok'";
            $koneksi->query($sql);
            set_sweetalert('success', 'Berhasil!', 'Data pemasok berhasil diperbarui.');
        }
        header("Location: index.php?page=stok_pemasok");
        exit();
    }
     // Redirect untuk menghindari resubmit jika validasi gagal
     header("Location: " . $_SERVER['REQUEST_URI']);
     exit();
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_pemasok) {
    // ☢️ Query DELETE (Tidak Aman)
    // Catatan: Relasi di database (ON DELETE SET NULL) akan membuat id_pemasok di tabel pembelian_stok menjadi NULL, bukan menghapus data pembeliannya.
    $sql = "DELETE FROM pemasok WHERE id_pemasok = '$id_pemasok'";
    if ($koneksi->query($sql)) {
        set_sweetalert('success', 'Berhasil!', 'Pemasok telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus pemasok. Mungkin masih terhubung dengan data pembelian.');
    }
    header("Location: index.php?page=stok_pemasok");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Pemasok";
$data_edit = null;

if ($action === 'edit' && $id_pemasok) {
    $form_title = "Edit Pemasok";
    $sql_edit = "SELECT * FROM pemasok WHERE id_pemasok = '$id_pemasok'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

// Ambil semua data pemasok untuk ditampilkan di tabel
$sql_list = "SELECT * FROM pemasok ORDER BY nama_pemasok ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Pemasok</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Pemasok</li>
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
                <form action="index.php?page=stok_pemasok&action=<?= $action ?><?= $id_pemasok ? '&id='.$id_pemasok : '' ?>" method="POST">
                    <div class="mb-3">
                        <label for="nama_pemasok" class="form-label">Nama Pemasok</label>
                        <input type="text" class="form-control" id="nama_pemasok" name="nama_pemasok" value="<?= sanitize($data_edit['nama_pemasok'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" value="<?= sanitize($data_edit['telepon'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($data_edit['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" id="alamat" rows="3"><?= sanitize($data_edit['alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" id="catatan" rows="2"><?= sanitize($data_edit['catatan'] ?? ''); ?></textarea>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=stok_pemasok" class="btn btn-secondary">Batal</a>
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
        <h5 class="card-title float-start">Daftar Pemasok</h5>
        <a href="index.php?page=stok_pemasok&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Pemasok</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th>Nama Pemasok</th>
                        <th>Telepon</th>
                        <th>Email</th>
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
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= sanitize($row['nama_pemasok']); ?></td>
                        <td><?= sanitize($row['telepon']); ?></td>
                        <td><?= sanitize($row['email']); ?></td>
                        <td class="text-center">
                            <a href="index.php?page=stok_pemasok&action=edit&id=<?= $row['id_pemasok']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=stok_pemasok&action=hapus&id=<?= $row['id_pemasok']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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
                text: "Data pemasok ini akan dihapus!",
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