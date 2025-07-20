<?php
// Ninda/admin/pages/promosi.php

$action = $_GET['action'] ?? 'list';
$id_promosi = (int)($_GET['id'] ?? null);

// =================================================================
// PROSES FORM (CREATE & UPDATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_promo = $koneksi->real_escape_string(strtoupper($_POST['kode_promo']));
    $deskripsi = $koneksi->real_escape_string($_POST['deskripsi']);
    $tipe_diskon = $koneksi->real_escape_string($_POST['tipe_diskon']);
    $nilai_diskon = (int)$_POST['nilai_diskon'];
    $min_pembelian = (int)$_POST['min_pembelian'];
    $tgl_mulai = $koneksi->real_escape_string($_POST['tgl_mulai']);
    $tgl_berakhir = $koneksi->real_escape_string($_POST['tgl_berakhir']);
    $kuota_penggunaan = (int)$_POST['kuota_penggunaan'];
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if (empty($kode_promo) || empty($nilai_diskon) || empty($tgl_mulai) || empty($tgl_berakhir)) {
        set_sweetalert('error', 'Gagal!', 'Kolom yang wajib diisi tidak boleh kosong.');
    } else {
        if ($action === 'tambah') {
            $sql = "INSERT INTO promosi (kode_promo, deskripsi, tipe_diskon, nilai_diskon, min_pembelian, tgl_mulai, tgl_berakhir, kuota_penggunaan, status_aktif) 
                    VALUES ('$kode_promo', '$deskripsi', '$tipe_diskon', '$nilai_diskon', '$min_pembelian', '$tgl_mulai', '$tgl_berakhir', '$kuota_penggunaan', '$status_aktif')";
            $koneksi->query($sql);
            set_sweetalert('success', 'Berhasil!', 'Promosi baru berhasil ditambahkan.');
        } elseif ($action === 'edit' && $id_promosi) {
            $sql = "UPDATE promosi SET 
                        kode_promo = '$kode_promo', 
                        deskripsi = '$deskripsi', 
                        tipe_diskon = '$tipe_diskon', 
                        nilai_diskon = '$nilai_diskon', 
                        min_pembelian = '$min_pembelian', 
                        tgl_mulai = '$tgl_mulai', 
                        tgl_berakhir = '$tgl_berakhir', 
                        kuota_penggunaan = '$kuota_penggunaan',
                        status_aktif = '$status_aktif'
                    WHERE id_promosi = '$id_promosi'";
            $koneksi->query($sql);
            set_sweetalert('success', 'Berhasil!', 'Data promosi berhasil diperbarui.');
        }
        header("Location: index.php?page=promosi");
        exit();
    }
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_promosi) {
    $sql = "DELETE FROM promosi WHERE id_promosi = '$id_promosi'";
    $koneksi->query($sql);
    set_sweetalert('success', 'Berhasil!', 'Promosi telah dihapus.');
    header("Location: index.php?page=promosi");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Promosi Baru";
$data_edit = null;

if ($action === 'edit' && $id_promosi) {
    $form_title = "Edit Promosi";
    $sql_edit = "SELECT * FROM promosi WHERE id_promosi = '$id_promosi'";
    $result_edit = $koneksi->query($sql_edit);
    $data_edit = $result_edit->fetch_assoc();
}

$sql_list = "SELECT * FROM promosi ORDER BY tgl_berakhir DESC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Promosi / Kupon</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Promosi</li>
        </ol>
    </div>
</div>

<?php if ($action === 'tambah' || $action === 'edit'): ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><?= $form_title; ?></h5></div>
            <div class="card-body">
                <form action="index.php?page=promosi&action=<?= $action ?><?= $id_promosi ? '&id='.$id_promosi : '' ?>" method="POST">
                    <div class="mb-3">
                        <label for="kode_promo" class="form-label">Kode Promo</label>
                        <input type="text" class="form-control" id="kode_promo" name="kode_promo" value="<?= sanitize($data_edit['kode_promo'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"><?= sanitize($data_edit['deskripsi'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipe_diskon" class="form-label">Tipe Diskon</label>
                            <select class="form-select" name="tipe_diskon" required>
                                <option value="percentage" <?= (($data_edit['tipe_diskon'] ?? '') === 'percentage') ? 'selected' : ''; ?>>Persentase (%)</option>
                                <option value="fixed" <?= (($data_edit['tipe_diskon'] ?? '') === 'fixed') ? 'selected' : ''; ?>>Potongan Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                             <label for="nilai_diskon" class="form-label">Nilai Diskon</label>
                            <input type="number" class="form-control" id="nilai_diskon" name="nilai_diskon" value="<?= sanitize($data_edit['nilai_diskon'] ?? '0'); ?>" required>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                             <label for="min_pembelian" class="form-label">Minimal Pembelian (Rp)</label>
                            <input type="number" class="form-control" id="min_pembelian" name="min_pembelian" value="<?= sanitize($data_edit['min_pembelian'] ?? '0'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kuota_penggunaan" class="form-label">Kuota Penggunaan</label>
                            <input type="number" class="form-control" id="kuota_penggunaan" name="kuota_penggunaan" value="<?= sanitize($data_edit['kuota_penggunaan'] ?? '100'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tgl_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" class="form-control" id="tgl_mulai" name="tgl_mulai" value="<?= !empty($data_edit['tgl_mulai']) ? date('Y-m-d\TH:i', strtotime($data_edit['tgl_mulai'])) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tgl_berakhir" class="form-label">Tanggal Berakhir</label>
                            <input type="datetime-local" class="form-control" id="tgl_berakhir" name="tgl_berakhir" value="<?= !empty($data_edit['tgl_berakhir']) ? date('Y-m-d\TH:i', strtotime($data_edit['tgl_berakhir'])) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="status_aktif" name="status_aktif" value="1" <?= (($data_edit['status_aktif'] ?? 1) == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="status_aktif">Aktifkan Promosi</label>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=promosi" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: // Tampilan Daftar Promosi ?>

<div class="card">
    <div class="card-header"><h5 class="card-title float-start">Daftar Promosi</h5>
        <a href="index.php?page=promosi&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Tambah Promosi</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Kode Promo</th>
                        <th>Deskripsi</th>
                        <th>Diskon</th>
                        <th>Masa Berlaku</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td><strong><?= sanitize($row['kode_promo']); ?></strong></td>
                        <td><?= sanitize($row['deskripsi']); ?></td>
                        <td>
                            <?php 
                                if ($row['tipe_diskon'] == 'percentage') {
                                    echo $row['nilai_diskon'] . '%';
                                } else {
                                    echo format_rupiah($row['nilai_diskon']);
                                }
                            ?>
                        </td>
                        <td><?= tgl_indo(date('Y-m-d', strtotime($row['tgl_mulai']))); ?> - <?= tgl_indo(date('Y-m-d', strtotime($row['tgl_berakhir']))); ?></td>
                        <td><?= $row['kuota_penggunaan']; ?></td>
                        <td>
                            <?php if($row['status_aktif']): ?>
                                <span class="badge bg-success-subtle text-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?page=promosi&action=edit&id=<?= $row['id_promosi']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=promosi&action=hapus&id=<?= $row['id_promosi']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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
                text: "Data promosi ini akan dihapus permanen!",
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