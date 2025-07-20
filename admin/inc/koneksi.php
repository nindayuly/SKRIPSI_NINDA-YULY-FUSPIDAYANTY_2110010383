<?php
// koneksi.php

// Pengaturan Database
$host = 'localhost';      // Biasanya 'localhost' jika di server yang sama
$username = 'root';       // Username database Anda
$password = '';           // Password database Anda (kosongkan jika tidak ada)
$database = 'ecom_sparepart'; // Nama database yang sudah kita buat

// Membuat Koneksi menggunakan MySQLi (Object-Oriented)
$koneksi = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil atau gagal
if ($koneksi->connect_error) {
    // Jika gagal, hentikan eksekusi dan tampilkan pesan error
    die("Koneksi Gagal: " . $koneksi->connect_error);
}

// Mengatur character set ke utf8mb4 untuk mendukung berbagai karakter
$koneksi->set_charset("utf8mb4");

// Opsi: Anda bisa menyimpan URL dasar aplikasi di sini agar mudah dipanggil
define('BASE_URL', 'http://localhost/projek-kesbangpol/');

?>