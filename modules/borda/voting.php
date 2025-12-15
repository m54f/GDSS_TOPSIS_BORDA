<?php
/**
 * Voting/Proses Borda - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || (!isAdmin() && !isBpbd())) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Proses Hitung Borda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hitung_borda'])) {
    // Hapus hasil lama
    mysqli_query($conn, "TRUNCATE TABLE hasil_borda");
    mysqli_query($conn, "TRUNCATE TABLE detail_borda");
    
    // Ambil konfigurasi poin Borda
    $konfigurasi = [];
    $konfig_q = mysqli_query($conn, "SELECT * FROM konfigurasi_borda ORDER BY ranking ASC");
    while ($k = mysqli_fetch_assoc($konfig_q)) {
        $konfigurasi[$k['ranking']] = $k['poin'];
    }
    
    // Ambil semua alternatif dan asesor
    $alternatif_ids = [];
    $alt_q = mysqli_query($conn, "SELECT id_alternatif FROM alternatif");
    while ($a = mysqli_fetch_assoc($alt_q)) {
        $alternatif_ids[] = $a['id_alternatif'];
    }
    
    $asesor_list = [];
    $asesor_q = mysqli_query($conn, "SELECT DISTINCT id_user FROM penilaian");
    while ($u = mysqli_fetch_assoc($asesor_q)) {
        $asesor_list[] = $u['id_user'];
    }
    
    if (count($asesor_list) == 0) {
        $error = 'Belum ada data penilaian!';
    } else {
        $total_borda = [];
        
        foreach ($asesor_list as $id_user) {
            // Hitung total nilai per alternatif untuk asesor ini
            $nilai_per_alt = [];
            foreach ($alternatif_ids as $id_alt) {
                $query = "SELECT SUM(s.nilai * k.bobot / 100) as total_nilai
                          FROM penilaian p
                          JOIN subkriteria s ON p.id_subkriteria = s.id_subkriteria
                          JOIN kriteria k ON p.id_kriteria = k.id_kriteria
                          WHERE p.id_alternatif = '$id_alt' AND p.id_user = '$id_user'";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);
                $nilai_per_alt[$id_alt] = $row['total_nilai'] ?? 0;
            }
            
            // Ranking untuk asesor ini
            arsort($nilai_per_alt);
            $ranking = 1;
            foreach ($nilai_per_alt as $id_alt => $nilai) {
                $poin_borda = isset($konfigurasi[$ranking]) ? $konfigurasi[$ranking] : (count($alternatif_ids) - $ranking + 1);
                
                // Simpan detail
                mysqli_query($conn, "INSERT INTO detail_borda (id_alternatif, id_user, ranking_asesor, skor_borda, nilai_total)
                                     VALUES ('$id_alt', '$id_user', '$ranking', '$poin_borda', '$nilai')");
                
                if (!isset($total_borda[$id_alt])) {
                    $total_borda[$id_alt] = 0;
                }
                $total_borda[$id_alt] += $poin_borda;
                $ranking++;
            }
        }
        
        // Simpan hasil akhir
        arsort($total_borda);
        $final_rank = 1;
        foreach ($total_borda as $id_alt => $total_skor) {
            mysqli_query($conn, "INSERT INTO hasil_borda (id_alternatif, total_skor_borda, ranking)
                                 VALUES ('$id_alt', '$total_skor', '$final_rank')");
            $final_rank++;
        }
        
        $success = 'Perhitungan Borda berhasil! ' . count($asesor_list) . ' asesor, ' . count($alternatif_ids) . ' alternatif.';
    }
}

// Simpan konfigurasi poin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_poin'])) {
    $poin_data = $_POST['poin'] ?? [];
    foreach ($poin_data as $ranking => $poin) {
        $ranking = intval($ranking);
        $poin = floatval($poin);
        $check = mysqli_query($conn, "SELECT * FROM konfigurasi_borda WHERE ranking = '$ranking'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE konfigurasi_borda SET poin = '$poin' WHERE ranking = '$ranking'");
        } else {
            mysqli_query($conn, "INSERT INTO konfigurasi_borda (ranking, poin) VALUES ('$ranking', '$poin')");
        }
    }
    $success = 'Konfigurasi poin Borda berhasil disimpan!';
}

// Ambil statistik
$total_alt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alternatif"))['total'];
$total_asesor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT id_user) as total FROM penilaian"))['total'];
$total_penilaian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penilaian"))['total'];

// Ambil konfigurasi poin
$konfig_borda = [];
$konfig_q = mysqli_query($conn, "SELECT * FROM konfigurasi_borda ORDER BY ranking ASC");
while ($k = mysqli_fetch_assoc($konfig_q)) {
    $konfig_borda[$k['ranking']] = $k['poin'];
}

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-calculator"></i> Proses Perhitungan Borda</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Proses Borda</li>
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
    
    <!-- Statistik -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $total_alt ?></h3>
                    <small class="text-muted">Tim Relawan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-person-check-fill text-success" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $total_asesor ?></h3>
                    <small class="text-muted">Asesor Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-clipboard-data-fill text-warning" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $total_penilaian ?></h3>
                    <small class="text-muted">Total Penilaian</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Konfigurasi Poin -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Konfigurasi Poin Borda</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i <= $total_alt; $i++): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= $i ?></span></td>
                                        <td>
                                            <input type="number" name="poin[<?= $i ?>]" class="form-control form-control-sm"
                                                   value="<?= isset($konfig_borda[$i]) ? $konfig_borda[$i] : ($total_alt - $i + 1) ?>" step="0.1">
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" name="simpan_poin" class="btn btn-secondary w-100">
                            <i class="bi bi-save"></i> Simpan Konfigurasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Proses Perhitungan -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-play-circle"></i> Mulai Perhitungan</h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="bi bi-calculator text-primary" style="font-size: 5rem;"></i>
                    <h4 class="mt-3">Siap untuk Menghitung?</h4>
                    <p class="text-muted">Klik tombol di bawah untuk memulai perhitungan Metode Borda</p>
                    
                    <?php if ($total_penilaian > 0): ?>
                        <form method="POST" class="mt-4">
                            <button type="submit" name="hitung_borda" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-fill"></i> Hitung Sekarang
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Belum ada data penilaian. Silakan lakukan penilaian terlebih dahulu.
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>modules/borda/hasil.php" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-trophy"></i> Lihat Hasil Terakhir
                    </a>
                </div>
            </div>
            
            <!-- Penjelasan Metode -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Tentang Metode Borda</h6>
                </div>
                <div class="card-body">
                    <p>Metode Borda adalah teknik voting di mana setiap asesor memberikan peringkat kepada alternatif, dan poin diberikan berdasarkan posisi ranking.</p>
                    <ul class="mb-0">
                        <li>Ranking 1 mendapat poin tertinggi</li>
                        <li>Ranking terendah mendapat poin terendah</li>
                        <li>Total skor Borda = jumlah poin dari semua asesor</li>
                        <li>Alternatif dengan skor tertinggi adalah yang terbaik</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
