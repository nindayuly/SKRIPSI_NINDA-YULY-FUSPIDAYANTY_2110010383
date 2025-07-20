<?php
// Ninda/admin/pages/stok_pembelian.php

$action = $_GET['action'] ?? 'list';
$id_pembelian = $_GET['id'] ?? null;

// =================================================================
// PROSES FORM (HANYA CREATE)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'tambah') {
    $id_pemasok = (int)($_POST['id_pemasok'] ?? 0);
    $tanggal_pembelian = $koneksi->real_escape_string($_POST['tanggal_pembelian']);
    $nomor_referensi = $koneksi->real_escape_string($_POST['nomor_referensi']);
    $catatan = $koneksi->real_escape_string($_POST['catatan']);
    
    // Status awal adalah 'dipesan', total biaya akan diupdate nanti saat item ditambahkan
    $status = 'dipesan';
    $total_biaya = 0;

    if (empty($id_pemasok) || empty($tanggal_pembelian)) {
        set_sweetalert('error', 'Gagal!', 'Pemasok dan Tanggal Pembelian wajib diisi.');
        header("Location: index.php?page=stok_pembelian&action=tambah");
        exit();
    } else {
        // ☢️ Query INSERT (Tidak Aman)
        $sql = "INSERT INTO pembelian_stok (id_pemasok, tanggal_pembelian, nomor_referensi, total_biaya, status, catatan) 
                VALUES ('$id_pemasok', '$tanggal_pembelian', '$nomor_referensi', '$total_biaya', '$status', '$catatan')";
        
        if ($koneksi->query($sql)) {
            $last_id = $koneksi->insert_id;
            set_sweetalert('success', 'Berhasil!', 'Catatan pembelian baru telah dibuat. Sekarang tambahkan item produk.');
            // Arahkan ke halaman detail untuk menambahkan produk
            header("Location: index.php?page=stok_pembelian_detail&id=" . $last_id); 
            exit();
        } else {
            set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $koneksi->error);
            header("Location: index.php?page=stok_pembelian&action=tambah");
            exit();
        }
    }
}

// =================================================================
// PROSES HAPUS
// =================================================================
if ($action === 'hapus' && $id_pembelian) {
    // Sebagai pengaman, idealnya hanya pembelian dengan status 'dipesan' yang bisa dihapus
    // karena belum mempengaruhi stok utama.
    $sql = "DELETE FROM pembelian_stok WHERE id_pembelian = '$id_pembelian' AND status = 'dipesan'";
    $koneksi->query($sql);

    if ($koneksi->affected_rows > 0) {
        set_sweetalert('success', 'Berhasil!', 'Catatan pembelian telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus. Pembelian yang sudah diterima tidak dapat dihapus.');
    }
    header("Location: index.php?page=stok_pembelian");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
$form_title = "Tambah Pembelian Baru";
$data_edit = null;

// Ambil daftar pemasok untuk dropdown
$pemasok_list = $koneksi->query("SELECT id_pemasok, nama_pemasok FROM pemasok ORDER BY nama_pemasok ASC");

// Ambil semua data pembelian untuk ditampilkan di tabel
$sql_list = "SELECT ps.*, p.nama_pemasok 
             FROM pembelian_stok ps
             LEFT JOIN pemasok p ON ps.id_pemasok = p.id_pemasok 
             ORDER BY ps.tanggal_pembelian DESC, ps.id_pembelian DESC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Pembelian / Restok</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Pembelian Stok</li>
        </ol>
    </div>
</div>

<?php if ($action === 'tambah'): ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $form_title; ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=stok_pembelian&action=tambah" method="POST">
                    <div class="mb-3">
                        <label for="id_pemasok" class="form-label">Pilih Pemasok</label>
                        <select class="form-select" name="id_pemasok" required>
                            <option value="">-- Pilih --</option>
                            <?php while($pemasok = $pemasok_list->fetch_assoc()): ?>
                                <option value="<?= $pemasok['id_pemasok']; ?>"><?= sanitize($pemasok['nama_pemasok']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian</label>
                        <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nomor_referensi" class="form-label">Nomor Referensi/Nota</label>
                        <input type="text" class="form-control" id="nomor_referensi" name="nomor_referensi" placeholder="Opsional">
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" id="catatan" rows="3"></textarea>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=stok_pembelian" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Lanjutkan & Tambah Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: // Tampilan Daftar Pembelian (default) ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title float-start">Daftar Pembelian Stok</h5>
        <a href="index.php?page=stok_pembelian&action=tambah" class="btn btn-primary float-end"><i class="bx bx-plus"></i> Buat Pembelian Baru</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Referensi</th>
                        <th>Pemasok</th>
                        <th>Total Biaya</th>
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
                        <td><?= tgl_indo($row['tanggal_pembelian']); ?></td>
                        <td><?= sanitize($row['nomor_referensi'] ?? '-'); ?></td>
                        <td><?= sanitize($row['nama_pemasok'] ?? 'Tanpa Pemasok'); ?></td>
                        <td><?= format_rupiah($row['total_biaya']); ?></td>
                        <td>
                            <?php 
                                $status = $row['status'];
                                $badge_class = 'bg-secondary-subtle text-secondary';
                                if ($status == 'diterima') $badge_class = 'bg-success-subtle text-success';
                                if ($status == 'dipesan') $badge_class = 'bg-warning-subtle text-warning';
                                if ($status == 'dibatalkan') $badge_class = 'bg-danger-subtle text-danger';
                            ?>
                            <span class="badge <?= $badge_class; ?>"><?= ucfirst($status); ?></span>
                        </td>
                        <td>
                            <a href="index.php?page=stok_pembelian_detail&id=<?= $row['id_pembelian']; ?>" class="btn btn-sm btn-info">Detail</a>
                            <?php if($row['status'] === 'dipesan'): ?>
                                <a href="index.php?page=stok_pembelian&action=hapus&id=<?= $row['id_pembelian']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
                            <?php endif; ?>
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
                text: "Catatan pembelian ini akan dihapus!",
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