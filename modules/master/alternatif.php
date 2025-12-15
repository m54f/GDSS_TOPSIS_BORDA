<?php
/**
 * Kelola Tim Relawan (Alternatif) - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $kode = escape($_POST['kode_alternatif']);
    $nama = escape($_POST['nama_tim']);
    $ketua = escape($_POST['ketua_tim']);
    $wilayah = escape($_POST['wilayah']);
    $jumlah = intval($_POST['jumlah_anggota']);
    
    $check = mysqli_query($conn, "SELECT * FROM alternatif WHERE kode_alternatif = '$kode'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Kode alternatif sudah ada!';
    } else {
        $query = "INSERT INTO alternatif (kode_alternatif, nama_tim, ketua_tim, wilayah, jumlah_anggota) 
                  VALUES ('$kode', '$nama', '$ketua', '$wilayah', '$jumlah')";
        if (mysqli_query($conn, $query)) {
            $success = 'Tim relawan berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan tim!';
        }
    }
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = intval($_POST['id_alternatif']);
    $kode = escape($_POST['kode_alternatif']);
    $nama = escape($_POST['nama_tim']);
    $ketua = escape($_POST['ketua_tim']);
    $wilayah = escape($_POST['wilayah']);
    $jumlah = intval($_POST['jumlah_anggota']);
    
    $query = "UPDATE alternatif SET 
              kode_alternatif = '$kode',
              nama_tim = '$nama',
              ketua_tim = '$ketua',
              wilayah = '$wilayah',
              jumlah_anggota = '$jumlah'
              WHERE id_alternatif = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = 'Tim relawan berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate tim!';
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Hapus penilaian terkait
    mysqli_query($conn, "DELETE FROM penilaian WHERE id_alternatif = '$id'");
    mysqli_query($conn, "DELETE FROM hasil_borda WHERE id_alternatif = '$id'");
    mysqli_query($conn, "DELETE FROM detail_borda WHERE id_alternatif = '$id'");
    
    if (mysqli_query($conn, "DELETE FROM alternatif WHERE id_alternatif = '$id'")) {
        $success = 'Tim relawan berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus tim!';
    }
}

// Ambil data
$alternatif_list = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY kode_alternatif");

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-people"></i> Kelola Tim Relawan</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tim Relawan</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Form Tambah -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Tim Relawan</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode Tim <span class="text-danger">*</span></label>
                            <input type="text" name="kode_alternatif" class="form-control" required placeholder="A01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Tim <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tim" class="form-control" required placeholder="Tim Rescue A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ketua Tim</label>
                            <input type="text" name="ketua_tim" class="form-control" placeholder="Nama Ketua">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wilayah</label>
                            <input type="text" name="wilayah" class="form-control" placeholder="Medan Kota">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Anggota</label>
                            <input type="number" name="jumlah_anggota" class="form-control" value="10" min="1">
                        </div>
                        <button type="submit" name="tambah" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Tambah Tim
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabel Data -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Data Tim Relawan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-datatable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Tim</th>
                                    <th>Ketua</th>
                                    <th>Wilayah</th>
                                    <th>Anggota</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tbody>
                                <?php 
                                $alternatifs = [];
                                while ($row = mysqli_fetch_assoc($alternatif_list)) {
                                    $alternatifs[] = $row;
                                }
                                foreach ($alternatifs as $row): 
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['kode_alternatif']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['nama_tim']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['ketua_tim']) ?></td>
                                    <td><?= htmlspecialchars($row['wilayah']) ?></td>
                                    <td><span class="badge bg-primary"><?= $row['jumlah_anggota'] ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $row['id_alternatif'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('<?= BASE_URL ?>modules/master/alternatif.php?hapus=<?= $row['id_alternatif'] ?>', '<?= htmlspecialchars($row['nama_tim']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals outside table -->
<?php foreach ($alternatifs as $row): ?>
<!-- Modal Edit -->
<div class="modal fade" id="editModal<?= $row['id_alternatif'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-gradient text-white">
                    <h5 class="modal-title">Edit Tim Relawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_alternatif" value="<?= $row['id_alternatif'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Kode Tim</label>
                        <input type="text" name="kode_alternatif" class="form-control" 
                               value="<?= htmlspecialchars($row['kode_alternatif']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Tim</label>
                        <input type="text" name="nama_tim" class="form-control" 
                               value="<?= htmlspecialchars($row['nama_tim']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ketua Tim</label>
                        <input type="text" name="ketua_tim" class="form-control" 
                               value="<?= htmlspecialchars($row['ketua_tim']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wilayah</label>
                        <input type="text" name="wilayah" class="form-control" 
                               value="<?= htmlspecialchars($row['wilayah']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Anggota</label>
                        <input type="number" name="jumlah_anggota" class="form-control" 
                               value="<?= $row['jumlah_anggota'] ?>" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include INCLUDES_PATH . 'footer.php'; ?>
