<?php
require_once '../inc/koneksi.php';
require_once '../inc/helper.php';
require_once '../mpdf60/mpdf.php';

session_start();
if (!isset($_SESSION['pengguna_id']) || $_SESSION['pengguna_peran'] !== 'admin') {
    die("Akses ditolak.");
}

// Ambil tanggal dari parameter GET
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

$tgl_mulai_sql = $koneksi->real_escape_string($tgl_mulai);
$tgl_akhir_sql = $koneksi->real_escape_string($tgl_akhir);

// Query data laporan penjualan
$sql_laporan = "SELECT p.nomor_pesanan, p.tanggal_pesanan, p.total_harga_produk, p.nilai_diskon, p.biaya_kirim, p.total_final, (p.total_harga_produk + p.biaya_kirim) AS pendapatan_kotor
                FROM pesanan p
                WHERE p.status_pesanan = 'selesai'
                AND DATE(p.tanggal_pesanan) BETWEEN '$tgl_mulai_sql' AND '$tgl_akhir_sql'
                ORDER BY p.tanggal_pesanan ASC";
$result_laporan = $koneksi->query($sql_laporan);

$laporan_data = [];
if ($result_laporan && $result_laporan->num_rows > 0) {
    while($row = $result_laporan->fetch_assoc()) $laporan_data[] = $row;
}

// Query untuk total penjualan
$sql_total_jual = "SELECT SUM(total_harga_produk + biaya_kirim) as total_kotor, SUM(nilai_diskon) as total_diskon, SUM(total_final) as total_bersih
                   FROM pesanan
                   WHERE status_pesanan = 'selesai'
                   AND DATE(tanggal_pesanan) BETWEEN '$tgl_mulai_sql' AND '$tgl_akhir_sql'";
$totals_jual = $koneksi->query($sql_total_jual)->fetch_assoc();
$total_bersih = $totals_jual['total_bersih'] ?? 0;

// BARU: Query untuk total biaya pembelian stok
$sql_total_beli = "SELECT SUM(total_biaya) AS total_pembelian_stok
                   FROM pembelian_stok
                   WHERE status = 'diterima'
                   AND tanggal_pembelian BETWEEN '$tgl_mulai_sql' AND '$tgl_akhir_sql'";
$totals_beli = $koneksi->query($sql_total_beli)->fetch_assoc();
$total_pembelian_stok = $totals_beli['total_pembelian_stok'] ?? 0;

// BARU: Hitung perkiraan laba
$perkiraan_laba = $total_bersih - $total_pembelian_stok;

// Ambil data meta
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10pt; }
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat .logo { width: 100px; text-align: center; vertical-align: middle; }
        .kop-surat .text { text-align: center; vertical-align: middle; }
        .kop-surat h1 { font-size: 18pt; margin: 0; }
        .kop-surat p { font-size: 10pt; margin: 0; }
        .judul-laporan { text-align: center; margin-bottom: 20px; }
        .judul-laporan h2 { text-decoration: underline; margin-bottom: 5px; font-size: 14pt;}
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 8px; }
        .data-table th { background-color: #f2f2f2; text-align: center; }
        .data-table tfoot th, .data-table tfoot td { background-color: #e8e8e8; font-weight: bold; }
        .summary-table1 { width: 30%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-table { width: 70%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-table td { border: 1px solid #333; padding: 8px; }
        .summary-table .label { background-color: #f2f2f2; font-weight: bold; }
        .summary-table .total-laba { background-color: #d4edda; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .ttd-area { margin-top: 50px; width: 100%; }
        .ttd-kolom { width: 280px; float: right; text-align: center; }
        .ttd-kolom .nama { font-weight: bold; text-decoration: underline; margin-top: 70px; }
    </style>
</head>
<body>
    <table class="kop-surat">
        <tr>
            <td class="logo">
                <?php if (!empty($meta['logo'])): ?>
                    <img src="../assets/images/<?= sanitize($meta['logo']); ?>" alt="logo" style="width: 85px; height: auto;">
                <?php endif; ?>
            </td>
            <td class="text">
                <h1><?= strtoupper(sanitize($meta['nama_instansi'])); ?></h1>
                <p><?= sanitize($meta['alamat']); ?></p>
                <p>Telepon: <?= sanitize($meta['telepon']); ?> | Email: <?= sanitize($meta['email']); ?></p>
            </td>
        </tr>
    </table>

    <div class="judul-laporan">
        <h2>LAPORAN PENDAPATAN & LABA BERSIH</h2>
        <span>Periode: <?= tgl_indo($tgl_mulai); ?> s/d <?= tgl_indo($tgl_akhir); ?></span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tgl Pesanan</th>
                <th>No. Pesanan</th>
                <th class="text-right">Total Trabsaksi</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): $no = 1; ?>
                <?php foreach($laporan_data as $data): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= tgl_indo(date('Y-m-d', strtotime($data['tanggal_pesanan']))); ?></td>
                    <td><?= sanitize($data['nomor_pesanan']); ?></td>
                    <td class="text-right"><?= rupiah($data['pendapatan_kotor']); ?></td>
                    <td class="text-right"><?= rupiah($data['nilai_diskon']); ?></td>
                    <td class="text-right fw-bold"><?= rupiah($data['total_final']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Tidak ada data penjualan untuk periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">TOTAL PENJUALAN</th>
                <th class="text-right"><?= rupiah($totals_jual['total_kotor'] ?? 0); ?></th>
                <th class="text-right"><?= rupiah($totals_jual['total_diskon'] ?? 0); ?></th>
                <th class="text-right"><?= rupiah($totals_jual['total_bersih'] ?? 0); ?></th>
            </tr>
        </tfoot>
    </table>

    <br>
    
    <h3 align="rihgt">Ringkasan Finansial</h3> 
    <table align="rihgt" class="summary-table">
        <tr>
            <td class="label">Total Pendapatan Kotor</td>
            <td class="text-right"><?= rupiah($total_bersih); ?></td>
        </tr>
        <tr>
            <td class="label">Total Biaya Pembelian Stok</td>
            <td class="text-right">- <?= rupiah($total_pembelian_stok); ?></td>
        </tr>
        <tr class="total-laba">
            <td class="label">Total Pendapatan Bersih / Laba Bersih</td>
            <td class="text-right"><?= rupiah($perkiraan_laba); ?></td>
        </tr>
    </table>

    <div class="ttd-area">
        <div class="ttd-kolom">
            <p>Martapura, <?= tgl_indo(date('Y-m-d')); ?></p>
            <p>Pimpinan</p>
            <div class="nama"><?= sanitize($meta['pimpinan'] ?? 'Nama Pimpinan'); ?></div>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_contents();
ob_end_clean();

$mpdf = new mPDF('c', 'A4');
$mpdf->WriteHTML($html);
$mpdf->Output('laporan-pendapatan-laba.pdf', 'I');
exit;
?>