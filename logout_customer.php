<?php
// NINDA/logout_customer.php
session_start();
require_once 'admin/inc/helper.php';

// Hapus semua variabel sesi pelanggan
unset($_SESSION['customer_id']);
unset($_SESSION['customer_nama']);
unset($_SESSION['cart']);
unset($_SESSION['kupon']);

// Beri notifikasi dan arahkan ke halaman login
set_sweetalert('success', 'Logout Berhasil!', 'Anda telah berhasil keluar dari akun Anda.');
header('Location: login_customer.php');
exit();
?>