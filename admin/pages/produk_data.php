<?php
// Ninda/admin/pages/produk_data.php

$action = $_GET['action'] ?? 'list';
$id_produk = $_GET['id'] ?? null;
$upload_dir = 'assets/images/produk/'; // Path dari index.php ke folder upload gambar produk

// Pastikan direktori upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dan bersihkan
    $nama_produk = $koneksi->real_escape_string($_POST['nama_produk']);
    $id_kategori = $koneksi->real_escape_string($_POST['id_kategori']);
    $kode_produk = $koneksi->real_escape_string($_POST['kode_produk']);
    $merk = $koneksi->real_escape_string($_POST['merk']);
    $deskripsi = $koneksi->real_escape_string($_POST['deskripsi']);
    $harga_jual = (int)$_POST['harga_jual'];
    $stok = (int)$_POST['stok'];
    $berat_gram = (int)$_POST['berat_gram'];
    $kondisi = $koneksi->real_escape_string($_POST['kondisi']);

    // Validasi dasar
    if (empty($nama_produk) || empty($id_kategori) || empty($harga_jual)) {
        set_sweetalert('error', 'Gagal!', 'Nama Produk, Kategori, dan Harga Jual tidak boleh kosong.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    
    $nama_file_gambar = '';

    // Logika upload gambar
    if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gambar_produk'];
        $nama_file_baru = uniqid() . '-' . basename($file['name']);
        $target_file = $upload_dir . $nama_file_baru;

        // Pindahkan file baru
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $nama_file_gambar = $nama_file_baru;

            // Jika ini adalah aksi edit dan ada gambar lama, hapus gambar lama
            if ($action === 'edit' && !empty($_POST['gambar_lama'])) {
                if (file_exists($upload_dir . $_POST['gambar_lama'])) {
                    unlink($upload_dir . $_POST['gambar_lama']);
                }
            }
        } else {
            set_sweetalert('error', 'Gagal!', 'Gagal mengupload gambar produk.');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    if ($action === 'tambah') {
        // ☢️ Query INSERT (Tidak Aman)
        $sql = "INSERT INTO produk (nama_produk, id_kategori, kode_produk, merk, deskripsi, harga_jual, stok, berat_gram, kondisi, gambar_produk) 
                VALUES ('$nama_produk', '$id_kategori', '$kode_produk', '$merk', '$deskripsi', '$harga_jual', '$stok', '$berat_gram', '$kondisi', '$nama_file_gambar')";
    } elseif ($action === 'edit' && $id_produk) {
        // Logika untuk update gambar: hanya jika ada gambar baru yang diupload
        $gambar_query_part = "";
        if (!empty($nama_file_gambar)) {
            $gambar_query_part = ", gambar_produk = '$nama_file_gambar'";
        }
        
        // ☢️ Query UPDATE (Tidak Aman)
        $sql = "UPDATE produk SET 
                    nama_produk = '$nama_produk', 
                    id_kategori = '$id_kategori',
                    kode_produk = '$kode_produk',
                    merk = '$merk',
                    deskripsi = '$deskripsi',
                    harga_jual = '$harga_jual',
                    stok = '$stok',
                    berat_gram = '$berat_gram',
                    kondisi = '$kondisi'
                    $gambar_query_part
                WHERE id_produk = '$id_produk'";
    }

    if (isset($sql) && $koneksi->query($sql)) {
        set_sweetalert('success', 'Berhasil!', 'Data produk berhasil disimpan.');
        header("Location: index.php?page=produk_data");
        exit();
    } else {
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_produk) {
    // Ambil nama file gambar sebelum menghapus record dari database
    $sql_get_gambar = "SELECT gambar_produk FROM produk WHERE id_produk = '$id_produk'";
    $result_gambar = $koneksi->query($sql_get_gambar);
    if($result_gambar->num_rows > 0) {
        $data_produk = $result_gambar->fetch_assoc();
        $gambar_untuk_dihapus = $data_produk['gambar_produk'];
    }

    // ☢️ Query DELETE (Tidak Aman)
    $sql = "DELETE FROM produk WHERE id_produk = '$id_produk'";
    if ($koneksi->query($sql)) {
        // Jika record berhasil dihapus, hapus juga file gambarnya
        if (!empty($gambar_untuk_dihapus) && file_exists($upload_dir . $gambar_untuk_dihapus)) {
            unlink($upload_dir . $gambar_untuk_dihapus);
        }
        set_sweetalert('success', 'Berhasil!', 'Produk telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus produk.');
    }
    header("Location: index.php?page=produk_data");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Produk";
$data_edit = null;

// Ambil daftar kategori untuk dropdown
$kategori_list = $koneksi->query("SELECT id_kategori, nama_kategori FROM kategori_produk ORDER BY nama_kategori ASC");

if ($action === 'edit' && $id_produk) {
    $form_title = "Edit Produk";
    $sql_edit = "SELECT * FROM produk WHERE id_produk = '$id_produk'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

// Ambil semua data produk untuk ditampilkan di tabel
$sql_list = "SELECT produk.*, kategori_produk.nama_kategori 
             FROM produk 
             LEFT JOIN kategori_produk ON produk.id_kategori = kategori_produk.id_kategori 
             ORDER BY produk.nama_produk ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Data Produk</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Data Produk</li>
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
                <form action="index.php?page=produk_data&action=<?= $action ?><?= $id_produk ? '&id='.$id_produk : '' ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= sanitize($data_edit['nama_produk'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="5"><?= sanitize($data_edit['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="id_kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="id_kategori" name="id_kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php while($kategori = $kategori_list->fetch_assoc()): ?>
                                        <option value="<?= $kategori['id_kategori']; ?>" <?= (($data_edit['id_kategori'] ?? '') == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                                            <?= sanitize($kategori['nama_kategori']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                             <div class="mb-3">
                                <label for="kode_produk" class="form-label">Kode Produk (SKU)</label>
                                <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="<?= sanitize($data_edit['kode_produk'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="merk" class="form-label">Merk</label>
                                <input type="text" class="form-control" id="merk" name="merk" value="<?= sanitize($data_edit['merk'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-4">
                             <div class="mb-3">
                                <label for="harga_jual" class="form-label">Harga Jual (Rp)</label>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="<?= sanitize($data_edit['harga_jual'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                             <div class="mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" value="<?= sanitize($data_edit['stok'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                             <div class="mb-3">
                                <label for="berat_gram" class="form-label">Berat (gram)</label>
                                <input type="number" class="form-control" id="berat_gram" name="berat_gram" value="<?= sanitize($data_edit['berat_gram'] ?? '0'); ?>">
                            </div>
                        </div>
                        <div class="col-lg-4">
                             <div class="mb-3">
                                <label for="kondisi" class="form-label">Kondisi</label>
                                <select class="form-select" name="kondisi" id="kondisi">
                                    <option value="Baru" <?= (($data_edit['kondisi'] ?? '') === 'Baru') ? 'selected' : ''; ?>>Baru</option>
                                    <option value="Bekas" <?= (($data_edit['kondisi'] ?? '') === 'Bekas') ? 'selected' : ''; ?>>Bekas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-8">
                             <div class="mb-3">
                                <label for="gambar_produk" class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" id="gambar_produk" name="gambar_produk">
                                <input type="hidden" name="gambar_lama" value="<?= sanitize($data_edit['gambar_produk'] ?? ''); ?>">
                                <?php if($action === 'edit' && !empty($data_edit['gambar_produk'])): ?>
                                    <small class="form-text text-muted">Gambar saat ini: <?= sanitize($data_edit['gambar_produk']); ?>. Kosongkan jika tidak ingin mengganti.</small>
                                    <img src="assets/images/produk/<?= sanitize($data_edit['gambar_produk']); ?>" alt="Gambar Produk" class="img-thumbnail mt-2" width="100">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <a href="index.php?page=produk_data" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title float-start">Daftar Produk</h5>
        <a href="index.php?page=produk_data&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Produk</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="10%" class="text-center">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center">
                            <?php if(!empty($row['gambar_produk']) && file_exists($upload_dir . $row['gambar_produk'])): ?>
                                <img src="assets/images/produk/<?= sanitize($row['gambar_produk']); ?>" alt="<?= sanitize($row['nama_produk']); ?>" width="50" class="img-thumbnail">
                            <?php else: ?>
                                <img src="assets/images/produk/default.png" alt="default" width="50" class="img-thumbnail">
                            <?php endif; ?>
                        </td>
                        <td><?= sanitize($row['nama_produk']); ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= sanitize($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></td>
                        <td><?= format_rupiah($row['harga_jual']); ?></td>
                        <td><?= $row['stok']; ?></td>
                        <td class="text-center">
                            <a href="index.php?page=produk_data&action=edit&id=<?= $row['id_produk']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=produk_data&action=hapus&id=<?= $row['id_produk']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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
                text: "Produk ini akan dihapus beserta gambarnya!",
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