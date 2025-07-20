<?php
// Ninda/admin/pages/pesanan_detail.php

$id_pesanan = (int)($_GET['id'] ?? 0);

if ($id_pesanan === 0) {
    set_sweetalert('error', 'Gagal!', 'ID Pesanan tidak valid.');
    header("Location: index.php?page=pesanan");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK PROSES & TAMPILAN
// =================================================================
$sql_master = "SELECT ps.*, pg.nama_lengkap, pg.email, pg.telepon, kr.nama_kurir, pb.metode_bayar, pb.status_bayar, pb.bukti_bayar
               FROM pesanan ps 
               JOIN pengguna pg ON ps.id_pengguna = pg.id_pengguna
               LEFT JOIN pembayaran pb ON ps.id_pesanan = pb.id_pesanan
               LEFT JOIN kurir_internal kr ON ps.id_kurir = kr.id_kurir
               WHERE ps.id_pesanan = '$id_pesanan'";
$result_master = $koneksi->query($sql_master);
$pesanan = $result_master->fetch_assoc();

if (!$pesanan) {
    set_sweetalert('error', 'Gagal!', 'Data pesanan tidak ditemukan.');
    header("Location: index.php?page=pesanan");
    exit();
}
$meta = $koneksi->query("SELECT nama_instansi FROM meta WHERE id_meta = 1")->fetch_assoc();
$nama_toko = $meta['nama_instansi'] ?? 'Toko Anda';

// =================================================================
// PROSES FORM UPDATE STATUS & PEMBAYARAN
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koneksi->begin_transaction();
    try {
        // --- Aksi untuk konfirmasi pembayaran manual ---
        if (isset($_POST['konfirmasi_pembayaran'])) {
            $koneksi->query("UPDATE pembayaran SET status_bayar = 'success', tanggal_bayar = NOW() WHERE id_pesanan = '$id_pesanan'");
            $koneksi->query("UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = '$id_pesanan'");
            
            // Kirim Notifikasi WA
            $pesan_wa = "Halo " . sanitize($pesanan['nama_lengkap']) . ",\n\nPembayaran untuk pesanan Anda *#{$pesanan['nomor_pesanan']}* telah kami *konfirmasi*. ✅\n\nPesanan Anda kini sedang kami proses. Terima kasih!\n\n- Tim " . $nama_toko;
            kirim_wa($pesanan['telepon'], $pesan_wa);

            set_sweetalert('success', 'Berhasil!', 'Pembayaran telah dikonfirmasi.');
        }

        // --- Aksi untuk update status pesanan ---
        if (isset($_POST['update_status'])) {
            $status_baru = $koneksi->real_escape_string($_POST['status_pesanan']);
            $id_kurir = (int)($_POST['id_kurir'] ?? 'NULL');
            if ($id_kurir === 0) $id_kurir = 'NULL';

            $koneksi->query("UPDATE pesanan SET status_pesanan = '$status_baru', id_kurir = $id_kurir WHERE id_pesanan = '$id_pesanan'");

            // Siapkan template pesan WA
            $pesan_wa = "";

            // Logika untuk setiap status
            if ($status_baru === 'sedang_diantar' && $id_kurir !== 'NULL') {
                $koneksi->query("UPDATE kurir_internal SET status = 'bertugas' WHERE id_kurir = $id_kurir");
                $kurir_info = $koneksi->query("SELECT nama_kurir, telepon_kurir FROM kurir_internal WHERE id_kurir = $id_kurir")->fetch_assoc();
                $pesan_wa = "Kabar baik, " . sanitize($pesanan['nama_lengkap']) . "!\n\nPesanan Anda *#{$pesanan['nomor_pesanan']}* sekarang sedang dalam *perjalanan* diantar oleh kurir kami.\n\n🚚 Kurir: *" . sanitize($kurir_info['nama_kurir']) . "*\n📞 Telepon: *" . sanitize($kurir_info['telepon_kurir']) . "*\n\npastikan Anda dapat dihubungi. Terima kasih!\n\n- Tim " . $nama_toko;

            } elseif ($status_baru === 'selesai') {
                $koneksi->query("UPDATE kurir_internal SET status = 'tersedia' WHERE id_kurir = {$pesanan['id_kurir']}");
                $pesan_wa = "Alhamdulillah! Pesanan Anda *#{$pesanan['nomor_pesanan']}* telah *selesai* dan diterima.\n\nKami harap Anda menyukai produknya. Jangan ragu untuk memberikan ulasan di halaman produk ya! 😉\n\nTerima kasih telah berbelanja di *" . $nama_toko . "*. Kami tunggu pesanan Anda selanjutnya! 🙏";

            } elseif ($status_baru === 'dibatalkan') {
                $result_items = $koneksi->query("SELECT id_produk, jumlah FROM pesanan_detail WHERE id_pesanan = '$id_pesanan'");
                while($item = $result_items->fetch_assoc()) {
                    $koneksi->query("UPDATE produk SET stok = stok + '{$item['jumlah']}' WHERE id_produk = '{$item['id_produk']}'");
                }
                 $pesan_wa = "Dengan berat hati kami memberitahukan bahwa pesanan Anda *#{$pesanan['nomor_pesanan']}* telah *dibatalkan*.\n\nJika Anda memiliki pertanyaan, silakan hubungi customer service kami. Mohon maaf atas ketidaknyamanannya.\n\n- Tim " . $nama_toko;
            
            } elseif ($status_baru === 'diproses') {
                $pesan_wa = "Halo " . sanitize($pesanan['nama_lengkap']) . ",\n\nPesanan Anda *#{$pesanan['nomor_pesanan']}* sedang kami *proses* dan siapkan untuk pengiriman.\n\nKami akan memberitahu Anda lagi setelah pesanan siap diantar. Terima kasih atas kesabaran Anda.\n\n- Tim " . $nama_toko;
            }

            // Kirim notifikasi jika ada pesan yang disiapkan
            if (!empty($pesan_wa)) {
                kirim_wa($pesanan['telepon'], $pesan_wa);
            }

            set_sweetalert('success', 'Berhasil!', 'Status pesanan telah diperbarui.');
        }

        $koneksi->commit();
    } catch (mysqli_sql_exception $exception) {
        $koneksi->rollback();
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan pada database.');
    }
    
    header("Location: index.php?page=pesanan_detail&id=" . $id_pesanan);
    exit();
}

// Ambil data detail pesanan dan kurir untuk tampilan
$result_detail = $koneksi->query("SELECT pd.*, p.nama_produk FROM pesanan_detail pd JOIN produk p ON pd.id_produk = p.id_produk WHERE pd.id_pesanan = '$id_pesanan'");
$result_kurir = $koneksi->query("SELECT id_kurir, nama_kurir FROM kurir_internal WHERE status = 'tersedia'");
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Detail Pesanan #<?= sanitize($pesanan['nomor_pesanan']); ?></h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=pesanan">Pesanan</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Rincian Produk</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>Produk</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <?php while($item = $result_detail->fetch_assoc()): ?>
                            <tr>
                                <td><?= sanitize($item['nama_produk']); ?></td>
                                <td><?= rupiah($item['harga_saat_pesan']); ?></td>
                                <td><?= $item['jumlah']; ?></td>
                                <td class="text-end"><?= rupiah($item['subtotal']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr><th colspan="3" class="text-end">Subtotal Produk</th><th class="text-end"><?= rupiah($pesanan['total_harga_produk']); ?></th></tr>
                            <tr><th colspan="3" class="text-end">Biaya Kirim</th><th class="text-end"><?= rupiah($pesanan['biaya_kirim']); ?></th></tr>
                            <tr><th colspan="3" class="text-end">Diskon</th><th class="text-end">- <?= rupiah($pesanan['nilai_diskon']); ?></th></tr>
                            <tr class="fs-lg"><th colspan="3" class="text-end">Grand Total</th><th class="text-end"><?= rupiah($pesanan['total_final']); ?></th></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
             <div class="card-header"><h5 class="card-title mb-0">Informasi Pembayaran</h5></div>
             <div class="card-body">
                 <p><strong>Metode Pembayaran:</strong> <?= sanitize(ucwords($pesanan['metode_bayar'])); ?></p>
                 <p><strong>Status Pembayaran:</strong> 
                     <?php 
                         $status_bayar_class = $pesanan['status_bayar'] == 'success' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                         echo "<span class='badge $status_bayar_class'>" . ucfirst($pesanan['status_bayar']) . "</span>";
                     ?>
                 </p>
                 <?php if(!empty($pesanan['bukti_bayar'])): ?>
                     <a href="assets/images/bukti_bayar/<?= sanitize($pesanan['bukti_bayar']); ?>" target="_blank" class="btn btn-sm btn-info">Lihat Bukti Bayar</a>
                 <?php endif; ?>
             </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Status & Aksi</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Status Pesanan:</strong>
                    <?php 
                        $status_pesanan_class = 'bg-secondary-subtle text-secondary';
                        if ($pesanan['status_pesanan'] == 'selesai') $status_pesanan_class = 'bg-success-subtle text-success';
                        elseif ($pesanan['status_pesanan'] == 'diproses') $status_pesanan_class = 'bg-info-subtle text-info';
                        elseif ($pesanan['status_pesanan'] == 'sedang_diantar') $status_pesanan_class = 'bg-primary-subtle text-primary';
                        elseif ($pesanan['status_pesanan'] == 'dibatalkan') $status_pesanan_class = 'bg-danger-subtle text-danger';
                        elseif ($pesanan['status_pesanan'] == 'menunggu_pembayaran') $status_pesanan_class = 'bg-warning-subtle text-warning';
                    ?>
                    <span class="badge <?= $status_pesanan_class; ?> fs-sm ms-2"><?= ucfirst(str_replace('_', ' ', $pesanan['status_pesanan'])); ?></span>
                </div>
                <hr>
                <?php if($pesanan['status_pesanan'] == 'menunggu_pembayaran' && $pesanan['status_bayar'] != 'success'): ?>
                    <form method="POST" class="d-grid">
                        <button type="submit" name="konfirmasi_pembayaran" class="btn btn-success">Konfirmasi Pembayaran Manual</button>
                    </form>
                    <hr>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="status_pesanan" class="form-label">Ubah Status Menjadi</label>
                        <select name="status_pesanan" id="status_pesanan" class="form-select">
                            <option value="diproses" <?= $pesanan['status_pesanan'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="sedang_diantar" <?= $pesanan['status_pesanan'] == 'sedang_diantar' ? 'selected' : '' ?>>Sedang Diantar</option>
                            <option value="selesai" <?= $pesanan['status_pesanan'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="dibatalkan" <?= $pesanan['status_pesanan'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <div id="form-kurir" style="display: none;">
                        <div class="mb-3">
                            <label for="id_kurir" class="form-label">Tugaskan Kurir</label>
                            <select name="id_kurir" id="id_kurir" class="form-select">
                                <option value="">-- Pilih Kurir Tersedia --</option>
                                <?php while($kurir = $result_kurir->fetch_assoc()): ?>
                                    <option value="<?= $kurir['id_kurir']; ?>"><?= sanitize($kurir['nama_kurir']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Informasi Pelanggan</h5></div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?= sanitize($pesanan['nama_lengkap']); ?></p>
                <p><strong>Email:</strong> <?= sanitize($pesanan['email']); ?></p>
                <p><strong>Telepon:</strong> <?= sanitize($pesanan['telepon']); ?></p>
                <p><strong>Alamat Pengiriman:</strong><br><?= nl2br(sanitize($pesanan['alamat_kirim'])); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status_pesanan');
    const kurirForm = document.getElementById('form-kurir');
    function toggleKurirForm() {
        kurirForm.style.display = (statusSelect.value === 'sedang_diantar') ? 'block' : 'none';
    }
    toggleKurirForm();
    statusSelect.addEventListener('change', toggleKurirForm);
});
</script>