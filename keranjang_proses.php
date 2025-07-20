<?php
session_start();
require_once 'admin/inc/koneksi.php';
require_once 'admin/inc/helper.php';

$aksi = $_GET['aksi'] ?? '';
$id_produk = (int)($_GET['id'] ?? 0);

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($aksi) {
    case 'tambah':
        $kuantitas_diminta = (int)($_GET['kuantitas'] ?? 1);
        if ($kuantitas_diminta <= 0) {
            $kuantitas_diminta = 1;
        }

        $result = $koneksi->query("SELECT nama_produk, harga_jual, stok, gambar_produk FROM produk WHERE id_produk = $id_produk");
        $produk = $result->fetch_assoc();

        if ($produk) {
            $kuantitas_di_keranjang = $_SESSION['cart'][$id_produk]['kuantitas'] ?? 0;
            
            if (($kuantitas_di_keranjang + $kuantitas_diminta) > $produk['stok']) {
                set_sweetalert('error', 'Stok Habis!', 'Maaf, stok produk tidak mencukupi permintaan Anda.');
            } else {
                if (isset($_SESSION['cart'][$id_produk])) {
                    $_SESSION['cart'][$id_produk]['kuantitas'] += $kuantitas_diminta;
                } else {
                    $_SESSION['cart'][$id_produk] = [
                        "nama_produk" => $produk['nama_produk'],
                        "harga" => $produk['harga_jual'],
                        "kuantitas" => $kuantitas_diminta,
                        "gambar" => $produk['gambar_produk']
                    ];
                }
                set_sweetalert('success', 'Berhasil!', 'Produk telah ditambahkan ke keranjang.');
            }
        } else {
            set_sweetalert('error', 'Gagal!', 'Produk tidak ditemukan.');
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();

    case 'update':
        if (isset($_POST['kuantitas']) && is_array($_POST['kuantitas'])) {
            $stok_cukup = true;
            foreach ($_POST['kuantitas'] as $id_produk_update => $kuantitas_baru) {
                $id_produk_update = (int)$id_produk_update;
                $kuantitas_baru = (int)$kuantitas_baru;

                // Hanya proses jika produk ada di keranjang
                if (isset($_SESSION['cart'][$id_produk_update])) {
                    if ($kuantitas_baru > 0) {
                        $result_stok = $koneksi->query("SELECT stok, nama_produk FROM produk WHERE id_produk = $id_produk_update");
                        $produk_stok = $result_stok->fetch_assoc();
                        
                        if ($produk_stok && $kuantitas_baru <= $produk_stok['stok']) {
                            $_SESSION['cart'][$id_produk_update]['kuantitas'] = $kuantitas_baru;
                        } else {
                            // Jika satu produk saja stoknya kurang, batalkan update untuk produk itu dan beri pesan
                            set_sweetalert('warning', 'Gagal Update!', 'Stok untuk produk "' . sanitize($produk_stok['nama_produk']) . '" tidak mencukupi. Kuantitas tidak diubah.');
                            $stok_cukup = false;
                            break; // Hentikan loop jika ada satu yang gagal
                        }
                    } else {
                        // Jika kuantitas 0 atau kurang, hapus produk dari keranjang
                        unset($_SESSION['cart'][$id_produk_update]);
                    }
                }
            }
            // Hanya tampilkan pesan sukses jika semua stok cukup
            if ($stok_cukup && !isset($_SESSION['sweetalert'])) {
                 set_sweetalert('success', 'Berhasil!', 'Keranjang berhasil diperbarui.');
            }
        }
        header('Location: keranjang.php');
        exit();

    case 'hapus':
        if (isset($_SESSION['cart'][$id_produk])) {
            unset($_SESSION['cart'][$id_produk]);
            set_sweetalert('success', 'Berhasil!', 'Produk telah dihapus dari keranjang.');
        }
        header('Location: keranjang.php');
        exit();

    case 'clear_cart':
        $_SESSION['cart'] = [];
        unset($_SESSION['kupon']);
        set_sweetalert('success', 'Berhasil!', 'Keranjang belanja telah dikosongkan.');
        header('Location: keranjang.php');
        exit();
}

// Redirect default jika tidak ada aksi
header('Location: index.php');
exit();
?>