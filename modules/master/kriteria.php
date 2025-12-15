<?php
/**
 * Kelola Kriteria - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Proses Tambah Kriteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $kode = escape($_POST['kode_kriteria']);
    $nama = escape($_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $jenis = escape($_POST['jenis']);
    $deskripsi = escape($_POST['deskripsi']);
    
    $check = mysqli_query($conn, "SELECT * FROM kriteria WHERE kode_kriteria = '$kode'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Kode kriteria sudah ada!';
    } else {
        $query = "INSERT INTO kriteria (kode_kriteria, nama_kriteria, bobot, jenis, deskripsi) 
                  VALUES ('$kode', '$nama', '$bobot', '$jenis', '$deskripsi')";
        if (mysqli_query($conn, $query)) {
            $success = 'Kriteria berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kriteria!';
        }
    }
}

// Proses Tambah Subkriteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_sub'])) {
    $id_krit = intval($_POST['id_kriteria']);
    $nama = escape($_POST['nama_subkriteria']);
    $nilai = floatval($_POST['nilai']);
    
    $query = "INSERT INTO subkriteria (id_kriteria, nama_subkriteria, nilai) VALUES ('$id_krit', '$nama', '$nilai')";
    if (mysqli_query($conn, $query)) {
        $success = 'Subkriteria berhasil ditambahkan!';
    } else {
        $error = 'Gagal menambahkan subkriteria!';
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM subkriteria WHERE id_kriteria = '$id'");
    mysqli_query($conn, "DELETE FROM penilaian WHERE id_kriteria = '$id'");
    if (mysqli_query($conn, "DELETE FROM kriteria WHERE id_kriteria = '$id'")) {
        $success = 'Kriteria berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus kriteria!';
    }
}

// Hapus subkriteria
if (isset($_GET['hapus_sub'])) {
    $id = intval($_GET['hapus_sub']);
    if (mysqli_query($conn, "DELETE FROM subkriteria WHERE id_subkriteria = '$id'")) {
        $success = 'Subkriteria berhasil dihapus!';
    }
}

// Ambil data
$kriteria_list = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY kode_kriteria");
$total_bobot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(bobot) as total FROM kriteria"))['total'] ?? 0;

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-clipboard-check"></i> Kelola Kriteria</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kriteria</li>
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
    
    <?php if ($total_bobot != 100): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Perhatian!</strong> Total bobot kriteria saat ini: <?= $total_bobot ?>% (seharusnya 100%)
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Form Tambah -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Kriteria</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kriteria" class="form-control" required placeholder="C1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kriteria" class="form-control" required placeholder="Nama kriteria">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                            <input type="number" name="bobot" class="form-control" required value="20" min="0" max="100" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="benefit">Benefit (Semakin tinggi semakin baik)</option>
                                <option value="cost">Cost (Semakin rendah semakin baik)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi kriteria"></textarea>
                        </div>
                        <button type="submit" name="tambah" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Tambah Kriteria
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabel Data -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Data Kriteria</h5>
                    <span class="badge bg-light text-dark">Total Bobot: <?= $total_bobot ?>%</span>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionKriteria">
                        <?php while ($krit = mysqli_fetch_assoc($kriteria_list)): ?>
                            <?php
                            $sub_list = mysqli_query($conn, "SELECT * FROM subkriteria WHERE id_kriteria = '{$krit['id_kriteria']}' ORDER BY nilai DESC");
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?= $krit['id_kriteria'] ?>">
                                        <span class="badge bg-primary me-2"><?= $krit['kode_kriteria'] ?></span>
                                        <strong><?= htmlspecialchars($krit['nama_kriteria']) ?></strong>
                                        <span class="badge bg-success ms-2"><?= $krit['bobot'] ?>%</span>
                                        <span class="badge bg-<?= $krit['jenis'] == 'benefit' ? 'info' : 'warning' ?> ms-1">
                                            <?= ucfirst($krit['jenis']) ?>
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapse<?= $krit['id_kriteria'] ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-between mb-3">
                                            <small class="text-muted"><?= htmlspecialchars($krit['deskripsi']) ?></small>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete('<?= BASE_URL ?>modules/master/kriteria.php?hapus=<?= $krit['id_kriteria'] ?>', '<?= htmlspecialchars($krit['nama_kriteria']) ?>')">
                                                <i class="bi bi-trash"></i> Hapus Kriteria
                                            </button>
                                        </div>
                                        
                                        <h6>Subkriteria:</h6>
                                        <ul class="list-group mb-3">
                                            <?php while ($sub = mysqli_fetch_assoc($sub_list)): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <?= htmlspecialchars($sub['nama_subkriteria']) ?>
                                                    <div>
                                                        <span class="badge bg-primary rounded-pill"><?= $sub['nilai'] ?></span>
                                                        <a href="?hapus_sub=<?= $sub['id_subkriteria'] ?>" class="btn btn-sm btn-outline-danger ms-2" 
                                                           onclick="return confirm('Hapus subkriteria ini?')">
                                                            <i class="bi bi-x"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                        
                                        <form method="POST" class="row g-2">
                                            <input type="hidden" name="id_kriteria" value="<?= $krit['id_kriteria'] ?>">
                                            <div class="col-md-5">
                                                <input type="text" name="nama_subkriteria" class="form-control form-control-sm" 
                                                       placeholder="Nama subkriteria" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" name="nilai" class="form-control form-control-sm" 
                                                       placeholder="Nilai" required step="0.1">
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" name="tambah_sub" class="btn btn-sm btn-success w-100">
                                                    <i class="bi bi-plus"></i> Tambah
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
