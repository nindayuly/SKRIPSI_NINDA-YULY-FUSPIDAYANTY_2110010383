<?php
// Ninda/admin/pages/laporan_restok.php

// Ambil semua pemasok untuk filter dropdown
$pemasok_list = $koneksi->query("SELECT id_pemasok, nama_pemasok FROM pemasok ORDER BY nama_pemasok ASC");

// Logika untuk filter
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');
$status_filter = $_GET['status'] ?? 'semua';
$pemasok_filter = (int)($_GET['pemasok'] ?? 0);

// Membangun klausa WHERE dinamis
$where_conditions = [];
$where_conditions[] = "DATE(ps.tanggal_pembelian) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";

if ($status_filter !== 'semua') {
    $where_conditions[] = "ps.status = '" . $koneksi->real_escape_string($status_filter) . "'";
}

if ($pemasok_filter > 0) {
    $where_conditions[] = "ps.id_pemasok = '$pemasok_filter'";
}

$where_clause = "WHERE " . implode(' AND ', $where_conditions);

// Query untuk mengambil data histori pembelian
$sql_laporan = "SELECT 
                    ps.id_pembelian,
                    ps.tanggal_pembelian,
                    ps.nomor_referensi,
                    p.nama_pemasok,
                    ps.total_biaya,
                    ps.status
                FROM pembelian_stok ps
                LEFT JOIN pemasok p ON ps.id_pemasok = p.id_pemasok
                $where_clause
                ORDER BY ps.tanggal_pembelian DESC";

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
        <h4>Laporan Histori Restok</h4>
        <h6>Melihat semua riwayat pembelian stok dari pemasok.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body"> 
        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_restok">
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
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="semua" <?= ($status_filter == 'semua') ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="dipesan" <?= ($status_filter == 'dipesan') ? 'selected' : ''; ?>>Dipesan</option>
                                    <option value="diterima" <?= ($status_filter == 'diterima') ? 'selected' : ''; ?>>Diterima</option>
                                    <option value="dibatalkan" <?= ($status_filter == 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Pemasok</label>
                                <select name="pemasok" class="form-select">
                                    <option value="0" <?= ($pemasok_filter == 0) ? 'selected' : ''; ?>>Semua Pemasok</option>
                                    <?php while($pemasok = $pemasok_list->fetch_assoc()): ?>
                                        <option value="<?= $pemasok['id_pemasok']; ?>" <?= ($pemasok_filter == $pemasok['id_pemasok']) ? 'selected' : ''; ?>>
                                            <?= sanitize($pemasok['nama_pemasok']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <a href="pages/laporan_restok_cetak.php?start=<?= sanitize($tanggal_mulai); ?>&end=<?= sanitize($tanggal_akhir); ?>&status=<?= sanitize($status_filter); ?>&pemasok=<?= sanitize($pemasok_filter); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No. Referensi</th>
                        <th>Pemasok</th>
                        <th class="text-end">Total Biaya</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= tgl_indo($data['tanggal_pembelian']); ?></td>
                            <td>
                                <a href="index.php?page=stok_pembelian_detail&id=<?= $data['id_pembelian']; ?>">
                                    <?= sanitize($data['nomor_referensi'] ?? 'N/A'); ?>
                                </a>
                            </td>
                            <td><?= sanitize($data['nama_pemasok'] ?? 'N/A'); ?></td>
                            <td class="text-end"><?= format_rupiah($data['total_biaya']); ?></td>
                            <td class="text-center">
                                <?php 
                                    $status = $data['status'];
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'diterima') $badge_class = 'bg-success';
                                    if ($status == 'dipesan') $badge_class = 'bg-warning';
                                    if ($status == 'dibatalkan') $badge_class = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge_class; ?>"><?= ucfirst($status); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data pembelian yang cocok dengan kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>