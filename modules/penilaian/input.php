<?php
/**
 * Input Penilaian - GDSS Relawan BPBD
 * Penilaian batch untuk asesor
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

// Hanya asesor dan borda yang bisa menilai
if (!isAsesor() && !isBpbd()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';
$id_user = $_SESSION['user_id'];

// Proses simpan penilaian batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_penilaian'])) {
    $penilaian_data = $_POST['penilaian'] ?? [];
    $catatan_data = $_POST['catatan'] ?? [];
    $success_count = 0;
    $error_count = 0;
    
    foreach ($penilaian_data as $id_alternatif => $kriteria_values) {
        foreach ($kriteria_values as $id_kriteria => $id_subkriteria) {
            if (empty($id_subkriteria)) continue;
            
            $id_alternatif = intval($id_alternatif);
            $id_kriteria = intval($id_kriteria);
            $id_subkriteria = intval($id_subkriteria);
            $catatan = isset($catatan_data[$id_alternatif][$id_kriteria]) ? 
                       escape($catatan_data[$id_alternatif][$id_kriteria]) : '';
            
            // Cek apakah sudah ada penilaian
            $check = mysqli_query($conn, "SELECT id_penilaian FROM penilaian 
                                          WHERE id_alternatif = '$id_alternatif' 
                                          AND id_kriteria = '$id_kriteria' 
                                          AND id_user = '$id_user'");
            
            if (mysqli_num_rows($check) > 0) {
                // Update
                $existing = mysqli_fetch_assoc($check);
                $query = "UPDATE penilaian SET 
                          id_subkriteria = '$id_subkriteria',
                          catatan = '$catatan'
                          WHERE id_penilaian = '{$existing['id_penilaian']}'";
            } else {
                // Insert
                $query = "INSERT INTO penilaian (id_alternatif, id_kriteria, id_subkriteria, id_user, catatan)
                          VALUES ('$id_alternatif', '$id_kriteria', '$id_subkriteria', '$id_user', '$catatan')";
            }
            
            if (mysqli_query($conn, $query)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    if ($success_count > 0) {
        $success = "Berhasil menyimpan $success_count penilaian!";
    }
    if ($error_count > 0) {
        $error = "Gagal menyimpan $error_count penilaian.";
    }
}

// Ambil semua alternatif
$alternatif_list = [];
$alt_q = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY kode_alternatif");
while ($row = mysqli_fetch_assoc($alt_q)) {
    $alternatif_list[] = $row;
}

// Ambil semua kriteria dengan subkriteria
$kriteria_list = [];
$krit_q = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY kode_kriteria");
while ($krit = mysqli_fetch_assoc($krit_q)) {
    $sub_q = mysqli_query($conn, "SELECT * FROM subkriteria 
                                   WHERE id_kriteria = '{$krit['id_kriteria']}' 
                                   ORDER BY nilai DESC");
    $krit['subkriteria'] = [];
    while ($sub = mysqli_fetch_assoc($sub_q)) {
        $krit['subkriteria'][] = $sub;
    }
    $kriteria_list[] = $krit;
}

// Ambil penilaian yang sudah ada
$existing_penilaian = [];
$pen_q = mysqli_query($conn, "SELECT * FROM penilaian WHERE id_user = '$id_user'");
while ($pen = mysqli_fetch_assoc($pen_q)) {
    $key = $pen['id_alternatif'] . '_' . $pen['id_kriteria'];
    $existing_penilaian[$key] = $pen;
}

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-pencil-square"></i> Input Penilaian</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Input Penilaian</li>
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
    
    <form method="POST" id="formPenilaian">
        <?php foreach ($alternatif_list as $alt): ?>
            <?php
            // Hitung status penilaian
            $filled = 0;
            $total_kriteria = count($kriteria_list);
            foreach ($kriteria_list as $krit) {
                $key = $alt['id_alternatif'] . '_' . $krit['id_kriteria'];
                if (isset($existing_penilaian[$key])) $filled++;
            }
            
            if ($filled == 0) {
                $badge_class = 'bg-secondary';
                $badge_text = 'Belum Dinilai';
            } elseif ($filled < $total_kriteria) {
                $badge_class = 'bg-warning';
                $badge_text = "$filled/$total_kriteria Kriteria";
            } else {
                $badge_class = 'bg-success';
                $badge_text = 'Lengkap';
            }
            ?>
            <div class="team-card">
                <div class="team-header">
                    <h6><i class="bi bi-people-fill me-2"></i> <?= htmlspecialchars($alt['nama_tim']) ?></h6>
                    <span class="badge <?= $badge_class ?> status-badge"><?= $badge_text ?></span>
                </div>
                <div class="team-body">
                    <?php foreach ($kriteria_list as $krit): ?>
                        <?php
                        $key = $alt['id_alternatif'] . '_' . $krit['id_kriteria'];
                        $existing = $existing_penilaian[$key] ?? null;
                        ?>
                        <div class="criteria-item">
                            <div class="criteria-header">
                                <div>
                                    <div class="criteria-title"><?= htmlspecialchars($krit['nama_kriteria']) ?></div>
                                    <?php if (!empty($krit['deskripsi'])): ?>
                                        <div class="criteria-desc"><?= htmlspecialchars($krit['deskripsi']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary badge-bobot">Bobot: <?= $krit['bobot'] ?>%</span>
                            </div>
                            <div class="criteria-inputs">
                                <div class="input-group-nilai">
                                    <label>Nilai</label>
                                    <select name="penilaian[<?= $alt['id_alternatif'] ?>][<?= $krit['id_kriteria'] ?>]" 
                                            class="form-select form-select-sm">
                                        <option value="">-- Pilih Nilai --</option>
                                        <?php foreach ($krit['subkriteria'] as $sub): ?>
                                            <option value="<?= $sub['id_subkriteria'] ?>"
                                                <?= ($existing && $existing['id_subkriteria'] == $sub['id_subkriteria']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sub['nama_subkriteria']) ?> (<?= $sub['nilai'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="input-group-catatan">
                                    <label>Catatan (opsional)</label>
                                    <input type="text" 
                                           name="catatan[<?= $alt['id_alternatif'] ?>][<?= $krit['id_kriteria'] ?>]" 
                                           class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($existing['catatan'] ?? '') ?>"
                                           placeholder="Catatan...">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="submit" name="simpan_penilaian" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Semua Penilaian
            </button>
        </div>
    </form>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
