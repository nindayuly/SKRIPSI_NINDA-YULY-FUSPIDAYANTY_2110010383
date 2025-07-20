<?php
// Ninda/admin/pages/laporan_pengiriman.php

// Logika untuk filter tanggal
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');

// Query untuk mengambil data pengiriman
$sql_laporan = "SELECT 
                    p.nomor_pesanan, 
                    p.tanggal_pesanan,
                    p.status_pesanan,
                    p.alamat_kirim,
                    k.nama_kurir
                FROM pesanan p
                JOIN kurir_internal k ON p.id_kurir = k.id_kurir
                WHERE p.status_pesanan IN ('sedang_diantar', 'selesai')
                  AND DATE(p.tanggal_pesanan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                ORDER BY p.tanggal_pesanan DESC";

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
        <h4>Laporan Pengiriman</h4>
        <h6>Melihat riwayat pengiriman oleh kurir.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-top"> 
            <div class="wordset">
                 <div class="d-flex gap-2">
                </div>
            </div>
        </div>

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0 mb-3">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_pengiriman">
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

        <div class="table-responsive mt-4">
            <a href="pages/laporan_pengiriman_cetak.php?start=<?= $tanggal_mulai; ?>&end=<?= $tanggal_akhir; ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Pesan</th>
                        <th>No. Pesanan</th>
                        <th>Kurir</th>
                        <th>Alamat Pengiriman</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= tgl_indo($data['tanggal_pesanan']); ?></td>
                            <td><?= sanitize($data['nomor_pesanan']); ?></td>
                            <td><?= sanitize($data['nama_kurir']); ?></td>
                            <td><?= sanitize($data['alamat_kirim']); ?></td>
                            <td class="text-center">
                                <?php 
                                    $status = $data['status_pesanan'];
                                    $badge_class = ($status == 'selesai') ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary';
                                ?>
                                <span class="badge <?= $badge_class; ?>"><?= ucfirst(str_replace('_', ' ', $status)); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data pengiriman pada rentang tanggal yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table> 
        </div>
    </div>
</div>