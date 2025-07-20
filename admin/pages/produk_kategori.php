<?php
// Ninda/admin/pages/produk_kategori.php

// Logika untuk menangani aksi (tambah, edit, hapus)
$action = $_GET['action'] ?? 'list';
$id_kategori = $_GET['id'] ?? null;

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = $koneksi->real_escape_string($_POST['nama_kategori']);
    $slug = create_slug($nama_kategori); // Buat slug dari nama kategori

    if ($action === 'tambah') {
        if (empty($nama_kategori)) {
            set_sweetalert('error', 'Gagal!', 'Nama Kategori tidak boleh kosong.');
        } else {
            // ☢️ Query INSERT (Tidak Aman)
            $sql = "INSERT INTO kategori_produk (nama_kategori, slug) VALUES ('$nama_kategori', '$slug')";
            if ($koneksi->query($sql)) {
                set_sweetalert('success', 'Berhasil!', 'Kategori baru berhasil ditambahkan.');
                header("Location: index.php?page=produk_kategori");
                exit();
            } else {
                set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
            }
        }
    } elseif ($action === 'edit' && $id_kategori) {
        if (empty($nama_kategori)) {
            set_sweetalert('error', 'Gagal!', 'Nama Kategori tidak boleh kosong.');
        } else {
            // ☢️ Query UPDATE (Tidak Aman)
            $sql = "UPDATE kategori_produk SET nama_kategori = '$nama_kategori', slug = '$slug' WHERE id_kategori = '$id_kategori'";
            if ($koneksi->query($sql)) {
                set_sweetalert('success', 'Berhasil!', 'Kategori berhasil diperbarui.');
                header("Location: index.php?page=produk_kategori");
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
if ($action === 'hapus' && $id_kategori) {
    // ☢️ Query DELETE (Tidak Aman)
    $sql = "DELETE FROM kategori_produk WHERE id_kategori = '$id_kategori'";
    if ($koneksi->query($sql)) {
        set_sweetalert('success', 'Berhasil!', 'Kategori telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus kategori. Pastikan tidak ada produk yang menggunakan kategori ini.');
    }
    header("Location: index.php?page=produk_kategori");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Kategori";
$data_edit = null;

if ($action === 'edit' && $id_kategori) {
    $form_title = "Edit Kategori";
    // ☢️ Query SELECT (Tidak Aman)
    $sql_edit = "SELECT * FROM kategori_produk WHERE id_kategori = '$id_kategori'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

// Ambil semua data kategori untuk ditampilkan di tabel
$sql_list = "SELECT * FROM kategori_produk ORDER BY nama_kategori ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Kategori Produk</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=produk_data">Produk</a></li>
            <li class="breadcrumb-item active">Kategori Produk</li>
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
                <form action="index.php?page=produk_kategori&action=<?= $action ?><?= $id_kategori ? '&id='.$id_kategori : '' ?>" method="POST">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= sanitize($data_edit['nama_kategori'] ?? ''); ?>" required>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=produk_kategori" class="btn btn-secondary">Batal</a>
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
        <h5 class="card-title float-start">Daftar Kategori</h5>
        <a href="index.php?page=produk_kategori&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Kategori</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th>Nama Kategori</th>
                        <th>Slug</th>
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
                        <td><?= sanitize($row['nama_kategori']); ?></td>
                        <td><?= sanitize($row['slug']); ?></td>
                        <td class="text-center">
                            <a href="index.php?page=produk_kategori&action=edit&id=<?= $row['id_kategori']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=produk_kategori&action=hapus&id=<?= $row['id_kategori']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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
                text: "Kategori ini akan dihapus!",
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