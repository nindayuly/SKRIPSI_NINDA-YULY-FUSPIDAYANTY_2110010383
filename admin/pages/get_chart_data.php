<?php
require_once '../inc/koneksi.php';

header('Content-Type: application/json');

$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Inisialisasi array untuk setiap hari dalam sebulan dengan nilai 0
$penjualan_per_hari = array_fill(1, $jumlah_hari, 0);
$pembelian_per_hari = array_fill(1, $jumlah_hari, 0);
$categories = range(1, $jumlah_hari); // Label untuk sumbu X (tanggal 1 s/d 31)

// Ambil data penjualan (pesanan selesai)
$sql_penjualan = "SELECT DAY(tanggal_pesanan) AS hari, SUM(total_final) AS total 
                  FROM pesanan 
                  WHERE status_pesanan = 'selesai' AND MONTH(tanggal_pesanan) = $bulan AND YEAR(tanggal_pesanan) = $tahun 
                  GROUP BY DAY(tanggal_pesanan)";
$result_penjualan = $koneksi->query($sql_penjualan);
if ($result_penjualan) {
    while ($row = $result_penjualan->fetch_assoc()) {
        $penjualan_per_hari[(int)$row['hari']] = (float)$row['total'];
    }
}

// Ambil data pembelian (stok diterima)
$sql_pembelian = "SELECT DAY(tanggal_pembelian) AS hari, SUM(total_biaya) AS total 
                  FROM pembelian_stok 
                  WHERE status = 'diterima' AND MONTH(tanggal_pembelian) = $bulan AND YEAR(tanggal_pembelian) = $tahun 
                  GROUP BY DAY(tanggal_pembelian)";
$result_pembelian = $koneksi->query($sql_pembelian);
if ($result_pembelian) {
    while ($row = $result_pembelian->fetch_assoc()) {
        $pembelian_per_hari[(int)$row['hari']] = (float)$row['total'];
    }
}

// Kirim data dalam format JSON
echo json_encode([
    'penjualan'  => array_values($penjualan_per_hari),
    'pembelian'  => array_values($pembelian_per_hari),
    'categories' => $categories
]);
?>