<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Validasi login
if (!isset($_SESSION['customer_id'])) {
    header('Location: login_customer.php');
    exit();
}
$id_pelanggan = $_SESSION['customer_id'];

// Ambil aksi dan id dari URL
$aksi = $_GET['aksi'] ?? '';
$id_pesanan = (int)($_GET['id'] ?? 0);

if ($aksi === 'selesai' && $id_pesanan > 0) {
    // Mulai transaksi database
    $koneksi->begin_transaction();

    try {
        // Ambil detail pesanan dan pelanggan untuk notifikasi dan validasi
        $sql_get_data = "SELECT p.id_kurir, p.nomor_pesanan, u.nama_lengkap, u.telepon 
                         FROM pesanan p
                         JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                         WHERE p.id_pesanan = $id_pesanan 
                         AND p.id_pengguna = $id_pelanggan 
                         AND p.status_pesanan = 'sedang_diantar'";
        $result_data = $koneksi->query($sql_get_data);

        if ($result_data && $result_data->num_rows > 0) {
            $data_pesanan = $result_data->fetch_assoc();
            $id_kurir = $data_pesanan['id_kurir'];

            // 1. Update status pesanan menjadi 'selesai'
            $sql_update_pesanan = "UPDATE pesanan SET status_pesanan = 'selesai' WHERE id_pesanan = $id_pesanan";
            if (!$koneksi->query($sql_update_pesanan)) {
                throw new Exception("Gagal update status pesanan.");
            }

            // 2. Update status kurir (jika ada) menjadi 'tersedia' kembali
            if (!empty($id_kurir)) {
                $sql_update_kurir = "UPDATE kurir_internal SET status = 'tersedia' WHERE id_kurir = $id_kurir";
                if (!$koneksi->query($sql_update_kurir)) {
                    throw new Exception("Gagal update status kurir.");
                }
            }
            
            // --- MULAI BLOK NOTIFIKASI WHATSAPP UNTUK PELANGGAN ---
            // Ambil nama toko dari tabel meta
            $meta = $koneksi->query("SELECT nama_instansi FROM meta WHERE id_meta = 1")->fetch_assoc();
            $nama_toko = $meta['nama_instansi'] ?? 'Toko Anda';
            $telepon_pelanggan = $data_pesanan['telepon'];

            if (!empty($telepon_pelanggan)) {
                $pesan_wa_pelanggan = "Hai *" . sanitize($data_pesanan['nama_lengkap']) . "*,\n\n" .
                                      "Terima kasih atas konfirmasi Anda! 🙏\n\n" .
                                      "Pesanan Anda dengan nomor *#{$data_pesanan['nomor_pesanan']}* telah kami tandai sebagai *SELESAI*.\n\n" .
                                      "Kami harap Anda puas dengan produk yang diterima. Jangan ragu untuk memberikan ulasan produk ya! Kami tunggu pesanan Anda selanjutnya. 😊\n\n" .
                                      "Salam hangat,\nTim *" . $nama_toko . "*";

                kirim_wa($telepon_pelanggan, $pesan_wa_pelanggan);
            }
            // --- SELESAI BLOK NOTIFIKASI WHATSAPP ---

            // Commit transaksi jika semua berhasil
            $koneksi->commit();
            set_sweetalert('success', 'Berhasil!', 'Terima kasih telah berbelanja di toko kami.');

        } else {
            // Jika pesanan tidak ditemukan atau statusnya salah
            set_sweetalert('error', 'Gagal!', 'Aksi tidak valid atau pesanan tidak ditemukan.');
        }

    } catch (Exception $e) {
        $koneksi->rollback();
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan pada database.');
    }
}

// Redirect kembali ke halaman riwayat pesanan
header('Location: riwayat_pesanan.php');
exit();
?>