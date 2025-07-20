-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 01:27 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecom_sparepart`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kategori_produk`
--

INSERT INTO `kategori_produk` (`id_kategori`, `nama_kategori`, `slug`) VALUES
(1, 'Mesin & Komponen', 'mesin-komponen'),
(2, 'Kelistrikan & Aki', 'kelistrikan-aki'),
(3, 'Body & Rangka', 'body-rangka'),
(4, 'Ban & Velg', 'ban-velg'),
(5, 'Sistem Pengereman', 'sistem-pengereman'),
(6, 'Oli & Cairan', 'oli-cairan'),
(7, 'Aksesoris Klasik', 'aksesoris-klasik'),
(8, 'Lampu & Bohlam', 'lampu-bohlam'),
(9, 'Gir & Rantai', 'gir-rantai'),
(10, 'Filter & Karburator', 'filter-karburator');

-- --------------------------------------------------------

--
-- Table structure for table `kurir_internal`
--

CREATE TABLE `kurir_internal` (
  `id_kurir` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `nama_kurir` varchar(255) NOT NULL,
  `telepon_kurir` varchar(20) NOT NULL,
  `nomor_polisi` varchar(15) DEFAULT NULL,
  `status` enum('tersedia','bertugas','nonaktif') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kurir_internal`
--

INSERT INTO `kurir_internal` (`id_kurir`, `id_pengguna`, `nama_kurir`, `telepon_kurir`, `nomor_polisi`, `status`) VALUES
(1, 5, 'Bambang Kurir', '081211110001', 'B 1234 ABC', 'tersedia'),
(2, 6, 'Cecep Supriadi', '081211110002', 'D 5678 DEF', 'tersedia'),
(3, 7, 'Dedi Mulyadi', '081211110003', 'E 9101 GHI', 'tersedia'),
(4, 8, 'Eko Widodo', '081211110004', 'F 1121 JKL', 'tersedia'),
(5, 9, 'Fajar Hidayat', '081211110005', 'G 3141 MNO', 'tersedia'),
(6, 10, 'Gatot Kaca', '081211110006', 'H 5161 PQR', 'tersedia'),
(7, 11, 'Herman Susanto', '081211110007', 'K 7181 STU', 'tersedia'),
(8, 12, 'Iwan Fals', '081211110008', 'L 9202 VWX', 'tersedia'),
(9, 13, 'Joko Anwar', '081211110009', 'M 1222 YZA', 'tersedia'),
(10, 14, 'Kaka Slank', '081211110010', 'N 3242 BCD', 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_kurir_terkini`
--

CREATE TABLE `lokasi_kurir_terkini` (
  `id_kurir` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `terakhir_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `meta`
--

CREATE TABLE `meta` (
  `id_meta` int(11) NOT NULL,
  `nama_instansi` varchar(255) DEFAULT NULL,
  `pimpinan` varchar(255) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `meta`
--

INSERT INTO `meta` (`id_meta`, `nama_instansi`, `pimpinan`, `telepon`, `email`, `alamat`, `logo`) VALUES
(1, 'Adong Classic Motor Shopp', 'Bapak Adong', '0812-3456-7890', 'info@adongclassic.com', 'Jl. Raya Sparepart No. 45, Jakarta Pusat, 10110', '68727f8fc286a-logoapp.png');

-- --------------------------------------------------------

--
-- Table structure for table `pemasok`
--

CREATE TABLE `pemasok` (
  `id_pemasok` int(11) NOT NULL,
  `nama_pemasok` varchar(255) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pemasok`
--

INSERT INTO `pemasok` (`id_pemasok`, `nama_pemasok`, `telepon`, `email`, `alamat`, `catatan`) VALUES
(1, 'PT Suku Cadang Jaya', '021-555-0101', 'sales@scjaya.com', 'Jl. Industri Raya No. 1, Jakarta', 'Pemasok utama komponen mesin'),
(2, 'CV Laju Motor', '022-444-0202', 'info@lajumotor.id', 'Jl. Otomotif No. 2, Bandung', 'Spesialis body part'),
(3, 'UD Terang Benderang', '031-777-0303', 'kontak@terang.co.id', 'Jl. Pahlawan No. 3, Surabaya', 'Aki dan sistem kelistrikan'),
(4, 'Toko Ban Abadi', '024-888-0404', 'order@banabadi.com', 'Jl. Gajah Mada No. 4, Semarang', 'Pemasok ban luar dan dalam'),
(5, 'Sinar Rem Cakram', '061-999-0505', 'sinarrem@email.com', 'Jl. Merdeka No. 5, Medan', 'Kampas rem dan minyak rem'),
(6, 'Oli Nusantara Corp', '021-222-0606', 'marketing@olinusantara.com', 'Kawasan Industri Pulogadung, Jakarta', 'Distributor resmi berbagai merk oli'),
(7, 'Classic Part Corner', '081234567890', 'classicpart@gmail.com', 'Jl. Kenangan No. 7, Yogyakarta', 'Aksesoris khusus motor klasik'),
(8, 'Bohlam Sejati', '031-111-0808', 'cahaya@bohlamsejati.net', 'Jl. Diponegoro No. 8, Surabaya', 'Lampu depan, sein, dan bohlam'),
(9, 'Rantai Emas Perkasa', '022-333-0909', 'sales@rantaiemas.com', 'Jl. Cibaduyut No. 9, Bandung', 'Gir set dan rantai'),
(10, 'Filter Prima Mandiri', '021-666-1010', 'filterprima@email.com', 'Jl. Daan Mogot No. 10, Jakarta', 'Filter udara dan karburator');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `metode_bayar` varchar(50) NOT NULL,
  `jumlah_bayar` int(11) NOT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `status_bayar` enum('pending','success','failed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pesanan`, `metode_bayar`, `jumlah_bayar`, `tanggal_bayar`, `bukti_bayar`, `status_bayar`) VALUES
(1, 1, 'transfer', 263750, '2025-07-13 05:49:34', '6872d86ead138-img-1593599519.jpg', 'success'),
(2, 2, 'transfer', 976500, '2025-07-13 06:10:58', '6872dd729d841-img-1593599519.jpg', 'success'),
(3, 3, 'transfer', 212000, '2025-07-18 18:23:42', '687a20ae3e9e2-avatar-8.jpg', 'success'),
(4, 4, 'transfer', 611000, '2025-07-18 18:27:45', '687a21a18c46f-avatar-4.jpg', 'success'),
(5, 5, 'transfer', 625000, '2025-07-18 18:33:01', NULL, 'success'),
(6, 6, 'transfer', 285000, '2025-07-18 18:48:53', '687a2695e98a3-4.png', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_stok`
--

CREATE TABLE `pembelian_stok` (
  `id_pembelian` int(11) NOT NULL,
  `id_pemasok` int(11) DEFAULT NULL,
  `nomor_referensi` varchar(100) DEFAULT NULL,
  `tanggal_pembelian` date NOT NULL,
  `total_biaya` int(11) NOT NULL DEFAULT 0,
  `status` enum('dipesan','diterima','dibatalkan') NOT NULL DEFAULT 'dipesan',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pembelian_stok`
--

INSERT INTO `pembelian_stok` (`id_pembelian`, `id_pemasok`, `nomor_referensi`, `tanggal_pembelian`, `total_biaya`, `status`, `catatan`) VALUES
(1, 6, '882172', '2025-07-13', 2000000, 'diterima', '-');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_stok_detail`
--

CREATE TABLE `pembelian_stok_detail` (
  `id_pembelian_detail` int(11) NOT NULL,
  `id_pembelian` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_beli_satuan` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pembelian_stok_detail`
--

INSERT INTO `pembelian_stok_detail` (`id_pembelian_detail`, `id_pembelian`, `id_produk`, `jumlah`, `harga_beli_satuan`, `subtotal`) VALUES
(1, 1, 3, 10, 200000, 2000000);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `peran` enum('pelanggan','admin','kurir') NOT NULL DEFAULT 'pelanggan',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `email`, `telepon`, `alamat`, `password`, `peran`, `tanggal_daftar`) VALUES
(1, 'Ninda Yuly Fuspidayanty', 'admin@adongclassic.com', '081200000001', 'Kantor Bengkel Adong', 'admin', 'admin', '2025-07-12 02:00:00'),
(2, 'Budi Kurir', 'budi.kurir@adongclassic.com', '081300000002', 'Mess Karyawan, Jl. Damai', '$2y$10$H.t9k1nL1qZ.D8cJ2Y.H.f3WzC5yY7kM3p.Q9wR1sX2bA4nC6oD8f', 'kurir', '2025-07-12 02:05:00'),
(3, 'Andi Saputra', 'andi.saputra@email.com', '081500000003', 'Jl. Merdeka No. 10, Bandung', '$2y$10$I.u9j1nO1qA.E8cJ2Z.I.g3WzC5yY7kM3p.Q9wR1sX2bA4nC6oD8g', 'pelanggan', '2025-07-12 03:20:00'),
(4, 'Siti Aminah', 'siti.aminah@email.com', '081800000004', 'Gg. Mawar No. 5, Surabaya', '$2y$10$J.v9k1nO1qB.F8cJ2A.J.h3WzC5yY7kM3p.Q9wR1sX2bA4nC6oD8h', 'pelanggan', '2025-07-12 06:45:00'),
(5, 'Bambang Kurir', 'bambang@kurir.com', '081211110001', 'Jl. Kurir 1', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(6, 'Cecep Supriadi', 'cecep@kurir.com', '081211110002', 'Jl. Kurir 2', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(7, 'Dedi Mulyadi', 'dedi@kurir.com', '081211110003', 'Jl. Kurir 3', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(8, 'Eko Widodo', 'eko@kurir.com', '081211110004', 'Jl. Kurir 4', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(9, 'Fajar Hidayat', 'fajar@kurir.com', '081211110005', 'Jl. Kurir 5', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(10, 'Gatot Kaca', 'gatot@kurir.com', '081211110006', 'Jl. Kurir 6', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(11, 'Herman Susanto', 'herman@kurir.com', '081211110007', 'Jl. Kurir 7', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(12, 'Iwan Fals', 'iwan@kurir.com', '081211110008', 'Jl. Kurir 8', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(13, 'Joko Anwar', 'joko@kurir.com', '081211110009', 'Jl. Kurir 9', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(14, 'Kaka Slank', 'kaka@kurir.com', '081211110010', 'Jl. Kurir 10', 'kurir123', 'kurir', '2025-07-12 16:31:51'),
(15, 'Maulana Aditya', 'maulanaadit@gmail.com', '0895329695138', 'Jl. Kuripan', 'adit', 'pelanggan', '2025-07-12 20:08:34'),
(16, 'Mugeni', 'mugeni@gmail.com', '0895329695138', 'Jl. Ahmad Yani', 'mugeni', 'pelanggan', '2025-07-18 10:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `id_promosi` int(11) DEFAULT NULL,
  `id_kurir` int(11) DEFAULT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `total_harga_produk` int(11) NOT NULL,
  `nilai_diskon` int(11) DEFAULT 0,
  `biaya_kirim` int(11) DEFAULT 0,
  `total_final` int(11) NOT NULL,
  `alamat_kirim` text NOT NULL,
  `catatan_pelanggan` text DEFAULT NULL,
  `status_pesanan` enum('menunggu_pembayaran','diproses','menunggu_kurir','sedang_diantar','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_pengguna`, `id_promosi`, `id_kurir`, `nomor_pesanan`, `total_harga_produk`, `nilai_diskon`, `biaya_kirim`, `total_final`, `alamat_kirim`, `catatan_pelanggan`, `status_pesanan`, `tanggal_pesanan`) VALUES
(1, 15, 1, 1, 'INV-202507-001', 275000, 41250, 30000, 263750, 'Jl. Kuripan', 'rumah lantai 2', 'selesai', '2025-07-12 21:29:02'),
(2, 15, 1, 2, 'INV-202507-002', 1090000, 163500, 50000, 976500, 'Jl. Kuripan', 'lantai 2 warna biru', 'selesai', '2025-07-12 22:09:23'),
(3, 15, 1, NULL, 'INV-202507-003', 220000, 33000, 25000, 212000, 'Jl. Kuripan', 'Rumah warna biru lantai 2', 'selesai', '2025-07-18 10:23:02'),
(4, 15, 1, NULL, 'INV-202507-004', 660000, 99000, 50000, 611000, 'Jl. Kuripan', 'Rumah ', 'selesai', '2025-07-18 10:27:18'),
(5, 16, 1, 1, 'INV-202507-005', 700000, 105000, 30000, 625000, 'Jl. Ahmad Yani', '-', 'selesai', '2025-07-18 10:32:35'),
(6, 16, 1, 1, 'INV-202507-006', 300000, 45000, 30000, 285000, 'Jl. Ahmad Yani', '-', 'selesai', '2025-07-18 10:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_detail`
--

CREATE TABLE `pesanan_detail` (
  `id_pesanan_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_saat_pesan` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pesanan_detail`
--

INSERT INTO `pesanan_detail` (`id_pesanan_detail`, `id_pesanan`, `id_produk`, `jumlah`, `harga_saat_pesan`, `subtotal`) VALUES
(1, 1, 8, 1, 275000, 275000),
(2, 2, 9, 1, 300000, 300000),
(3, 2, 6, 1, 350000, 350000),
(4, 2, 3, 2, 220000, 440000),
(5, 3, 3, 1, 220000, 220000),
(6, 4, 3, 3, 220000, 660000),
(7, 5, 6, 2, 350000, 700000),
(8, 6, 9, 1, 300000, 300000);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `kode_produk` varchar(50) DEFAULT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok` int(11) NOT NULL DEFAULT 0,
  `berat_gram` int(11) DEFAULT 0,
  `kondisi` enum('Baru','Bekas') DEFAULT 'Baru',
  `gambar_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `kode_produk`, `merk`, `deskripsi`, `harga_jual`, `stok`, `berat_gram`, `kondisi`, `gambar_produk`) VALUES
(1, 1, 'Piston Kit Honda C70', 'PRD-001', 'Honda Genuine', 'Piston kit lengkap untuk Honda C70, original AHM.', 150000, 50, 250, 'Baru', '68728f4d72b66-a9.jpeg'),
(2, 3, 'Spakbor Depan CB100', 'PRD-002', 'Aspira', 'Spakbor depan chrome untuk Honda CB100 K2.', 250000, 30, 1200, 'Baru', '68728f556db0e-a10.jpeg'),
(3, 2, 'Aki Kering GS Astra', 'PRD-003', 'GS Astra', 'Aki kering MF GTZ-5S untuk motor bebek dan matic.', 220000, 8, 1500, 'Baru', '68728eed12143-a1.jpeg'),
(4, 5, 'Kampas Rem Depan Vario', 'PRD-004', 'Federal', 'Kampas rem cakram depan untuk Honda Vario series.', 45000, 200, 150, 'Baru', '68728f12836bc-a5.jpeg'),
(5, 6, 'Oli Mesin MPX 2 0.8L', 'PRD-005', 'AHM Oil', 'Oli resmi Honda untuk motor matic.', 55000, 150, 850, 'Baru', '68728f448d731-a8.jpeg'),
(6, 7, 'Jok Pisah Japstyle Coklat', 'PRD-006', 'Lokal', 'Jok model custom japstyle warna coklat, bahan kulit sintetis.', 350000, 22, 1300, 'Baru', '68728f09753af-a4.jpeg'),
(7, 4, 'Ban Luar IRC 80/90-17', 'PRD-007', 'IRC', 'Ban luar tubetype ukuran 80/90 ring 17.', 180000, 80, 2000, 'Baru', '68728ef629347-a2.jpeg'),
(8, 9, 'Gear Set SSS 428 Supra X', 'PRD-008', 'SSS', 'Gear set (depan, belakang, rantai) untuk Supra X 125.', 275000, 39, 1600, 'Baru', '68728f00b6eb9-a3.jpeg'),
(9, 8, 'Lampu Depan Daymaker 5 inch', 'PRD-009', 'Lokal', 'Lampu depan LED Daymaker universal 5.75 inch.', 300000, 33, 800, 'Baru', '68728f3b3b841-a7.jpeg'),
(10, 10, 'Karburator PE 28', 'PRD-010', 'Keihin', 'Karburator Keihin PE 28, cocok untuk upgrade performa.', 450000, 20, 750, 'Baru', '68728f1c117bd-a6.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `promosi`
--

CREATE TABLE `promosi` (
  `id_promosi` int(11) NOT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tipe_diskon` enum('percentage','fixed') NOT NULL,
  `nilai_diskon` int(11) NOT NULL,
  `min_pembelian` int(11) DEFAULT 0,
  `tgl_mulai` datetime NOT NULL,
  `tgl_berakhir` datetime NOT NULL,
  `kuota_penggunaan` int(11) DEFAULT 100,
  `status_aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promosi`
--

INSERT INTO `promosi` (`id_promosi`, `kode_promo`, `deskripsi`, `tipe_diskon`, `nilai_diskon`, `min_pembelian`, `tgl_mulai`, `tgl_berakhir`, `kuota_penggunaan`, `status_aktif`) VALUES
(1, 'GASPOL15', 'Diskon 15% untuk semua produk', 'percentage', 15, 100000, '2025-07-01 00:00:00', '2025-07-31 23:59:59', 94, 1),
(2, 'ONGKIRGRATIS', 'Gratis Ongkir hingga Rp 20.000', 'fixed', 20000, 50000, '2025-07-10 00:00:00', '2025-08-10 23:59:59', 500, 1),
(3, 'CUCI GUDANG', 'Potongan langsung Rp 50.000 untuk aksesoris', 'fixed', 50000, 200000, '2025-07-15 00:00:00', '2025-07-20 23:59:59', 50, 1),
(4, 'KLASIKASIK', 'Diskon 20% khusus part motor klasik', 'percentage', 20, 0, '2025-08-01 00:00:00', '2025-08-31 23:59:59', 200, 1),
(5, 'KILAPLAGI', 'Diskon Rp 10.000 untuk semua jenis oli', 'fixed', 10000, 0, '2025-07-01 00:00:00', '2025-12-31 23:59:59', 9999, 1),
(6, 'SERBUJUNI', 'Promo spesial bulan Juni', 'percentage', 10, 0, '2025-06-01 00:00:00', '2025-06-30 23:59:59', 100, 0),
(7, 'FLASH99', 'Flash sale 9.9', 'percentage', 25, 150000, '2025-09-09 00:00:00', '2025-09-09 23:59:59', 100, 1),
(8, 'AKHIRTAHUN', 'Diskon besar akhir tahun', 'percentage', 30, 500000, '2025-12-15 00:00:00', '2025-12-31 23:59:59', 1000, 1),
(9, 'GAJIANSERU', 'Potongan Rp 75.000 saat gajian', 'fixed', 75000, 300000, '2025-07-25 00:00:00', '2025-07-28 23:59:59', 150, 1),
(10, 'EXPIREDPROMO', 'Promo yang sudah kadaluarsa', 'fixed', 10000, 0, '2024-01-01 00:00:00', '2024-01-31 23:59:59', 100, 0);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok_opname`
--

CREATE TABLE `riwayat_stok_opname` (
  `id_opname` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `stok_sebelumnya` int(11) NOT NULL,
  `stok_setelahnya` int(11) NOT NULL,
  `selisih` int(11) NOT NULL,
  `jenis_penyesuaian` enum('opname','retur_penjualan','barang_rusak','retur_pembelian','lainnya') NOT NULL DEFAULT 'opname',
  `catatan` text DEFAULT NULL,
  `tanggal_opname` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal_ulasan` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_pesanan_detail` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id_ulasan`, `id_pengguna`, `id_produk`, `rating`, `komentar`, `tanggal_ulasan`, `id_pesanan_detail`) VALUES
(1, 15, 9, 5, 'Best', '2025-07-12 23:29:27', 2),
(2, 15, 3, 5, 'nice part', '2025-07-18 10:25:11', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `kurir_internal`
--
ALTER TABLE `kurir_internal`
  ADD PRIMARY KEY (`id_kurir`),
  ADD UNIQUE KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `lokasi_kurir_terkini`
--
ALTER TABLE `lokasi_kurir_terkini`
  ADD PRIMARY KEY (`id_kurir`);

--
-- Indexes for table `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`id_meta`);

--
-- Indexes for table `pemasok`
--
ALTER TABLE `pemasok`
  ADD PRIMARY KEY (`id_pemasok`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_pesanan` (`id_pesanan`);

--
-- Indexes for table `pembelian_stok`
--
ALTER TABLE `pembelian_stok`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `id_pemasok` (`id_pemasok`);

--
-- Indexes for table `pembelian_stok_detail`
--
ALTER TABLE `pembelian_stok_detail`
  ADD PRIMARY KEY (`id_pembelian_detail`),
  ADD KEY `id_pembelian` (`id_pembelian`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_promosi` (`id_promosi`),
  ADD KEY `id_kurir` (`id_kurir`);

--
-- Indexes for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD PRIMARY KEY (`id_pesanan_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `promosi`
--
ALTER TABLE `promosi`
  ADD PRIMARY KEY (`id_promosi`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indexes for table `riwayat_stok_opname`
--
ALTER TABLE `riwayat_stok_opname`
  ADD PRIMARY KEY (`id_opname`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kurir_internal`
--
ALTER TABLE `kurir_internal`
  MODIFY `id_kurir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `meta`
--
ALTER TABLE `meta`
  MODIFY `id_meta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemasok`
--
ALTER TABLE `pemasok`
  MODIFY `id_pemasok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pembelian_stok`
--
ALTER TABLE `pembelian_stok`
  MODIFY `id_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembelian_stok_detail`
--
ALTER TABLE `pembelian_stok_detail`
  MODIFY `id_pembelian_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  MODIFY `id_pesanan_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promosi`
--
ALTER TABLE `promosi`
  MODIFY `id_promosi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `riwayat_stok_opname`
--
ALTER TABLE `riwayat_stok_opname`
  MODIFY `id_opname` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kurir_internal`
--
ALTER TABLE `kurir_internal`
  ADD CONSTRAINT `kurir_internal_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE;

--
-- Constraints for table `lokasi_kurir_terkini`
--
ALTER TABLE `lokasi_kurir_terkini`
  ADD CONSTRAINT `lokasi_kurir_terkini_ibfk_1` FOREIGN KEY (`id_kurir`) REFERENCES `kurir_internal` (`id_kurir`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE;

--
-- Constraints for table `pembelian_stok`
--
ALTER TABLE `pembelian_stok`
  ADD CONSTRAINT `pembelian_stok_ibfk_1` FOREIGN KEY (`id_pemasok`) REFERENCES `pemasok` (`id_pemasok`) ON DELETE SET NULL;

--
-- Constraints for table `pembelian_stok_detail`
--
ALTER TABLE `pembelian_stok_detail`
  ADD CONSTRAINT `pembelian_stok_detail_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian_stok` (`id_pembelian`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembelian_stok_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_promosi`) REFERENCES `promosi` (`id_promosi`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_kurir`) REFERENCES `kurir_internal` (`id_kurir`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD CONSTRAINT `pesanan_detail_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE SET NULL;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_produk` (`id_kategori`) ON DELETE SET NULL;

--
-- Constraints for table `riwayat_stok_opname`
--
ALTER TABLE `riwayat_stok_opname`
  ADD CONSTRAINT `riwayat_stok_opname_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_stok_opname_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL;

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
