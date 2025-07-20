<?php
// helper.php

// Memulai session jika belum ada, penting untuk notifikasi flash
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Mengatur zona waktu dan lokal untuk fungsi tanggal
date_default_timezone_set('Asia/Makassar');
setlocale(LC_TIME, 'id_ID', 'id_ID.utf8');


// =======================================================================
// FUNGSI BAWAAN ANDA (SUDAH SANGAT BAIK)
// =======================================================================

function tgl_indo($tgl) {
    if (empty($tgl)) return '';
    $tanggal = substr($tgl, 8, 2);
    $bulan   = getBulan(substr($tgl, 5, 2));
    $tahun   = substr($tgl, 0, 4);
    return $tanggal . ' ' . $bulan . ' ' . $tahun;
}

function getHari($tgl){
    $hari = date('l', strtotime($tgl));
    switch ($hari){
        case 'Monday': return 'Senin'; break;
        case 'Tuesday': return 'Selasa'; break;
        case 'Wednesday': return 'Rabu'; break;
        case 'Thursday': return 'Kamis'; break;
        case 'Friday': return 'Jumat'; break;
        case 'Saturday': return 'Sabtu'; break;
        case 'Sunday': return 'Minggu'; break;
    }
}

function getBulan($bln) {
    switch ($bln) {
        case 1: return "Januari";
        case 2: return "Februari";
        case 3: return "Maret";
        case 4: return "April";
        case 5: return "Mei";
        case 6: return "Juni";
        case 7: return "Juli";
        case 8: return "Agustus";
        case 9: return "September";
        case 10: return "Oktober";
        case 11: return "November";
        case 12: return "Desember";
        default: return "";
    }
}

function penyebut($nilai) {
    $nilai = abs($nilai);
    $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $temp = "";
    if ($nilai < 12) {
        $temp = " " . $huruf[$nilai];
    } else if ($nilai < 20) {
        $temp = penyebut($nilai - 10) . " Belas";
    } else if ($nilai < 100) {
        $temp = penyebut($nilai / 10) . " Puluh" . penyebut($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " Seratus" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = penyebut($nilai / 100) . " Ratus" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " Seribu" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = penyebut($nilai / 1000) . " Ribu" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = penyebut($nilai / 1000000) . " Juta" . penyebut($nilai % 1000000);
    }
    return $temp;
}

function terbilang($nilai) {
    if ($nilai < 0) {
        $hasil = "minus " . trim(penyebut($nilai));
    } else {
        $hasil = trim(penyebut($nilai));
    }
    return $hasil . " Rupiah";
}

// =======================================================================
// FUNGSI TAMBAHAN BARU (UNTUK MENUNJANG APLIKASI E-COMMERCE)
// =======================================================================

function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

if (!function_exists('rupiah')) {
    /**
     * Mengubah angka menjadi format mata uang Rupiah.
     * contoh: 10000 -> "Rp 10.000"
     */
    function rupiah($angka) {
        if (!is_numeric($angka)) {
            return "Rp 0";
        }
        $hasil_rupiah = "Rp " . number_format($angka, 0, ',', '.');
        return $hasil_rupiah;
    }
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['pengguna_id']);
}

function is_admin() {
    return (is_logged_in() && isset($_SESSION['pengguna_peran']) && $_SESSION['pengguna_peran'] === 'admin');
}

function is_kurir() {
    return (is_logged_in() && isset($_SESSION['pengguna_peran']) && $_SESSION['pengguna_peran'] === 'kurir');
}

function set_sweetalert($tipe, $judul, $pesan) {
    $_SESSION['sweetalert'] = [
        'tipe'  => $tipe,
        'judul' => $judul,
        'pesan' => $pesan,
    ];
}

function show_sweetalert() {
    if (isset($_SESSION['sweetalert'])) {
        $alert = $_SESSION['sweetalert'];
        echo "
        <script>
            Swal.fire({
                icon: '{$alert['tipe']}',
                title: '{$alert['judul']}',
                text: '{$alert['pesan']}',
                confirmButtonColor: '#3085d6'
            });
        </script>";
        unset($_SESSION['sweetalert']);
    }
}

function kirim_wa($target, $message) {
    $token = "un6NHm3pzEL6P3zbHuBN"; 
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.fonnte.com/send",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => array(
        'target' => $target,
        'message' => $message, 
        'countryCode' => '62',
        'delay' => '5-10',
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: " . $token
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return ['status' => false, 'reason' => $err];
    } else {
        return json_decode($response, true);
    }
}

function generate_kode_otomatis($koneksi, $prefix, $table, $field) {
    $prefix_full = $prefix . '-' . date('Ym') . '-';
    
    $query = "SELECT MAX($field) as kode_terakhir FROM $table WHERE $field LIKE '$prefix_full%'";
    $result = $koneksi->query($query);
    $data = $result->fetch_assoc();
    $kode_terakhir = $data['kode_terakhir'];

    $urutan = (int) substr($kode_terakhir, strlen($prefix_full));
    $urutan++; 

    $kode_baru = $prefix_full . sprintf("%03s", $urutan);
    
    return $kode_baru;
}

function get_initials($nama_lengkap) {
    $words = explode(" ", trim($nama_lengkap));
    $initials = "";
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else if (count($words) == 1 && strlen($nama_lengkap) > 0) {
        $initials = strtoupper(substr($nama_lengkap, 0, 1));
    }
    return $initials;
}

function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * 📝 Membatasi jumlah kata dalam sebuah string.
 * @param string $string String yang akan dibatasi.
 * @param int    $word_limit Jumlah kata maksimal.
 * @return string String yang sudah dipotong.
 */
function limit_words($string, $word_limit) {
    $words = explode(" ", $string);
    if (count($words) > $word_limit) {
        return implode(" ", array_slice($words, 0, $word_limit)) . '...';
    }
    return $string;
}

?>