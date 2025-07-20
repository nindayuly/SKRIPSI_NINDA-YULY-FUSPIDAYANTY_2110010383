<?php
// Ninda/admin/pages/laporan_terlaris.php

// Logika untuk filter tanggal
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');
$limit = (int)($_GET['limit'] ?? 10); // Opsi untuk membatasi jumlah produk, default 10

// Klausa WHERE untuk memfilter pesanan yang sudah selesai dalam rentang tanggal
$where_clause = "WHERE ps.status_pesanan = 'selesai' AND DATE(ps.tanggal_pesanan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";

// Query utama untuk mengambil produk terlaris
$sql_laporan = "SELECT 
                    p.kode_produk,
                    p.nama_produk,
                    kp.nama_kategori,
                    SUM(pd.jumlah) as total_terjual,
                    p.stok
                FROM pesanan_detail pd
                JOIN produk p ON pd.id_produk = p.id_produk
                JOIN pesanan ps ON pd.id_pesanan = ps.id_pesanan
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                $where_clause
                GROUP BY pd.id_produk
                ORDER BY total_terjual DESC
                LIMIT $limit";

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
        <h4>Laporan Produk Terlaris</h4>
        <h6>Melihat produk yang paling banyak terjual.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 

        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_terlaris">
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
                                <label>Tampilkan Top</label>
                                <select name="limit" class="form-select">
                                    <option value="10" <?= ($limit == 10) ? 'selected' : ''; ?>>10 Produk</option>
                                    <option value="20" <?= ($limit == 20) ? 'selected' : ''; ?>>20 Produk</option>
                                    <option value="50" <?= ($limit == 50) ? 'selected' : ''; ?>>50 Produk</option>
                                    <option value="100" <?= ($limit == 100) ? 'selected' : ''; ?>>100 Produk</option>
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
            <a href="pages/laporan_terlaris_cetak.php?start=<?= sanitize($tanggal_mulai); ?>&end=<?= sanitize($tanggal_akhir); ?>&limit=<?= sanitize($limit); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Peringkat</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Total Terjual</th>
                        <th class="text-center">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td class="text-center"><strong><?= $no++; ?></strong></td>
                            <td><?= sanitize($data['kode_produk']); ?></td>
                            <td><?= sanitize($data['nama_produk']); ?></td>
                            <td><?= sanitize($data['nama_kategori'] ?? 'Tidak ada kategori'); ?></td>
                            <td class="text-center"><?= sanitize($data['total_terjual']); ?> Unit</td>
                            <td class="text-center"><?= sanitize($data['stok']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data penjualan untuk periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>