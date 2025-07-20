<?php
require_once '../inc/koneksi.php';
require_once '../inc/helper.php';
require_once '../mpdf60/mpdf.php'; 

session_start();
if (!isset($_SESSION['pengguna_id']) || $_SESSION['pengguna_peran'] !== 'admin') {
    die("Akses ditolak.");
}

$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');

$sql_laporan = "SELECT p.nomor_pesanan, p.tanggal_pesanan, p.status_pesanan, p.alamat_kirim, k.nama_kurir
                FROM pesanan p
                JOIN kurir_internal k ON p.id_kurir = k.id_kurir
                WHERE p.status_pesanan IN ('sedang_diantar', 'selesai')
                  AND DATE(p.tanggal_pesanan) BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                ORDER BY p.tanggal_pesanan ASC";
$result_laporan = $koneksi->query($sql_laporan);

$laporan_data = [];
if ($result_laporan && $result_laporan->num_rows > 0) {
    while($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
    }
}
$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengiriman</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11pt; }
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat .logo { width: 100px; text-align: center; vertical-align: middle; }
        .kop-surat .text { text-align: center; vertical-align: middle; }
        .kop-surat h1 { font-size: 18pt; margin: 0; }
        .kop-surat p { font-size: 10pt; margin: 0; }
        .judul-laporan { text-align: center; margin-bottom: 20px; }
        .judul-laporan h2 { text-decoration: underline; margin-bottom: 5px; font-size: 14pt;}
        .report-info { font-size: 10pt; margin-bottom: 15px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 8px; }
        .data-table th { background-color: #f2f2f2; text-align: center; }
        .text-end { text-align: right; }
        tfoot th { font-size: 11pt; }
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
        <h2>LAPORAN PENGIRIMAN</h2>
        <span>Periode: <?= tgl_indo($tanggal_mulai); ?> s/d <?= tgl_indo($tanggal_akhir); ?></span>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>No. Pesanan</th>
                <th>Kurir</th>
                <th>Alamat Pengiriman</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): $no = 1; ?>
                <?php foreach($laporan_data as $data): ?>
                <tr>
                    <td style="text-align:center;"><?= $no++; ?></td>
                    <td><?= tgl_indo($data['tanggal_pesanan']); ?></td>
                    <td><?= sanitize($data['nomor_pesanan']); ?></td>
                    <td><?= sanitize($data['nama_kurir']); ?></td>
                    <td><?= sanitize($data['alamat_kirim']); ?></td>
                    <td style="text-align:center;"><?= ucfirst(str_replace('_', ' ', $data['status_pesanan'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Tidak ada data pengiriman untuk periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
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

$mpdf = new mPDF('c', 'A4-L'); // 'L' untuk Landscape
$mpdf->WriteHTML($html);
$mpdf->Output('laporan-pengiriman.pdf', 'I');
exit;
?>