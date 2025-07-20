<?php
// Ninda/admin/pages/laporan_stok_habis.php

// Filter untuk ambang batas stok
$stok_threshold = (isset($_GET['stok']) && is_numeric($_GET['stok'])) ? (int)$_GET['stok'] : 0;

// Query untuk mengambil produk yang stoknya menipis atau habis
$sql_laporan = "SELECT 
                    p.kode_produk,
                    p.nama_produk,
                    kp.nama_kategori,
                    p.merk,
                    p.stok
                FROM produk p
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                WHERE p.stok <= $stok_threshold
                ORDER BY p.stok ASC, p.nama_produk ASC";

$result_laporan = $koneksi->query($sql_laporan);
$laporan_data = [];
if ($result_laporan) {
    while($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h4>Laporan Stok Habis & Menipis</h4>
        <h6>Melihat produk yang perlu di-stok ulang.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_stok_habis">
                    <div class="row align-items-end">
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Tampilkan Produk dengan Stok ≤</label>
                                <input type="number" name="stok" class="form-control" value="<?= sanitize($stok_threshold); ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter Laporan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <a href="pages/laporan_stok_habis_cetak.php?stok=<?= sanitize($stok_threshold); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Merk</th>
                        <th class="text-center">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= sanitize($data['kode_produk']); ?></td>
                            <td><?= sanitize($data['nama_produk']); ?></td>
                            <td><?= sanitize($data['nama_kategori'] ?? 'Tidak ada kategori'); ?></td>
                            <td><?= sanitize($data['merk'] ?? '-'); ?></td>
                            <td class="text-center">
                                <span class="text-danger fw-bold"><?= sanitize($data['stok']); ?> Unit</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada produk yang stoknya menipis sesuai kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>