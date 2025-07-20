<?php
// Ninda/admin/pages/laporan_promosi.php

// Logika untuk filter
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');
$status_filter = $_GET['status'] ?? 'all'; // 'all', '1' (Aktif), '0' (Tidak Aktif)

// Membangun klausa WHERE berdasarkan filter
$where_clause = "WHERE (pr.tgl_mulai <= '$tanggal_akhir' AND pr.tgl_berakhir >= '$tanggal_mulai')";
if ($status_filter !== 'all') {
    $where_clause .= " AND pr.status_aktif = " . (int)$status_filter;
}

// Query untuk mengambil data promosi
$sql_laporan = "SELECT 
                    pr.kode_promo,
                    pr.deskripsi,
                    pr.tipe_diskon,
                    pr.nilai_diskon,
                    pr.tgl_mulai,
                    pr.tgl_berakhir,
                    pr.status_aktif
                FROM promosi pr
                $where_clause
                ORDER BY pr.tgl_mulai DESC";

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
        <h4>Laporan Promosi</h4>
        <h6>Melihat semua data promosi yang tersedia.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_promosi">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start" class="form-control" value="<?= sanitize($tanggal_mulai); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end" class="form-control" value="<?= sanitize($tanggal_akhir); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Filter Status</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?= ($status_filter == 'all') ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="1" <?= ($status_filter == '1') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?= ($status_filter == '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
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
            <a href="pages/laporan_promosi_cetak.php?start=<?= sanitize($tanggal_mulai); ?>&end=<?= sanitize($tanggal_akhir); ?>&status=<?= sanitize($status_filter); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Promo</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Periode Berlaku</th>
                        <th class="text-center">Diskon</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= sanitize($data['kode_promo']); ?></strong></td>
                            <td><?= sanitize($data['deskripsi']); ?></td>
                            <td class="text-center"><?= tgl_indo($data['tgl_mulai']); ?> - <?= tgl_indo($data['tgl_berakhir']); ?></td>
                            <td class="text-center">
                                <?php 
                                if ($data['tipe_diskon'] == 'percentage') {
                                    echo sanitize($data['nilai_diskon']) . '%';
                                } else {
                                    echo 'Rp ' . number_format(sanitize($data['nilai_diskon']), 0, ',', '.');
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <?php if ($data['status_aktif'] == 1): ?>
                                    <span class="badges bg-lightgreen">Aktif</span>
                                <?php else: ?>
                                    <span class="badges bg-lightred">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data promosi yang cocok dengan kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>