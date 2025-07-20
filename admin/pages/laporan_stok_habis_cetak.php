<?php
require_once '../inc/koneksi.php';
require_once '../inc/helper.php';
require_once '../mpdf60/mpdf.php';

session_start();
if (!isset($_SESSION['pengguna_id']) || $_SESSION['pengguna_peran'] !== 'admin') {
    die("Akses ditolak.");
}

// Mengambil parameter filter
$stok_threshold = (isset($_GET['stok']) && is_numeric($_GET['stok'])) ? (int)$_GET['stok'] : 0;

// Query yang sama dengan halaman laporan
$sql_laporan = "SELECT 
                    p.kode_produk, p.nama_produk, kp.nama_kategori, p.merk, p.stok
                FROM produk p
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                WHERE p.stok <= $stok_threshold
                ORDER BY p.stok ASC, p.nama_produk ASC";

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
    <title>Laporan Stok Habis & Menipis</title>
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
        <h2>LAPORAN STOK HABIS & MENIPIS</h2>
        <span>Daftar Produk dengan Stok Kurang Dari atau Sama Dengan <?= $stok_threshold; ?></span>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode Produk</th>
                <th width="40%">Nama Produk</th>
                <th>Kategori</th>
                <th width="12%">Sisa Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): $no = 1; ?>
                <?php foreach($laporan_data as $data): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= sanitize($data['kode_produk']); ?></td>
                    <td><?= sanitize($data['nama_produk']); ?></td>
                    <td><?= sanitize($data['nama_kategori'] ?? 'N/A'); ?></td>
                    <td class="text-center"><?= sanitize($data['stok']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Tidak ada produk yang stoknya menipis sesuai kriteria.</td></tr>
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

$mpdf = new mPDF('c', 'A4-P');
$mpdf->WriteHTML($html);
$mpdf->Output('laporan-stok-habis.pdf', 'I');
exit;
?>