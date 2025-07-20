<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Pastikan library mPDF sudah ada di folder mpdf60/
require_once 'admin/mpdf60/mpdf.php'; 

// 1. Validasi Akses & Ambil ID
if (!isset($_SESSION['customer_id'])) {
    exit('Akses ditolak.');
}
$id_pesanan = (int)($_GET['id'] ?? 0);
if ($id_pesanan === 0) {
    exit('ID Pesanan tidak valid.');
}
$id_pelanggan = $_SESSION['customer_id'];

// 2. Ambil semua data yang dibutuhkan dari database (sama seperti di invoice.php)
$sql_pesanan = "SELECT p.*, py.metode_bayar, u.nama_lengkap, u.email, u.telepon FROM pesanan p JOIN pengguna u ON p.id_pengguna = u.id_pengguna LEFT JOIN pembayaran py ON p.id_pesanan = py.id_pesanan WHERE p.id_pesanan = $id_pesanan AND p.id_pengguna = $id_pelanggan";
$result_pesanan = $koneksi->query($sql_pesanan);
if ($result_pesanan->num_rows === 0) exit('Pesanan tidak ditemukan.');
$pesanan = $result_pesanan->fetch_assoc();

$sql_detail = "SELECT pd.*, pr.nama_produk, pr.merk FROM pesanan_detail pd JOIN produk pr ON pd.id_produk = pr.id_produk WHERE pd.id_pesanan = $id_pesanan";
$result_detail = $koneksi->query($sql_detail);
$detail_items = [];
while ($row = $result_detail->fetch_assoc()) $detail_items[] = $row;

$meta = $koneksi->query("SELECT * FROM meta WHERE id_meta = 1")->fetch_assoc();

// 3. Buat Konten HTML untuk PDF
// =================================
ob_start(); // Mulai output buffering
?>
<style>
    body { font-family: sans-serif; font-size: 10pt; }
    h1 { font-size: 24pt; color: #333; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .text-right { text-align: right; }
    .header { text-align: center; margin-bottom: 20px; }
    .invoice-details { margin-bottom: 20px; }
    .invoice-details table td { border: 0; padding: 2px; }
</style>

<div class="header">
    <h1>INVOICE</h1>
    <strong><?= sanitize($meta['nama_instansi']); ?></strong><br>
    <?= sanitize($meta['alamat']); ?><br>
    Telp: <?= sanitize($meta['telepon']); ?> | Email: <?= sanitize($meta['email']); ?>
</div>
<hr>

<div class="invoice-details">
    <table>
        <tr>
            <td width="50%">
                <strong>Ditagihkan Kepada:</strong><br>
                <?= sanitize($pesanan['nama_lengkap']); ?><br>
                <?= sanitize($pesanan['telepon']); ?><br>
                <?= nl2br(sanitize($pesanan['alamat_kirim'])); ?>
            </td>
            <td width="50%" style="text-align: right;">
                <strong>No. Invoice:</strong> <?= sanitize($pesanan['nomor_pesanan']); ?><br>
                <strong>Tanggal:</strong> <?= tgl_indo($pesanan['tanggal_pesanan']); ?>
            </td>
        </tr>
    </table>
</div>

<h4>Detail Pesanan:</h4>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach($detail_items as $item): ?>
        <tr>
            <td><?= $i++; ?></td>
            <td><?= sanitize($item['nama_produk']); ?></td>
            <td><?= format_rupiah($item['harga_saat_pesan']); ?></td>
            <td><?= $item['jumlah']; ?></td>
            <td class="text-right"><?= format_rupiah($item['subtotal']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><td colspan="4" class="text-right">Subtotal Produk</td><td class="text-right"><?= format_rupiah($pesanan['total_harga_produk']); ?></td></tr>
        <tr><td colspan="4" class="text-right">Biaya Kirim</td><td class="text-right"><?= format_rupiah($pesanan['biaya_kirim']); ?></td></tr>
        <tr><td colspan="4" class="text-right">Diskon</td><td class="text-right"> <?= format_rupiah($pesanan['nilai_diskon']); ?></td></tr>
        <tr><th colspan="4" class="text-right">Grand Total</th><th class="text-right"><?= format_rupiah($pesanan['total_final']); ?></th></tr>
    </tfoot>
</table>

<?php
$html = ob_get_contents(); // Simpan output HTML ke variabel
ob_end_clean(); // Hentikan dan bersihkan output buffer

// 4. Generate PDF menggunakan mPDF
// ===================================
$mpdf = new mPDF('c', 'A4', '', '', 15, 15, 16, 16, 9, 9, 'L');
$mpdf->WriteHTML($html);
$nama_file_pdf = "invoice-" . $pesanan['nomor_pesanan'] . ".pdf";
$mpdf->Output($nama_file_pdf, 'I'); // 'I' untuk tampil di browser, 'D' untuk langsung download
exit;
?>