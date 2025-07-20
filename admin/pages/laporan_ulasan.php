<?php
// Ninda/admin/pages/laporan_ulasan.php

// Logika untuk filter
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');
$rating_filter = (int)($_GET['rating'] ?? 0);

$where_clause = "WHERE DATE(u.tanggal_ulasan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";
if ($rating_filter > 0) {
    $where_clause .= " AND u.rating = $rating_filter";
}

// Query untuk mengambil data ulasan
$sql_laporan = "SELECT 
                    u.rating, 
                    u.komentar, 
                    u.tanggal_ulasan,
                    p.nama_produk,
                    pg.nama_lengkap as nama_pelanggan
                FROM ulasan u
                JOIN produk p ON u.id_produk = p.id_produk
                JOIN pengguna pg ON u.id_pengguna = pg.id_pengguna
                $where_clause
                ORDER BY u.tanggal_ulasan DESC";

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
        <h4>Laporan Ulasan & Kepuasan Pelanggan</h4>
        <h6>Melihat semua ulasan dari pelanggan.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_ulasan">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start" class="form-control" value="<?= $tanggal_mulai; ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end" class="form-control" value="<?= $tanggal_akhir; ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Filter Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="0" <?= ($rating_filter == 0) ? 'selected' : ''; ?>>Semua Rating</option>
                                    <option value="5" <?= ($rating_filter == 5) ? 'selected' : ''; ?>>★★★★★ (5)</option>
                                    <option value="4" <?= ($rating_filter == 4) ? 'selected' : ''; ?>>★★★★☆ (4)</option>
                                    <option value="3" <?= ($rating_filter == 3) ? 'selected' : ''; ?>>★★★☆☆ (3)</option>
                                    <option value="2" <?= ($rating_filter == 2) ? 'selected' : ''; ?>>★★☆☆☆ (2)</option>
                                    <option value="1" <?= ($rating_filter == 1) ? 'selected' : ''; ?>>★☆☆☆☆ (1)</option>
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
            <a href="pages/laporan_ulasan_cetak.php?start=<?= $tanggal_mulai; ?>&end=<?= $tanggal_akhir; ?>&rating=<?= $rating_filter; ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Produk</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Rating</th>
                        <th>Komentar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= tgl_indo($data['tanggal_ulasan']); ?></td>
                            <td><?= sanitize($data['nama_produk']); ?></td>
                            <td><?= sanitize($data['nama_pelanggan']); ?></td>
                            <td class="text-center">
                                <?php for($i=0; $i<$data['rating']; $i++): ?>★<?php endfor; ?>
                            </td>
                            <td><?= sanitize($data['komentar'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada ulasan yang cocok dengan kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>