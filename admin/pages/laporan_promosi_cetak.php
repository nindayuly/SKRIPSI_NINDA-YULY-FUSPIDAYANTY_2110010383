<?php
require_once '../inc/koneksi.php';
require_once '../inc/helper.php';
require_once '../mpdf60/mpdf.php'; // Sesuaikan path jika perlu

session_start();
if (!isset($_SESSION['pengguna_id']) || $_SESSION['pengguna_peran'] !== 'admin') {
    die("Akses ditolak.");
}

// Mengambil parameter filter dari URL
$tanggal_mulai = $_GET['start'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end'] ?? date('Y-m-t');
$status_filter = $_GET['status'] ?? 'all';

// Logika filter yang sama persis dengan halaman laporan
$where_clause = "WHERE (pr.tgl_mulai <= '$tanggal_akhir' AND pr.tgl_berakhir >= '$tanggal_mulai')";
if ($status_filter !== 'all') {
    $where_clause .= " AND pr.status_aktif = " . (int)$status_filter;
}

$sql_laporan = "SELECT 
                    pr.kode_promo, pr.deskripsi, pr.tipe_diskon, pr.nilai_diskon, 
                    pr.tgl_mulai, pr.tgl_berakhir, pr.status_aktif
                FROM promosi pr
                $where_clause
                ORDER BY pr.tgl_mulai ASC"; // Urutkan ASC untuk cetak kronologis

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
    <title>Laporan Promosi</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10pt; }
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat .logo { width: 100px; text-align: center; vertical-align: middle; }
        .kop-surat .text { text-align: center; vertical-align: middle; }
        .kop-surat h1 { font-size: 18pt; margin: 0; }
        .kop-surat p { font-size: 10pt; margin: 0; }
        .judul-laporan { text-align: center; margin-bottom: 20px; }
        .judul-laporan h2 { text-decoration: underline; margin-bottom: 5px; font-size: 14pt;}
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 8px; }
        .data-table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .ttd-area { margin-top: 50px; width: 100%; }
        .ttd-kolom { width: 280px; float: right; text-align: center; }
        .ttd-kolom .nama { font-weight: bold; text-decoration: underline; margin-top: 70px; }
    </style>
</head>
<body>
    <table class="kop-surat">
        <tr>
            <td class="logo"><img src="../assets/images/<?= sanitize($meta['logo']); ?>" alt="logo" style="width: 85px; height: auto;"></td>
            <td class="text">
                <h1><?= strtoupper(sanitize($meta['nama_instansi'])); ?></h1>
                <p><?= sanitize($meta['alamat']); ?></p>
                <p>Telepon: <?= sanitize($meta['telepon']); ?> | Email: <?= sanitize($meta['email']); ?></p>
            </td>
        </tr>
    </table>

    <div class="judul-laporan">
        <h2>LAPORAN PROMOSI</h2>
        <span>Periode Terkait: <?= tgl_indo($tanggal_mulai); ?> s/d <?= tgl_indo($tanggal_akhir); ?></span>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode Promo</th>
                <th width="30%">Deskripsi</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Berakhir</th>
                <th>Diskon</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): $no = 1; ?>
                <?php foreach($laporan_data as $data): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= sanitize($data['kode_promo']); ?></td>
                    <td><?= sanitize($data['deskripsi']); ?></td>
                    <td class="text-center"><?= tgl_indo($data['tgl_mulai']); ?></td>
                    <td class="text-center"><?= tgl_indo($data['tgl_berakhir']); ?></td>
                    <td class="text-center">
                        <?php 
                        if ($data['tipe_diskon'] == 'percentage') {
                            echo sanitize($data['nilai_diskon']) . ' %';
                        } else {
                            echo 'Rp ' . number_format(sanitize($data['nilai_diskon']), 0, ',', '.');
                        }
                        ?>
                    </td>
                    <td class="text-center"><?= ($data['status_aktif'] == 1) ? 'Aktif' : 'Tidak Aktif'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Tidak ada data promosi untuk periode ini.</td></tr>
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

$mpdf = new mPDF('c', 'A4-L'); // Menggunakan orientasi Landscape untuk tabel yang lebih lebar
$mpdf->WriteHTML($html);
$mpdf->Output('laporan-promosi.pdf', 'I');
exit;
?>