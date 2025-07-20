<?php
// Ninda/admin/pages/beranda.php

// =================================================================
// 1. MENGAMBIL DATA UNTUK KARTU STATISTIK
// =================================================================

// Ambil Total Penjualan (Hanya dari pesanan yang sudah selesai)
$sql_penjualan = "SELECT SUM(total_final) AS total FROM pesanan WHERE status_pesanan = 'selesai'";
$result_penjualan = $koneksi->query($sql_penjualan);
$total_penjualan = $result_penjualan->fetch_assoc()['total'] ?? 0;

// Ambil Total Pembelian (Hanya dari restok yang sudah diterima)
$sql_pembelian = "SELECT SUM(total_biaya) AS total FROM pembelian_stok WHERE status = 'diterima'";
$result_pembelian = $koneksi->query($sql_pembelian);
$total_pembelian = $result_pembelian->fetch_assoc()['total'] ?? 0;

// Ambil Jumlah Pelanggan
$sql_pelanggan = "SELECT COUNT(id_pengguna) AS total FROM pengguna WHERE peran = 'pelanggan'";
$result_pelanggan = $koneksi->query($sql_pelanggan);
$jumlah_pelanggan = $result_pelanggan->fetch_assoc()['total'] ?? 0;

// Ambil Jumlah Produk
$sql_produk = "SELECT COUNT(id_produk) AS total FROM produk";
$result_produk = $koneksi->query($sql_produk);
$jumlah_produk = $result_produk->fetch_assoc()['total'] ?? 0;


// =================================================================
// BARIS BARU: 2. PENGATURAN FILTER DAN PENGAMBILAN DATA GRAFIK
// =================================================================

// Tentukan filter bulan dan tahun, defaultnya bulan dan tahun saat ini
$filter_bulan = $_GET['bulan'] ?? date('m');
$filter_tahun = $_GET['tahun'] ?? date('Y');

// Query untuk mengambil data penjualan harian berdasarkan filter bulan dan tahun
$sql_chart = "SELECT 
                DAY(tanggal_pesanan) as hari, 
                SUM(total_final) as total 
              FROM pesanan 
              WHERE 
                status_pesanan = 'selesai' AND 
                MONTH(tanggal_pesanan) = '$filter_bulan' AND
                YEAR(tanggal_pesanan) = '$filter_tahun'
              GROUP BY DAY(tanggal_pesanan)
              ORDER BY hari ASC";

$result_chart = $koneksi->query($sql_chart);

// -- Mempersiapkan data untuk ApexCharts --

// Buat array berisi semua tanggal dalam satu bulan
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $filter_bulan, $filter_tahun);
$chart_labels = [];
for ($i = 1; $i <= $jumlah_hari; $i++) {
    $chart_labels[] = str_pad($i, 2, '0', STR_PAD_LEFT); // Format tanggal "01", "02", ...
}

// Buat array data penjualan dengan nilai default 0
$chart_data = array_fill(0, $jumlah_hari, 0);

// Isi array data dengan hasil query dari database
if ($result_chart) {
    while ($row = $result_chart->fetch_assoc()) {
        // Index array dimulai dari 0, jadi hari ke-N ada di index N-1
        $hari_ke = (int)$row['hari'] - 1;
        $chart_data[$hari_ke] = (int)$row['total'];
    }
}

// Konversi array PHP ke format JSON untuk JavaScript
$json_chart_labels = json_encode($chart_labels);
$json_chart_data = json_encode($chart_data);


// =================================================================
// 3. MENGAMBIL DATA UNTUK DAFTAR (LISTS) - TIDAK ADA PERUBAHAN DI SINI
// =================================================================

// Ambil 5 Produk Terlaris
$sql_terlaris = "SELECT p.nama_produk, p.gambar_produk, SUM(pd.jumlah) AS total_terjual
                 FROM pesanan_detail pd
                 JOIN produk p ON pd.id_produk = p.id_produk
                 GROUP BY pd.id_produk
                 ORDER BY total_terjual DESC
                 LIMIT 5";
$result_terlaris = $koneksi->query($sql_terlaris);
$produk_terlaris_list = [];
if ($result_terlaris && $result_terlaris->num_rows > 0) {
    while($row = $result_terlaris->fetch_assoc()) {
        $produk_terlaris_list[] = $row;
    }
}

// Ambil 5 Produk dengan Stok Menipis (Stok <= 10)
$sql_stok_menipis = "SELECT nama_produk, stok, gambar_produk 
                     FROM produk 
                     WHERE stok <= 10 
                     ORDER BY stok ASC 
                     LIMIT 5";
$result_stok_menipis = $koneksi->query($sql_stok_menipis);
$stok_menipis_list = [];
if ($result_stok_menipis && $result_stok_menipis->num_rows > 0) {
    while($row = $result_stok_menipis->fetch_assoc()) {
        $stok_menipis_list[] = $row;
    }
}

// Ambil 5 Pesanan Terbaru
$sql_pesanan_terbaru = "SELECT p.nomor_pesanan, p.total_final, p.status_pesanan, u.nama_lengkap
                        FROM pesanan p
                        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                        ORDER BY p.tanggal_pesanan DESC
                        LIMIT 5";
$result_pesanan_terbaru = $koneksi->query($sql_pesanan_terbaru);
$pesanan_terbaru_list = [];
if ($result_pesanan_terbaru && $result_pesanan_terbaru->num_rows > 0) {
    while($row = $result_pesanan_terbaru->fetch_assoc()) {
        $pesanan_terbaru_list[] = $row;
    }
}

?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Beranda</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-height-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                            <i class="bx bx-dollar-circle"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-3">Total Penjualan</p>
                        <h4 class="fs-4 mb-0"><?= format_rupiah($total_penjualan); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-height-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-2 fs-2">
                            <i class="bx bx-shopping-bag"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-3">Total Pembelian</p>
                        <h4 class="fs-4 mb-0"><?= format_rupiah($total_pembelian); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-height-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2">
                            <i class="bx bx-user-circle"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-3">Jumlah Pelanggan</p>
                        <h4 class="fs-4 mb-0"><?= $jumlah_pelanggan; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-height-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">
                            <i class="bx bx-box"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-3">Jumlah Produk</p>
                        <h4 class="fs-4 mb-0"><?= $jumlah_produk; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="card-title flex-grow-1 mb-0">Grafik Penjualan Bulanan</h4>
                <div class="flex-shrink-0">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="beranda">
                        <select name="bulan" class="form-select form-select-sm">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $nama_bulan = DateTime::createFromFormat('!m', $m)->format('F');
                                $selected = ($m == $filter_bulan) ? 'selected' : '';
                                echo "<option value='$m' $selected>$nama_bulan</option>";
                            }
                            ?>
                        </select>
                        <select name="tahun" class="form-select form-select-sm">
                             <?php
                            $tahun_awal = 2023;
                            $tahun_sekarang = date('Y');
                            for ($y = $tahun_sekarang; $y >= $tahun_awal; $y--) {
                                $selected = ($y == $filter_tahun) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div id="sales-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header d-flex align-items-center">
                <h4 class="card-title flex-grow-1 mb-0">Produk Terlaris</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($produk_terlaris_list)): ?>
                    <?php foreach ($produk_terlaris_list as $produk): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <img src="assets/images/produk/<?= sanitize($produk['gambar_produk']); ?>" alt="" class="avatar-sm rounded-circle p-1">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fs-md mb-0"><?= sanitize($produk['nama_produk']); ?></h6>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge bg-primary-subtle text-primary"><?= $produk['total_terjual']; ?> Terjual</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Belum ada data penjualan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header d-flex align-items-center">
                <h4 class="card-title flex-grow-1 mb-0">Stok Menipis</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($stok_menipis_list)): ?>
                    <?php foreach ($stok_menipis_list as $produk): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <img src="assets/images/produk/<?= sanitize($produk['gambar_produk']); ?>" alt="" class="avatar-sm rounded-circle p-1">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fs-md mb-0"><?= sanitize($produk['nama_produk']); ?></h6>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge bg-danger-subtle text-danger">Sisa <?= $produk['stok']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Semua stok dalam kondisi aman.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header d-flex align-items-center">
                <h4 class="card-title flex-grow-1 mb-0">Pesanan Terbaru</h4>
            </div>
            <div class="card-body">
                 <?php if (!empty($pesanan_terbaru_list)): ?>
                    <?php foreach ($pesanan_terbaru_list as $pesanan): ?>
                        <div class="d-flex mb-3"> 
                        <div class="flex-shrink-0 avatar-initials me-2">
                            <?= sanitize(get_initials($pesanan['nama_lengkap'])); ?> 
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fs-md mb-0"><?= sanitize($pesanan['nomor_pesanan']); ?></h6>
                            <p class="text-muted mb-0">Oleh: <?= sanitize($pesanan['nama_lengkap']); ?> - <?= format_rupiah($pesanan['total_final']); ?></p>
                        </div>
                        <div class="flex-shrink-0">
                            <?php 
                                $status = $pesanan['status_pesanan'];
                                $badge_class = 'bg-secondary-subtle text-secondary';
                                if ($status == 'selesai') $badge_class = 'bg-success-subtle text-success';
                                if ($status == 'diproses') $badge_class = 'bg-info-subtle text-info';
                                if ($status == 'sedang_diantar') $badge_class = 'bg-primary-subtle text-primary';
                                if ($status == 'dibatalkan') $badge_class = 'bg-danger-subtle text-danger';
                            ?>
                            <span class="badge <?= $badge_class; ?>"><?= ucfirst(str_replace('_', ' ', $status)); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Belum ada pesanan masuk.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Ambil data dari PHP yang sudah di-format ke JSON
    const chartLabels = <?php echo $json_chart_labels; ?>;
    const chartData = <?php echo $json_chart_data; ?>;

    var options = {
      series: [{
          name: 'Pendapatan',
          data: chartData
      }],
      chart: {
          height: 350,
          type: 'area',
          toolbar: {
            show: false
          }
      },
      dataLabels: {
          enabled: false
      },
      stroke: {
          curve: 'smooth',
          width: 2
      },
      xaxis: {
          categories: chartLabels,
          title: {
            text: 'Tanggal'
          }
      },
      yaxis: {
        title: {
            text: 'Pendapatan (Rp)'
        },
        labels: {
            formatter: function (value) {
                // Format angka menjadi ribuan, jutaan, dst.
                if (value >= 1000000) {
                    return "Rp " + (value / 1000000).toFixed(1) + " Jt";
                }
                if (value >= 1000) {
                    return "Rp " + (value / 1000).toFixed(0) + " Rb";
                }
                return "Rp " + value;
            }
        }
      },
      tooltip: {
        y: {
            formatter: function (val) {
                // Format tooltip menjadi format Rupiah lengkap
                return "Rp " + new Intl.NumberFormat('id-ID').format(val);
            }
        }
      },
      colors: ['#007bff'], // Warna biru primer
      fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.2,
            stops: [0, 90, 100]
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#sales-chart"), options);
    chart.render();
});
</script>