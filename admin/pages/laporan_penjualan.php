<?php
// Ninda/admin/pages/laporan_penjualan.php

// Logika untuk filter tanggal
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');

// Query untuk mengambil data laporan penjualan
$sql_laporan = "SELECT 
                    p.id_pesanan,
                    p.nomor_pesanan, 
                    p.tanggal_pesanan, 
                    pg.nama_lengkap, 
                    p.total_final,
                    (SELECT SUM(jumlah) FROM pesanan_detail pd WHERE pd.id_pesanan = p.id_pesanan) as total_item
                FROM pesanan p
                JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
                WHERE p.status_pesanan = 'selesai' 
                  AND DATE(p.tanggal_pesanan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                ORDER BY p.tanggal_pesanan DESC";

$result_laporan = $koneksi->query($sql_laporan);

// Hitung summary untuk kartu informasi
$total_penjualan = 0;
$total_item_terjual = 0;
$total_pesanan = 0;
$laporan_data = [];

if ($result_laporan && $result_laporan->num_rows > 0) {
    $total_pesanan = $result_laporan->num_rows;
    while($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
        $total_penjualan += $row['total_final'];
        $total_item_terjual += $row['total_item'];
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h4>Laporan Penjualan</h4>
        <h6>Melihat laporan penjualan berdasarkan rentang waktu.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_penjualan">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start" class="form-control" value="<?= $tanggal_mulai; ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end" class="form-control" value="<?= $tanggal_akhir; ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter Laporan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-sm-6 col-md-4"><div class="card bg-light"><div class="card-body text-center"><h5 class="card-title">Total Penjualan</h5><p class="card-text fs-4 fw-bold text-primary"><?= format_rupiah($total_penjualan); ?></p></div></div></div>
            <div class="col-sm-6 col-md-4"><div class="card bg-light"><div class="card-body text-center"><h5 class="card-title">Total Pesanan Selesai</h5><p class="card-text fs-4 fw-bold text-success"><?= $total_pesanan; ?></p></div></div></div>
            <div class="col-sm-6 col-md-4"><div class="card bg-light"><div class="card-body text-center"><h5 class="card-title">Total Item Terjual</h5><p class="card-text fs-4 fw-bold text-info"><?= $total_item_terjual; ?></p></div></div></div>
        </div>

        <div class="table-responsive">

            <a href="pages/laporan_penjualan_cetak.php?start=<?= $tanggal_mulai; ?>&end=<?= $tanggal_akhir; ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a> 
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-end">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= tgl_indo($data['tanggal_pesanan']); ?></td>
                            <td><a href="index.php?page=pesanan_detail&id=<?= $data['id_pesanan']; ?>"><?= sanitize($data['nomor_pesanan']); ?></a></td>
                            <td><?= sanitize($data['nama_lengkap']); ?></td>
                            <td class="text-center"><?= $data['total_item']; ?></td>
                            <td class="text-end fw-bold"><?= format_rupiah($data['total_final']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data penjualan pada rentang tanggal yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>