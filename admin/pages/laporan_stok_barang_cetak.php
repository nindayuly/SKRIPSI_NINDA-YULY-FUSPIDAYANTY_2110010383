<?php
require_once '../inc/koneksi.php';
require_once '../inc/helper.php';
require_once '../mpdf60/mpdf.php';

session_start();
if (!isset($_SESSION['pengguna_id']) || $_SESSION['pengguna_peran'] !== 'admin') {
    die("Akses ditolak.");
}

// Mengambil parameter filter
$kategori_filter = (int)($_GET['kategori'] ?? 0);
$nama_kategori_filter = "Semua Kategori";

$where_clause = "";
if ($kategori_filter > 0) {
    $where_clause = "WHERE p.id_kategori = $kategori_filter";
    // Ambil nama kategori untuk ditampilkan di judul laporan
    $q_kat = $koneksi->query("SELECT nama_kategori FROM kategori_produk WHERE id_kategori = $kategori_filter");
    if($q_kat->num_rows > 0) {
        $nama_kategori_filter = "Kategori: " . $q_kat->fetch_assoc()['nama_kategori'];
    }
}

// Query untuk data detail & ringkasan
$sql_laporan = "SELECT p.kode_produk, p.nama_produk, kp.nama_kategori, p.stok, p.harga_jual, (p.stok * p.harga_jual) AS nilai_stok
                FROM produk p
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                $where_clause ORDER BY p.nama_produk ASC";

$sql_summary = "SELECT SUM(stok) as total_item, SUM(stok * p.harga_jual) as total_nilai
                FROM produk p $where_clause";

$result_laporan = $koneksi->query($sql_laporan);
$summary_result = $koneksi->query($sql_summary);
$summary_data = $summary_result->fetch_assoc();
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
    <title>Laporan Stok Barang</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10pt; }
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat .logo { width: 100px; text-align: center; vertical-align: middle; }
        .kop-surat .text { text-align: center; vertical-align: middle; }
        .kop-surat h1 { font-size: 18pt; margin: 0; }
        .kop-surat p { font-size: 10pt; margin: 0; }
        .judul-laporan { text-align: center; margin-bottom: 20px; }
        .judul-laporan h2 { text-decoration: underline; margin-bottom: 5px; font-size: 14pt;}
        .summary-table { width: 60%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #333; }
        .summary-table td { padding: 8px; border: 1px solid #333; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 8px; }
        .data-table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
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
        <h2>LAPORAN STOK BARANG</h2>
        <span><?= sanitize($nama_kategori_filter); ?> per Tanggal <?= tgl_indo(date('Y-m-d')); ?></span>
    </div>
    
    <h3>Ringkasan Inventaris</h3>
    <table class="summary-table">
        <tr>
            <td>Total Jenis Produk</td>
            <td class="text-right"><?= number_format($result_laporan->num_rows); ?></td>
        </tr>
        <tr>
            <td>Total Item dalam Stok</td>
            <td class="text-right"><?= number_format($summary_data['total_item'] ?? 0); ?> Unit</td>
        </tr>
         <tr>
            <td><b>Total Nilai Inventaris (Harga Jual)</b></td>
            <td class="text-right"><b>Rp <?= number_format($summary_data['total_nilai'] ?? 0); ?></b></td>
        </tr>
    </table>

    <h3>Detail Stok Barang</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode Produk</th>
                <th width="30%">Nama Produk</th>
                <th class="text-center">Stok</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Nilai Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($laporan_data)): $no = 1; ?>
                <?php foreach($laporan_data as $data): ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= sanitize($data['kode_produk']); ?></td>
                    <td><?= sanitize($data['nama_produk']); ?></td>
                    <td class="text-center"><?= sanitize($data['stok']); ?></td>
                    <td class="text-right">Rp <?= number_format($data['harga_jual']); ?></td>
                    <td class="text-right">Rp <?= number_format($data['nilai_stok']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Tidak ada data stok yang cocok dengan kriteria.</td></tr>
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

$mpdf = new mPDF('c', 'A4-L'); // Landscape untuk tabel yang lebih lebar
$mpdf->WriteHTML($html);
$mpdf->Output('laporan-stok-barang.pdf', 'I');
exit;
?>