<?php
// Ninda/admin/pages/laporan_stok_barang.php

// Ambil semua kategori untuk filter dropdown
$kategori_list = $koneksi->query("SELECT id_kategori, nama_kategori FROM kategori_produk ORDER BY nama_kategori ASC");

// Logika untuk filter
$kategori_filter = (int)($_GET['kategori'] ?? 0);

// Klausa WHERE dinamis berdasarkan filter kategori
$where_clause = "";
if ($kategori_filter > 0) {
    $where_clause = "WHERE p.id_kategori = $kategori_filter";
}

// Query TUNGGAL untuk mengambil semua data yang dibutuhkan
$sql_laporan = "SELECT 
                    p.kode_produk,
                    p.nama_produk,
                    kp.nama_kategori,
                    p.stok,
                    p.harga_jual,
                    (p.stok * p.harga_jual) AS nilai_stok
                FROM produk p
                LEFT JOIN kategori_produk kp ON p.id_kategori = kp.id_kategori
                $where_clause
                ORDER BY p.nama_produk ASC";

$result_laporan = $koneksi->query($sql_laporan);

// --- PENGOLAHAN DATA YANG LEBIH EFISIEN ---
// Inisialisasi array untuk menampung data
$laporan_data = [];
$summary_data = [
    'total_item' => 0,
    'total_nilai' => 0
];

// Loop melalui hasil query, simpan data detail dan hitung summary secara bersamaan
if ($result_laporan) {
    while($row = $result_laporan->fetch_assoc()) {
        // 1. Simpan data baris ke dalam array untuk ditampilkan di tabel
        $laporan_data[] = $row;

        // 2. Akumulasikan data untuk summary (tanpa perlu query lagi)
        $summary_data['total_item'] += $row['stok'];
        $summary_data['total_nilai'] += $row['nilai_stok'];
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h4>Laporan Stok Barang</h4>
        <h6>Melihat inventaris barang secara keseluruhan.</h6>
    </div>
</div>



<div class="card">
    <div class="card-body"> 
        <div class="row">
            <div class="col-lg-6 col-sm-6 col-12">
                <div class="dash-widget">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters" data-count="<?= (int)$summary_data['total_nilai']; ?>">Rp <?= number_format($summary_data['total_nilai']); ?></span></h5>
                        <h6>Total Nilai Inventaris (Harga Jual)</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-sm-6 col-12">
                <div class="dash-widget dash1">
                    <div class="dash-widgetcontent">
                        <h5><span class="counters" data-count="<?= (int)$summary_data['total_item']; ?>"><?= number_format($summary_data['total_item']); ?></span></h5>
                        <h6>Total Item dalam Stok</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-0" id="filter_inputs">
            <div class="card-body pb-0">
                <form method="GET">
                    <input type="hidden" name="page" value="laporan_stok_barang">
                    <div class="row align-items-end">
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Filter Berdasarkan Kategori</label>
                                <select name="kategori" class="form-select">
                                    <option value="0" <?= ($kategori_filter == 0) ? 'selected' : ''; ?>>Semua Kategori</option>
                                    <?php
                                    // Reset pointer hasil query kategori untuk loop baru
                                    $kategori_list->data_seek(0);
                                    while($kategori = $kategori_list->fetch_assoc()):
                                    ?>
                                        <option value="<?= $kategori['id_kategori']; ?>" <?= ($kategori_filter == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                                            <?= sanitize($kategori['nama_kategori']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter Laporan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <a href="pages/laporan_stok_barang_cetak.php?kategori=<?= sanitize($kategori_filter); ?>" target="_blank" class="btn btn-sm btn-danger mb-3">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-end">Harga Jual Satuan</th>
                        <th class="text-end">Nilai Total Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($laporan_data)): $no = 1; ?>
                        <?php foreach($laporan_data as $data): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= sanitize($data['kode_produk']); ?></td>
                            <td><?= sanitize($data['nama_produk']); ?></td>
                            <td><?= sanitize($data['nama_kategori'] ?? 'Tidak ada kategori'); ?></td>
                            <td class="text-center fw-bold"><?= sanitize($data['stok']); ?> Unit</td>
                            <td class="text-end">Rp <?= number_format($data['harga_jual']); ?></td>
                            <td class="text-end">Rp <?= number_format($data['nilai_stok']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data stok yang cocok dengan kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>