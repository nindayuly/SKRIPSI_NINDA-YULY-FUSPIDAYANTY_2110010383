<?php
// Ninda/admin/pages/laporan_pelanggan.php

// Logika untuk filter
$search = $_GET['search'] ?? '';
$where_clause = "";
if (!empty($search)) {
    $search_safe = $koneksi->real_escape_string($search);
    // Mencari berdasarkan nama, email, telepon, atau alamat
    $where_clause = "WHERE (nama_lengkap LIKE '%$search_safe%' OR email LIKE '%$search_safe%' OR telepon LIKE '%$search_safe%' OR alamat LIKE '%$search_safe%')";
}

// Query untuk mengambil data pelanggan
$sql_laporan = "SELECT 
                    nama_lengkap, 
                    email, 
                    telepon, 
                    alamat, 
                    tanggal_daftar,
                    (SELECT COUNT(*) FROM pesanan WHERE id_pengguna = pengguna.id_pengguna AND status_pesanan = 'selesai') as total_transaksi
                FROM pengguna
                WHERE peran = 'pelanggan'
                $where_clause
                ORDER BY tanggal_daftar DESC";

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
        <h4>Laporan Pelanggan</h4>
        <h6>Menampilkan semua data pelanggan terdaftar.</h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-top"> 
            <div class="wordset">
                 <div class="d-flex gap-2">
                    <a href="pages/laporan_pelanggan_cetak.php?search=<?= urlencode($search); ?>" target="_blank" class="btn btn-sm btn-danger">
                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th class="text-center">Total Transaksi</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= sanitize($data['nama_lengkap']); ?></td>
                            <td>
                                <div>Email: <?= sanitize($data['email']); ?></div>
                                <div>Telepon: <?= sanitize($data['telepon'] ?? '-'); ?></div>
                            </td>
                            <td><?= sanitize($data['alamat'] ?? '-'); ?></td>
                            <td class="text-center"><?= $data['total_transaksi']; ?></td>
                            <td><?= tgl_indo($data['tanggal_daftar']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data pelanggan yang cocok dengan kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>