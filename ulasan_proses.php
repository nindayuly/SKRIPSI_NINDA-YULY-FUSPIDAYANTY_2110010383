<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

// Validasi login
if (!isset($_SESSION['customer_id'])) {
    header('Location: login_customer.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan_detail = (int)$_POST['id_pesanan_detail'];
    $rating = (int)$_POST['rating'];
    $komentar = $koneksi->real_escape_string($_POST['komentar']);
    $id_pelanggan = $_SESSION['customer_id'];

    // Validasi input
    if ($rating < 1 || $rating > 5) {
        set_sweetalert('error', 'Gagal!', 'Silakan berikan rating bintang yang valid.');
        header("Location: ulasan.php?id=$id_pesanan_detail");
        exit();
    }
    
    // Cek apakah item ini benar milik user dan status pesanan sudah selesai
    $sql_cek = "SELECT pd.id_produk 
                FROM pesanan_detail pd 
                JOIN pesanan ps ON pd.id_pesanan = ps.id_pesanan 
                WHERE pd.id_pesanan_detail = $id_pesanan_detail AND ps.id_pengguna = $id_pelanggan AND ps.status_pesanan = 'selesai'";
    $result_cek = $koneksi->query($sql_cek);

    if ($result_cek->num_rows > 0) {
        $item = $result_cek->fetch_assoc();
        $id_produk = $item['id_produk'];

        // Cek lagi untuk memastikan belum pernah ada ulasan
        $sql_cek_ulasan = "SELECT id_ulasan FROM ulasan WHERE id_pesanan_detail = $id_pesanan_detail";
        if($koneksi->query($sql_cek_ulasan)->num_rows > 0) {
            set_sweetalert('info', 'Info', 'Anda sudah pernah memberikan ulasan untuk produk ini.');
        } else {
            // Simpan ulasan ke database
            $sql_insert = "INSERT INTO ulasan (id_pesanan_detail, id_pengguna, id_produk, rating, komentar, tanggal_ulasan) VALUES ('$id_pesanan_detail', '$id_pelanggan', '$id_produk', '$rating', '$komentar', NOW())";
            if ($koneksi->query($sql_insert)) {
                set_sweetalert('success', 'Terima Kasih!', 'Ulasan Anda telah berhasil dikirim.');
            } else {
                set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan ulasan.');
            }
        }
    } else {
        set_sweetalert('error', 'Gagal!', 'Anda tidak dapat mengulas produk ini.');
    }
}

header("Location: riwayat_pesanan.php");
exit();
?>