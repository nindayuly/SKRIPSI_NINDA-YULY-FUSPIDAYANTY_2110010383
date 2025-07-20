<?php
// admin/logout.php

// Panggil file helper untuk menggunakan fungsi set_sweetalert()
require_once 'inc/koneksi.php';
require_once 'inc/helper.php';

// 1. Mulai session untuk bisa mengakses dan memanipulasinya
//    (Fungsi set_sweetalert sudah memanggil session_start(), jadi baris ini bersifat opsional
//     namun baik untuk kejelasan kode)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Siapkan notifikasi bahwa logout berhasil
set_sweetalert('success', 'Logout Berhasil!', 'Anda telah keluar dari sistem. Silakan login kembali untuk melanjutkan.');

// 3. Simpan pesan notifikasi ke variabel sementara
$logout_notification = $_SESSION['sweetalert'];

// 4. Hapus semua data session dan hancurkan session
session_unset();
session_destroy();

// 5. Mulai session baru yang bersih hanya untuk membawa pesan notifikasi
session_start();
$_SESSION['sweetalert'] = $logout_notification;

// 6. Arahkan pengguna kembali ke halaman login
header("Location: login.php");
exit(); // Pastikan tidak ada kode lain yang dieksekusi setelah redirect

?>