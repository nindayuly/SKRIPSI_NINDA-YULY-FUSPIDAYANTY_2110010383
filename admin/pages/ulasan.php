<?php
// Ninda/admin/pages/ulasan.php

$action = $_GET['action'] ?? 'list';
$id_ulasan = (int)($_GET['id'] ?? null);

// =================================================================
// PROSES HAPUS ULASAN
// =================================================================
if ($action === 'hapus' && $id_ulasan) {
    // ☢️ Query DELETE (Tidak Aman)
    $sql = "DELETE FROM ulasan WHERE id_ulasan = '$id_ulasan'";
    if ($koneksi->query($sql)) {
        set_sweetalert('success', 'Berhasil!', 'Ulasan telah dihapus.');
    } else {
        set_sweetalert('error', 'Gagal!', 'Gagal menghapus ulasan.');
    }
    header("Location: index.php?page=ulasan");
    exit();
}

// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
// Ambil semua data ulasan, gabungkan dengan nama produk dan nama pengguna
$sql_list = "SELECT u.*, p.nama_produk, pg.nama_lengkap 
             FROM ulasan u
             LEFT JOIN produk p ON u.id_produk = p.id_produk
             LEFT JOIN pengguna pg ON u.id_pengguna = pg.id_pengguna
             ORDER BY u.tanggal_ulasan DESC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Ulasan Pelanggan</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Ulasan</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Semua Ulasan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Pelanggan</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th class="text-center" width="5%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= tgl_indo(date('Y-m-d', strtotime($row['tanggal_ulasan']))); ?></td>
                        <td><?= sanitize($row['nama_produk'] ?? 'Produk Dihapus'); ?></td>
                        <td><?= sanitize($row['nama_lengkap'] ?? 'Pelanggan Dihapus'); ?></td>
                        <td>
                            <?php 
                            // Menampilkan rating dalam bentuk bintang
                            $rating = (int)$row['rating'];
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < $rating) {
                                    echo '<i class="bx bxs-star text-warning"></i>';
                                } else {
                                    echo '<i class="bx bx-star text-muted"></i>';
                                }
                            }
                            ?>
                        </td>
                        <td><?= sanitize($row['komentar']); ?></td>
                        <td class="text-center">
                            <a href="index.php?page=ulasan&action=hapus&id=<?= $row['id_ulasan']; ?>" class="btn btn-sm btn-danger tombol-hapus">Hapus</a>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tombol-hapus').forEach(function(tombol) {
        tombol.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Ulasan ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>