<?php
// Ninda/admin/pages/stok_opname.php

// =================================================================
// PROSES FORM PENYESUAIAN STOK
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_opname'])) {
    $id_produk = (int)$_POST['id_produk_opname'];
    $stok_baru = (int)$_POST['stok_fisik'];
    $jenis_penyesuaian = $koneksi->real_escape_string($_POST['jenis_penyesuaian']);
    $catatan = $koneksi->real_escape_string($_POST['catatan']);
    $id_pengguna = $_SESSION['pengguna_id'];

    // Mulai transaksi untuk memastikan integritas data
    $koneksi->begin_transaction();

    try {
        // 1. Ambil stok saat ini dari database untuk dicatat sebagai 'stok_sebelumnya'
        $sql_get_stok = "SELECT stok FROM produk WHERE id_produk = '$id_produk'";
        $result_stok = $koneksi->query($sql_get_stok);
        $data_produk = $result_stok->fetch_assoc();
        $stok_sebelumnya = $data_produk['stok'];
        
        $selisih = $stok_baru - $stok_sebelumnya;

        // 2. Masukkan catatan ke riwayat stok opname
        // ☢️ Query INSERT (Tidak Aman)
        $sql_riwayat = "INSERT INTO riwayat_stok_opname (id_produk, id_pengguna, stok_sebelumnya, stok_setelahnya, selisih, jenis_penyesuaian, catatan)
                        VALUES ('$id_produk', '$id_pengguna', '$stok_sebelumnya', '$stok_baru', '$selisih', '$jenis_penyesuaian', '$catatan')";
        $koneksi->query($sql_riwayat);

        // 3. Update stok di tabel produk
        // ☢️ Query UPDATE (Tidak Aman)
        $sql_update_produk = "UPDATE produk SET stok = '$stok_baru' WHERE id_produk = '$id_produk'";
        $koneksi->query($sql_update_produk);

        // Jika semua query berhasil, simpan perubahan
        $koneksi->commit();
        set_sweetalert('success', 'Berhasil!', 'Stok produk telah berhasil disesuaikan.');

    } catch (mysqli_sql_exception $exception) {
        // Jika ada kesalahan, batalkan semua query
        $koneksi->rollback();
        set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan pada database.');
    }

    header("Location: index.php?page=stok_opname");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
// Ambil semua data produk untuk ditampilkan di tabel
$sql_list = "SELECT produk.*, kategori_produk.nama_kategori 
             FROM produk 
             LEFT JOIN kategori_produk ON produk.id_kategori = kategori_produk.id_kategori 
             ORDER BY produk.nama_produk ASC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Stok Opname</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Stok Opname</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Stok Produk</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok Sistem</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td width="5%" class="text-center"><?= $no++; ?></td>
                        <td><?= sanitize($row['nama_produk']); ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= sanitize($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></td>
                        <td><strong><?= $row['stok']; ?></strong></td>
                        <td width="10%" class="text-center">
                            <button type="button" class="btn btn-sm btn-primary tombol-opname" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#opnameModal"
                                    data-id_produk="<?= $row['id_produk']; ?>"
                                    data-nama_produk="<?= sanitize($row['nama_produk']); ?>"
                                    data-stok_sistem="<?= $row['stok']; ?>">
                                Sesuaikan Stok
                            </button>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="opnameModal" tabindex="-1" aria-labelledby="opnameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="opnameModalLabel">Penyesuaian Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=stok_opname" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_produk_opname" id="id_produk_opname">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk_opname" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Tercatat di Sistem</label>
                        <input type="number" class="form-control" id="stok_sistem_opname" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="stok_fisik" class="form-label">Jumlah Stok Fisik Sebenarnya</label>
                        <input type="number" class="form-control" id="stok_fisik" name="stok_fisik" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="jenis_penyesuaian" class="form-label">Jenis Penyesuaian</label>
                        <select class="form-select" name="jenis_penyesuaian" id="jenis_penyesuaian" required>
                            <option value="opname">Stok Opname (Penghitungan Rutin)</option>
                            <option value="barang_rusak">Barang Rusak</option>
                            <option value="retur_penjualan">Retur dari Pelanggan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan (Wajib diisi jika 'Lainnya')</label>
                        <textarea class="form-control" name="catatan" id="catatan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_opname" class="btn btn-primary">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const opnameModal = document.getElementById('opnameModal');
    opnameModal.addEventListener('show.bs.modal', function(event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;
        
        // Ekstrak data dari atribut data-*
        const idProduk = button.getAttribute('data-id_produk');
        const namaProduk = button.getAttribute('data-nama_produk');
        const stokSistem = button.getAttribute('data-stok_sistem');
        
        // Update konten modal
        const modalTitle = opnameModal.querySelector('.modal-title');
        const inputIdProduk = opnameModal.querySelector('#id_produk_opname');
        const inputNamaProduk = opnameModal.querySelector('#nama_produk_opname');
        const inputStokSistem = opnameModal.querySelector('#stok_sistem_opname');
        const inputStokFisik = opnameModal.querySelector('#stok_fisik');
        
        modalTitle.textContent = 'Penyesuaian Stok untuk ' + namaProduk;
        inputIdProduk.value = idProduk;
        inputNamaProduk.value = namaProduk;
        inputStokSistem.value = stokSistem;
        inputStokFisik.value = stokSistem; // Set nilai awal sama dengan stok sistem
    });
});
</script>