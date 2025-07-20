<?php
// admin/pages/laporan_pendapatan.php

// Tentukan tanggal default (bulan ini)
$tgl_mulai_default = date('Y-m-01');
$tgl_akhir_default = date('Y-m-t');

// Ambil tanggal dari filter, atau gunakan default
$tgl_mulai = $_GET['tgl_mulai'] ?? $tgl_mulai_default;
$tgl_akhir = $_GET['tgl_akhir'] ?? $tgl_akhir_default;

// Pastikan format tanggal valid untuk query
$tgl_mulai_sql = $koneksi->real_escape_string($tgl_mulai);
$tgl_akhir_sql = $koneksi->real_escape_string($tgl_akhir);

// Query untuk mengambil data pesanan yang sudah selesai dalam rentang tanggal
$sql_laporan = "SELECT 
                    p.nomor_pesanan,
                    p.tanggal_pesanan,
                    p.total_harga_produk,
                    p.nilai_diskon,
                    p.biaya_kirim,
                    p.total_final,
                    peng.nama_lengkap AS nama_pelanggan,
                    (p.total_harga_produk + p.biaya_kirim) AS pendapatan_kotor
                FROM 
                    pesanan p
                LEFT JOIN 
                    pengguna peng ON p.id_pengguna = peng.id_pengguna
                WHERE 
                    p.status_pesanan = 'selesai'
                    AND DATE(p.tanggal_pesanan) BETWEEN '$tgl_mulai_sql' AND '$tgl_akhir_sql'
                ORDER BY 
                    p.tanggal_pesanan ASC";

$result_laporan = $koneksi->query($sql_laporan);
$laporan_data = [];
$total_kotor = 0;
$total_diskon = 0;
$total_bersih = 0;

if ($result_laporan) {
    while($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
        $total_kotor += $row['pendapatan_kotor'];
        $total_diskon += $row['nilai_diskon'];
        $total_bersih += $row['total_final'];
    }
}

// --- BARU: Query untuk mengambil total biaya pembelian stok (restok) ---
$sql_restok = "SELECT SUM(total_biaya) AS total_pembelian_stok 
               FROM pembelian_stok 
               WHERE status = 'diterima' 
               AND tanggal_pembelian BETWEEN '$tgl_mulai_sql' AND '$tgl_akhir_sql'";
$result_restok = $koneksi->query($sql_restok);
$total_pembelian_stok = $result_restok->fetch_assoc()['total_pembelian_stok'] ?? 0;

// --- BARU: Hitung Perkiraan Laba ---
$perkiraan_laba = $total_bersih - $total_pembelian_stok;
?>

<div class="page-header">
    <div class="page-title">
        <h4>Laporan Pendapatan & Laba</h4>
        <h6>Menampilkan ringkasan finansial dari transaksi penjualan dan pembelian stok.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-top">
            <form method="GET" action="?page=laporan_pendapatan">
                <input type="hidden" name="page" value="laporan_pendapatan">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= sanitize($tgl_mulai); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= sanitize($tgl_akhir); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                    <div class="col-md-3 text-end">
                         <a href="pages/laporan_pendapatan_cetak.php?tgl_mulai=<?= urlencode($tgl_mulai); ?>&tgl_akhir=<?= urlencode($tgl_akhir); ?>" target="_blank" class="btn btn-danger">
                            <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row mt-4">
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-widget">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters"><?= rupiah($total_bersih) ?></span></h5>
                        <h6>Total Pendapatan </h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-widget dash-widget-red">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters"><?= rupiah($total_pembelian_stok) ?></span></h5>
                        <h6>Total Biaya Pembelian Stok</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-widget dash-widget-green">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters"><?= rupiah($perkiraan_laba) ?></span></h5>
                        <h6>Perkiraan Laba Bersih</h6>
                    </div>
                </div>
            </div>
             <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-widget dash-widget-info">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters"><?= rupiah($total_kotor) ?></span></h5>
                        <h6>Total Pendapatan Kotor</h6>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4">Rincian Transaksi Penjualan</h5>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Tgl Pesanan</th>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Total Transaksi</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= tgl_indo(date('Y-m-d', strtotime($data['tanggal_pesanan'])), true); ?></td>
                            <td><?= sanitize($data['nomor_pesanan']); ?></td>
                            <td><?= sanitize($data['nama_pelanggan'] ?? 'Pelanggan Dihapus'); ?></td>
                            <td class="text-end"><?= rupiah($data['pendapatan_kotor']); ?></td>
                            <td class="text-end"><?= rupiah($data['nilai_diskon']); ?></td>
                            <td class="text-end fw-bold"><?= rupiah($data['total_final']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data penjualan pada periode yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end fw-bold">TOTAL PENJUALAN</th>
                        <th class="text-end fw-bold"><?= rupiah($total_kotor); ?></th>
                        <th class="text-end fw-bold"><?= rupiah($total_diskon); ?></th>
                        <th class="text-end fw-bold"><?= rupiah($total_bersih); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>