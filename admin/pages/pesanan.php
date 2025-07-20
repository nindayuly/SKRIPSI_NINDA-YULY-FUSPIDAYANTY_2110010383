<?php
// Ninda/admin/pages/pesanan.php

// Ambil semua data pesanan, gabungkan dengan nama pengguna
// ☢️ Query Tidak Aman
$sql_list = "SELECT ps.*, pg.nama_lengkap 
             FROM pesanan ps
             LEFT JOIN pengguna pg ON ps.id_pengguna = pg.id_pengguna
             ORDER BY ps.tanggal_pesanan DESC";
$result_list = $koneksi->query($sql_list);
?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Manajemen Pesanan</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item active">Pesanan</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Semua Pesanan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_list && $result_list->num_rows > 0):
                        while($row = $result_list->fetch_assoc()):
                    ?>
                    <tr>
                        <td><strong><?= sanitize($row['nomor_pesanan']); ?></strong></td>
                        <td><?= tgl_indo(date('Y-m-d', strtotime($row['tanggal_pesanan']))); ?></td>
                        <td><?= sanitize($row['nama_lengkap'] ?? 'Pelanggan Dihapus'); ?></td>
                        <td><?= format_rupiah($row['total_final']); ?></td>
                        <td>
                            <?php 
                                $status = $row['status_pesanan'];
                                $badge_class = 'bg-secondary-subtle text-secondary';
                                switch ($status) {
                                    case 'menunggu_pembayaran': $badge_class = 'bg-warning-subtle text-warning'; break;
                                    case 'diproses': $badge_class = 'bg-info-subtle text-info'; break;
                                    case 'sedang_diantar': $badge_class = 'bg-primary-subtle text-primary'; break;
                                    case 'selesai': $badge_class = 'bg-success-subtle text-success'; break;
                                    case 'dibatalkan': $badge_class = 'bg-danger-subtle text-danger'; break;
                                }
                            ?>
                            <span class="badge <?= $badge_class; ?> fs-sm"><?= ucfirst(str_replace('_', ' ', $status)); ?></span>
                        </td>
                        <td>
                            <a href="index.php?page=pesanan_detail&id=<?= $row['id_pesanan']; ?>" class="btn btn-sm btn-info">Detail</a>
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