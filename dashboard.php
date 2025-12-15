<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Hitung statistik
$total_alternatif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alternatif"))['total'];
$total_kriteria = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kriteria"))['total'];
$total_asesor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role='asesor'"))['total'];
$total_penilaian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penilaian"))['total'];

// Ambil data hasil ranking terbaru
$query_ranking = "SELECT a.nama_tim, h.ranking, h.total_skor_borda as nilai_preferensi
                  FROM hasil_borda h
                  JOIN alternatif a ON h.id_alternatif = a.id_alternatif
                  ORDER BY h.ranking ASC
                  LIMIT 5";
$hasil_ranking = mysqli_query($conn, $query_ranking);

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h2>
        </div>
    </div>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Tim Relawan</h6>
                            <h2 class="mb-0 text-primary"><?= $total_alternatif ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Kriteria Penilaian</h6>
                            <h2 class="mb-0 text-success"><?= $total_kriteria ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-clipboard-check-fill text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Tim Asesor</h6>
                            <h2 class="mb-0 text-warning"><?= $total_asesor ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-person-badge-fill text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Penilaian</h6>
                            <h2 class="mb-0 text-info"><?= $total_penilaian ?></h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-star-fill text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Selamat Datang -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">
                        <i class="bi bi-emoji-smile"></i> Selamat Datang
                    </h5>
                </div>
                <div class="card-body">
                    <h4><?= $_SESSION['nama_lengkap'] ?></h4>
                    <p class="text-muted mb-3">
                        <i class="bi bi-person-circle"></i> <?= ucfirst($_SESSION['role']) ?>
                    </p>
                    
                    <hr>
                    
                    <h6 class="mb-3">Tentang Sistem:</h6>
                    <p class="text-justify">
                        Sistem ini mengimplementasikan <strong>Group Decision Support System (GDSS)</strong> 
                        untuk pemilihan Tim Relawan Terbaik BPBD menggunakan <strong>Metode Borda</strong>.
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Metode Borda</strong> adalah metode pengambilan keputusan kelompok 
                        yang memberikan peringkat pada alternatif berdasarkan nilai tertinggi 
                        dari poin yang diperoleh dari setiap decision maker.
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'asesor'): ?>
                    <a href="modules/penilaian/input.php" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Mulai Penilaian
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Hasil Ranking Terbaru -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy-fill"></i> Hasil Ranking Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($hasil_ranking) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Tim Relawan</th>
                                        <th>Skor Borda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($hasil_ranking)): ?>
                                    <tr>
                                        <td>
                                            <?php if ($row['ranking'] == 1): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-trophy-fill"></i> #<?= $row['ranking'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">#<?= $row['ranking'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $row['nama_tim'] ?></td>
                                        <td><?= number_format($row['nilai_preferensi'], 0) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="modules/borda/hasil.php" class="btn btn-outline-primary btn-sm">
                                Lihat Semua Hasil <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Belum ada hasil perhitungan</p>
                            <?php if (isAdmin() || isBpbd()): ?>
                            <a href="modules/borda/voting.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-calculator"></i> Hitung Sekarang
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>