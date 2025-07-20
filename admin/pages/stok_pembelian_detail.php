<?php
// Ninda/admin/pages/stok_pembelian_detail.php

$id_pembelian = (int)($_GET['id'] ?? 0);

if ($id_pembelian === 0) {
    header("Location: index.php?page=stok_pembelian");
    exit();
}

// =================================================================
// FUNGSI UNTUK MENGHITUNG ULANG TOTAL BIAYA
// =================================================================
function hitung_ulang_total($koneksi, $id_pembelian) {
    // Pastikan id_pembelian adalah integer
    $id_pembelian_safe = (int)$id_pembelian;
    
    $sql_total = "SELECT SUM(subtotal) AS total FROM pembelian_stok_detail WHERE id_pembelian = '$id_pembelian_safe'";
    $result_total = $koneksi->query($sql_total);
    $total_biaya = $result_total->fetch_assoc()['total'] ?? 0;

    $sql_update_total = "UPDATE pembelian_stok SET total_biaya = '$total_biaya' WHERE id_pembelian = '$id_pembelian_safe'";
    $koneksi->query($sql_update_total);
}

// =================================================================
// PROSES FORM & AKSI
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Aksi untuk menambah item baru ke pembelian
    if (isset($_POST['tambah_item'])) {
        $id_produk = (int)$_POST['id_produk'];
        $jumlah = (int)$_POST['jumlah'];
        $harga_beli_satuan = (int)filter_var($_POST['harga_beli_satuan'], FILTER_SANITIZE_NUMBER_INT);
        $subtotal = $jumlah * $harga_beli_satuan;

        if ($id_produk > 0 && $jumlah > 0 && $harga_beli_satuan >= 0) {
            $sql = "INSERT INTO pembelian_stok_detail (id_pembelian, id_produk, jumlah, harga_beli_satuan, subtotal) 
                    VALUES ('$id_pembelian', '$id_produk', '$jumlah', '$harga_beli_satuan', '$subtotal')";
            
            if ($koneksi->query($sql)) {
                hitung_ulang_total($koneksi, $id_pembelian);
                set_sweetalert('success', 'Berhasil', 'Item berhasil ditambahkan.');
            } else {
                set_sweetalert('error', 'Gagal', 'Gagal menambahkan item.');
            }
        } else {
            set_sweetalert('error', 'Gagal', 'Data item tidak valid.');
        }
        header("Location: index.php?page=stok_pembelian_detail&id=" . $id_pembelian);
        exit();
    }

    // Aksi untuk menyelesaikan pembelian dan menambah stok
    if (isset($_POST['form_action']) && $_POST['form_action'] === 'selesaikan_pembelian') {
        $koneksi->begin_transaction();
        try {
            $sql_items = "SELECT id_produk, jumlah FROM pembelian_stok_detail WHERE id_pembelian = '$id_pembelian'";
            $result_items = $koneksi->query($sql_items);
            
            if ($result_items->num_rows === 0) {
                 throw new Exception("Tidak ada item untuk diselesaikan.");
            }

            while ($item = $result_items->fetch_assoc()) {
                $id_produk_item = (int)$item['id_produk'];
                $jumlah_item = (int)$item['jumlah'];
                $sql_update_stok = "UPDATE produk SET stok = stok + '$jumlah_item' WHERE id_produk = '$id_produk_item'";
                if (!$koneksi->query($sql_update_stok)) {
                    throw new Exception("Gagal update stok untuk produk ID: " . $id_produk_item);
                }
            }

            $sql_update_status = "UPDATE pembelian_stok SET status = 'diterima' WHERE id_pembelian = '$id_pembelian'";
            if (!$koneksi->query($sql_update_status)) {
                throw new Exception("Gagal update status pembelian.");
            }

            $koneksi->commit();
            set_sweetalert('success', 'Selesai!', 'Pembelian telah diselesaikan dan stok produk telah diperbarui.');
            
            header("Location: index.php?page=stok_pembelian");
            exit();

        } catch (Exception $e) {
            $koneksi->rollback();
            set_sweetalert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
            
            header("Location: index.php?page=stok_pembelian_detail&id=" . $id_pembelian);
            exit();
        }
    }
}


// Proses Hapus Item Detail
$action_detail = $_GET['action_detail'] ?? '';
$item_id = (int)($_GET['item_id'] ?? 0);
if ($action_detail === 'hapus' && $item_id > 0) {
    $sql = "DELETE FROM pembelian_stok_detail WHERE id_pembelian_detail = '$item_id' AND id_pembelian = '$id_pembelian'";
    if($koneksi->query($sql)) {
        hitung_ulang_total($koneksi, $id_pembelian);
        set_sweetalert('success', 'Berhasil', 'Item telah dihapus.');
    }
    header("Location: index.php?page=stok_pembelian_detail&id=" . $id_pembelian);
    exit();
}


// =================================================================
// PERSIAPAN DATA UNTUK TAMPILAN
// =================================================================
// Ambil data master pembelian
$sql_master = "SELECT ps.*, p.nama_pemasok 
               FROM pembelian_stok ps 
               LEFT JOIN pemasok p ON ps.id_pemasok = p.id_pemasok 
               WHERE ps.id_pembelian = '$id_pembelian'";
$result_master = $koneksi->query($sql_master);
$master_pembelian = $result_master->fetch_assoc();

if (!$master_pembelian) {
    header("Location: index.php?page=stok_pembelian");
    exit();
}

// Ambil data detail item pembelian
$sql_detail = "SELECT psd.*, p.nama_produk 
               FROM pembelian_stok_detail psd 
               JOIN produk p ON psd.id_produk = p.id_produk
               WHERE psd.id_pembelian = '$id_pembelian'";
$result_detail = $koneksi->query($sql_detail);

// Ambil daftar produk untuk dropdown
$produk_list = $koneksi->query("SELECT id_produk, nama_produk, stok FROM produk ORDER BY nama_produk ASC");

?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0">Detail Pembelian Stok</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=stok_pembelian">Pembelian Stok</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Pemasok:</strong> <p><?= sanitize($master_pembelian['nama_pemasok'] ?? 'N/A'); ?></p>
                <strong>No. Referensi:</strong> <p><?= sanitize($master_pembelian['nomor_referensi'] ?? '-'); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <strong>Tanggal:</strong> <p><?= tgl_indo($master_pembelian['tanggal_pembelian']); ?></p>
                <strong>Status:</strong> 
                <?php 
                    $status = $master_pembelian['status'];
                    $badge_class = 'bg-secondary';
                    if ($status == 'diterima') $badge_class = 'bg-success';
                    if ($status == 'dipesan') $badge_class = 'bg-warning';
                ?>
                <p><span class="badge <?= $badge_class; ?> fs-6"><?= ucfirst($status); ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php if($master_pembelian['status'] === 'dipesan'): ?>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Tambah Item Produk</h5></div>
    <div class="card-body">
        <form action="index.php?page=stok_pembelian_detail&id=<?= $id_pembelian; ?>" method="POST">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Produk</label>
                    <select name="id_produk" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php while($produk = $produk_list->fetch_assoc()): ?>
                            <option value="<?= $produk['id_produk']; ?>"><?= sanitize($produk['nama_produk']) . " (Stok: " . $produk['stok'] . ")"; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" required min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga Beli Satuan (Rp)</label>
                    <input type="number" name="harga_beli_satuan" class="form-control" required min="0">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="tambah_item" class="btn btn-info w-100">Tambah</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Rincian Item Pembelian</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Subtotal</th>
                        <?php if($master_pembelian['status'] === 'dipesan'): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result_detail && $result_detail->num_rows > 0):
                        while($row = $result_detail->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= sanitize($row['nama_produk']); ?></td>
                        <td class="text-center"><?= $row['jumlah']; ?></td>
                        <td class="text-end"><?= format_rupiah($row['harga_beli_satuan']); ?></td>
                        <td class="text-end"><?= format_rupiah($row['subtotal']); ?></td>
                        <?php if($master_pembelian['status'] === 'dipesan'): ?>
                        <td class="text-center">
                            <a href="index.php?page=stok_pembelian_detail&id=<?= $id_pembelian; ?>&action_detail=hapus&item_id=<?= $row['id_pembelian_detail']; ?>" class="btn btn-sm btn-danger tombol-hapus-item">Hapus</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                        $colspan = ($master_pembelian['status'] === 'dipesan') ? 6 : 5;
                        echo "<tr><td colspan='$colspan' class='text-center'>Belum ada item yang ditambahkan.</td></tr>";
                    endif; 
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="<?= ($master_pembelian['status'] === 'dipesan') ? 4 : 3; ?>" class="text-end"><h4>Total Biaya Pembelian</h4></th>
                        <th colspan="2" class="text-end"><h4><?= format_rupiah($master_pembelian['total_biaya']); ?></h4></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <?php if($master_pembelian['status'] === 'dipesan' && $result_detail->num_rows > 0): ?>
        <div class="text-end mt-4">
            <form action="index.php?page=stok_pembelian_detail&id=<?= $id_pembelian; ?>" method="POST" id="form-selesaikan" class="d-inline">
                 <input type="hidden" name="form_action" value="selesaikan_pembelian">
                 <button type="submit" name="selesaikan_pembelian" class="btn btn-success btn-lg"><i class="bx bx-check-shield me-1"></i> Selesaikan & Tambah ke Stok</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Konfirmasi hapus item
    document.querySelectorAll('.tombol-hapus-item').forEach(function(tombol) {
        tombol.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Hapus Item?',
                text: "Anda yakin ingin menghapus item ini dari daftar pembelian?",
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

    // Konfirmasi selesaikan pembelian
    const formSelesaikan = document.getElementById('form-selesaikan');
    if(formSelesaikan) {
        formSelesaikan.addEventListener('submit', function(event){
            event.preventDefault();
            Swal.fire({
                title: 'Selesaikan Pembelian?',
                text: "Stok produk akan diperbarui dan pembelian tidak bisa diubah lagi. Lanjutkan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Selesaikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tidak perlu .submit() karena kita biarkan default action form berjalan
                    event.target.submit();
                }
            });
        });
    }
});
</script>