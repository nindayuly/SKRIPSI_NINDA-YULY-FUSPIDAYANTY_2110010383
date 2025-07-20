<?php
// Ninda/admin/pages/laporan_produk_tidak_laku.php

// Filter tanggal
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');

// Subquery untuk mendapatkan semua ID produk yang TERJUAL pada periode yang dipilih
$subquery_produk_terjual = "SELECT DISTINCT pd.id_produk 
                            FROM pesanan_detail pd
                            JOIN pesanan ps ON pd.id_pesanan = ps.id_pesanan
                            WHERE ps.status_pesanan = 'selesai' 
                            AND DATE(ps.tanggal_pesanan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";

// Query utama untuk mendapatkan produk yang ID-nya TIDAK ADA di dalam subquery di atas
$sql_laporan = "SELECT 
                    p.kode_produk,
                    p.nama_produk,
                    kp.nama_kategori,
                    p.stok
                FROM produk p
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                WHERE p.id_produk NOT IN ($subquery_produk_terjual)
                ORDER BY p.nama_produk ASC";

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
        <h4>Laporan Produk Tidak Laku</h4>
        <h6>Melihat produk yang tidak ada penjualan pada periode tertentu.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 
        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_produk_tidak_laku">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Periode Dari Tanggal</label>
                                <input type="date" name="start" class="form-control" value="<?= sanitize($tanggal_mulai); ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end" class="form-control" value="<?= sanitize($tanggal_akhir); ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter Laporan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <a href="pages/laporan_produk_tidak_laku_cetak.php?start=<?= sanitize($tanggal_mulai); ?>&end=<?= sanitize($tanggal_akhir); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
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
                            <td class="text-center"><?= sanitize($data['stok']); ?> Unit</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Semua produk memiliki penjualan pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>