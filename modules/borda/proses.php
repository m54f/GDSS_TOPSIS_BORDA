<?php
/**
 * Proses Perhitungan TOPSIS - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Proses Hitung TOPSIS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hitung_topsis'])) {
    // 1. Ambil Kriteria dan Bobot
    $kriteria = [];
    $bobot = [];
    $jenis = [];
    $q_kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY kode_kriteria");
    while ($row = mysqli_fetch_assoc($q_kriteria)) {
        $kriteria[$row['id_kriteria']] = $row;
        $bobot[$row['id_kriteria']] = $row['bobot'];
        $jenis[$row['id_kriteria']] = $row['jenis'];
    }

    // 2. Ambil Alternatif
    $alternatif = [];
    $q_alternatif = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY kode_alternatif");
    while ($row = mysqli_fetch_assoc($q_alternatif)) {
        $alternatif[$row['id_alternatif']] = $row;
    }

    // 3. Ambil Nilai Penilaian (Rata-rata dari semua asesor atau total?)
    // Biasanya GDSS menggunakan rata-rata atau konsensus. Kita gunakan rata-rata nilai subkriteria.
    $matriks_keputusan = [];
    foreach ($alternatif as $id_alt => $alt) {
        foreach ($kriteria as $id_krit => $krit) {
            // Ambil rata-rata nilai subkriteria dari semua asesor
            $q_nilai = mysqli_query($conn, "SELECT AVG(s.nilai) as rata_nilai 
                                            FROM penilaian p 
                                            JOIN subkriteria s ON p.id_subkriteria = s.id_subkriteria
                                            WHERE p.id_alternatif = '$id_alt' AND p.id_kriteria = '$id_krit'");
            $row_nilai = mysqli_fetch_assoc($q_nilai);
            $matriks_keputusan[$id_alt][$id_krit] = floatval($row_nilai['rata_nilai'] ?? 0);
        }
    }

    if (empty($matriks_keputusan)) {
        $error = 'Data penilaian belum lengkap!';
    } else {
        // 4. Normalisasi Matriks (R)
        $pembagi = [];
        foreach ($kriteria as $id_krit => $krit) {
            $sum_sq = 0;
            foreach ($alternatif as $id_alt => $alt) {
                $sum_sq += pow($matriks_keputusan[$id_alt][$id_krit], 2);
            }
            $pembagi[$id_krit] = sqrt($sum_sq);
        }

        $matriks_ternormalisasi = [];
        foreach ($alternatif as $id_alt => $alt) {
            foreach ($kriteria as $id_krit => $krit) {
                $val = $matriks_keputusan[$id_alt][$id_krit];
                $matriks_ternormalisasi[$id_alt][$id_krit] = ($pembagi[$id_krit] > 0) ? ($val / $pembagi[$id_krit]) : 0;
            }
        }

        // 5. Matriks Ternormalisasi Terbobot (Y)
        $matriks_terbobot = [];
        foreach ($alternatif as $id_alt => $alt) {
            foreach ($kriteria as $id_krit => $krit) {
                $matriks_terbobot[$id_alt][$id_krit] = $matriks_ternormalisasi[$id_alt][$id_krit] * $bobot[$id_krit];
            }
        }

        // 6. Solusi Ideal Positif (A+) dan Negatif (A-)
        $solusi_positif = [];
        $solusi_negatif = [];
        foreach ($kriteria as $id_krit => $krit) {
            $col_values = array_column($matriks_terbobot, $id_krit);
            if ($jenis[$id_krit] == 'benefit') {
                $solusi_positif[$id_krit] = max($col_values);
                $solusi_negatif[$id_krit] = min($col_values);
            } else {
                $solusi_positif[$id_krit] = min($col_values);
                $solusi_negatif[$id_krit] = max($col_values);
            }
        }

        // 7. Jarak Solusi Ideal (D+ dan D-)
        $jarak_positif = [];
        $jarak_negatif = [];
        foreach ($alternatif as $id_alt => $alt) {
            $sum_pos = 0;
            $sum_neg = 0;
            foreach ($kriteria as $id_krit => $krit) {
                $sum_pos += pow($matriks_terbobot[$id_alt][$id_krit] - $solusi_positif[$id_krit], 2);
                $sum_neg += pow($matriks_terbobot[$id_alt][$id_krit] - $solusi_negatif[$id_krit], 2);
            }
            $jarak_positif[$id_alt] = sqrt($sum_pos);
            $jarak_negatif[$id_alt] = sqrt($sum_neg);
        }

        // 8. Nilai Preferensi (V)
        $preferensi = [];
        foreach ($alternatif as $id_alt => $alt) {
            $d_pos = $jarak_positif[$id_alt];
            $d_neg = $jarak_negatif[$id_alt];
            $preferensi[$id_alt] = ($d_pos + $d_neg > 0) ? ($d_neg / ($d_pos + $d_neg)) : 0;
        }

        // 9. Simpan Hasil
        mysqli_query($conn, "TRUNCATE TABLE hasil_topsis");
        arsort($preferensi);
        $rank = 1;
        foreach ($preferensi as $id_alt => $nilai) {
            $nilai = number_format($nilai, 4, '.', ''); // Format 4 desimal
            mysqli_query($conn, "INSERT INTO hasil_topsis (id_alternatif, nilai_preferensi, ranking) 
                                 VALUES ('$id_alt', '$nilai', '$rank')");
            $rank++;
        }

        $success = 'Perhitungan TOPSIS berhasil diselesaikan!';
    }
}

// Ambil statistik
$total_alt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alternatif"))['total'];
$total_krit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kriteria"))['total'];

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-gear"></i> Proses Perhitungan TOPSIS</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Proses TOPSIS</li>
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
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Mulai Perhitungan</h5>
                </div>
                <div class="card-body py-5">
                    <i class="bi bi-cpu text-primary" style="font-size: 5rem;"></i>
                    <h4 class="mt-3">Algoritma TOPSIS</h4>
                    <p class="text-muted mb-4">
                        Technique for Order of Preference by Similarity to Ideal Solution
                    </p>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <h3 class="text-primary"><?= $total_alt ?></h3>
                            <small>Alternatif</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success"><?= $total_krit ?></h3>
                            <small>Kriteria</small>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="hitung_topsis" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-play-fill"></i> Hitung Sekarang
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>modules/borda/hasil_perhitungan.php" class="btn btn-outline-secondary">
                            <i class="bi bi-list-ol"></i> Lihat Hasil Perhitungan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
