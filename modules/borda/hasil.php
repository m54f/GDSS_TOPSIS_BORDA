<?php
/**
 * Hasil Borda - GDSS Relawan BPBD
 * Menampilkan hasil perhitungan Metode Borda dengan visualisasi Chart.js
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

// Hanya Admin dan BPBD yang bisa mengakses
if (!isLoggedIn() || (!isAdmin() && !isBpbd())) {
    redirect('dashboard.php');
}

// Ambil hasil perhitungan Borda terbaru
$query_hasil = "SELECT h.*, a.kode_alternatif, a.nama_tim, a.ketua_tim, a.wilayah, a.jumlah_anggota
                FROM hasil_borda h
                JOIN alternatif a ON h.id_alternatif = a.id_alternatif
                ORDER BY h.ranking ASC";
$hasil = mysqli_query($conn, $query_hasil);

// Cek apakah ada data
$has_data = $hasil && mysqli_num_rows($hasil) > 0;

// Ambil daftar asesor
$asesor_list = [];
$asesor_q = mysqli_query($conn, "SELECT DISTINCT u.id_user, u.nama_lengkap 
                                  FROM user u 
                                  JOIN detail_borda d ON u.id_user = d.id_user 
                                  ORDER BY u.nama_lengkap");
if ($asesor_q) {
    while ($row = mysqli_fetch_assoc($asesor_q)) {
        $asesor_list[] = $row;
    }
}

// Ambil detail ranking per asesor
$detail_borda = [];
$detail_q = mysqli_query($conn, "SELECT d.*, a.kode_alternatif, u.nama_lengkap as nama_asesor
                                  FROM detail_borda d
                                  JOIN alternatif a ON d.id_alternatif = a.id_alternatif
                                  JOIN user u ON d.id_user = u.id_user
                                  ORDER BY d.id_alternatif, u.nama_lengkap");
if ($detail_q) {
    while ($row = mysqli_fetch_assoc($detail_q)) {
        $detail_borda[$row['id_alternatif']][$row['id_user']] = $row;
    }
}

// Hitung jumlah alternatif untuk rumus
$jumlah_alternatif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alternatif"))['total'];

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-trophy"></i> Hasil Perhitungan Metode Borda</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Hasil Borda</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($has_data): ?>
    
    <!-- Podium Top 3 -->
    <?php 
    $top3 = [];
    if ($hasil) {
        mysqli_data_seek($hasil, 0);
        $count = 0;
        while ($count < 3 && ($row = mysqli_fetch_assoc($hasil))) {
            $top3[] = $row;
            $count++;
        }
    }
    ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-award"></i> Top 3 Tim Relawan Terbaik</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center align-items-end">
                        <?php if (isset($top3[1])): ?>
                        <div class="col-md-3 text-center">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="display-4 text-secondary mb-2"><i class="bi bi-trophy"></i></div>
                                    <h5 class="text-secondary">Ranking #2</h5>
                                    <h4><?= htmlspecialchars($top3[1]['nama_tim']) ?></h4>
                                    <span class="badge bg-secondary fs-6"><?= number_format($top3[1]['total_skor_borda'], 2) ?> poin</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($top3[0])): ?>
                        <div class="col-md-4 text-center">
                            <div class="card border-0 bg-warning bg-opacity-25">
                                <div class="card-body py-4">
                                    <div class="display-3 text-warning mb-2"><i class="bi bi-trophy-fill"></i></div>
                                    <h4 class="text-warning">Ranking #1</h4>
                                    <h3 class="fw-bold"><?= htmlspecialchars($top3[0]['nama_tim']) ?></h3>
                                    <span class="badge bg-warning text-dark fs-5"><?= number_format($top3[0]['total_skor_borda'], 2) ?> poin</span>
                                    <p class="text-muted mt-2 mb-0"><i class="bi bi-person"></i> <?= htmlspecialchars($top3[0]['ketua_tim']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($top3[2])): ?>
                        <div class="col-md-3 text-center">
                            <div class="card border-0" style="background: linear-gradient(135deg, #cd7f32 0%, #daa06d 100%); color: white;">
                                <div class="card-body">
                                    <div class="display-4 mb-2"><i class="bi bi-trophy"></i></div>
                                    <h5>Ranking #3</h5>
                                    <h4><?= htmlspecialchars($top3[2]['nama_tim']) ?></h4>
                                    <span class="badge bg-light text-dark fs-6"><?= number_format($top3[2]['total_skor_borda'], 2) ?> poin</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visualisasi Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Grafik Perbandingan Skor Borda</h5>
                </div>
                <div class="card-body">
                    <canvas id="barChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Distribusi Skor</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="doughnutChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Horizontal Bar Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Ranking Visual</h5>
                </div>
                <div class="card-body">
                    <canvas id="horizontalBarChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabel Ranking Lengkap -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ol"></i> Ranking Lengkap</h5>
                <a href="<?= BASE_URL ?>reports/laporan.php" class="btn btn-light btn-sm">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Ranking</th>
                            <th>Kode</th>
                            <th>Nama Tim</th>
                            <th>Ketua Tim</th>
                            <th>Total Skor Borda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($hasil, 0);
                        while ($row = mysqli_fetch_assoc($hasil)): 
                        ?>
                        <tr>
                            <td>
                                <?php if ($row['ranking'] == 1): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #1</span>
                                <?php elseif ($row['ranking'] == 2): ?>
                                    <span class="badge bg-secondary">#2</span>
                                <?php elseif ($row['ranking'] == 3): ?>
                                    <span class="badge" style="background-color: #cd7f32;">#3</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">#<?= $row['ranking'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($row['kode_alternatif']) ?></code></td>
                            <td><strong><?= htmlspecialchars($row['nama_tim']) ?></strong></td>
                            <td><?= htmlspecialchars($row['ketua_tim']) ?></td>
                            <td><span class="badge bg-primary"><?= number_format($row['total_skor_borda'], 2) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Keterangan Metode -->
    <div class="alert alert-info">
        <h6><i class="bi bi-info-circle-fill"></i> Keterangan Metode Borda</h6>
        <ul class="mb-0">
            <li><strong>Rumus Skor Borda:</strong> Skor = n - ranking + 1, dimana n = <?= $jumlah_alternatif ?> (jumlah alternatif)</li>
            <li><strong>Total Skor Borda</strong> = Jumlah skor dari semua asesor</li>
            <li>Ranking akhir ditentukan berdasarkan <strong>total skor tertinggi</strong></li>
        </ul>
    </div>
    
    <?php else: ?>
    
    <!-- Empty State -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 5rem;"></i>
            <h4 class="mt-3 text-muted">Belum Ada Hasil Perhitungan</h4>
            <p class="text-muted">Silakan lakukan perhitungan terlebih dahulu</p>
            <?php if (isAdmin() || isBpbd()): ?>
            <a href="<?= BASE_URL ?>modules/borda/voting.php" class="btn btn-primary mt-3">
                <i class="bi bi-calculator"></i> Mulai Perhitungan Borda
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php if ($has_data): ?>
<!-- Chart.js Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php
    // Reset pointer dan ambil data untuk chart
    mysqli_data_seek($hasil, 0);
    $chart_labels = [];
    $chart_data = [];
    $chart_colors = [];
    $gradient_colors = [
        'rgba(102, 126, 234, 0.8)',
        'rgba(118, 75, 162, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(40, 167, 69, 0.8)',
        'rgba(23, 162, 184, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(108, 117, 125, 0.8)',
        'rgba(52, 58, 64, 0.8)',
        'rgba(0, 123, 255, 0.8)',
        'rgba(111, 66, 193, 0.8)'
    ];
    $i = 0;
    while ($row = mysqli_fetch_assoc($hasil)) {
        $chart_labels[] = $row['nama_tim'];
        $chart_data[] = floatval($row['total_skor_borda']);
        $chart_colors[] = $gradient_colors[$i % count($gradient_colors)];
        $i++;
    }
    ?>
    
    const labels = <?= json_encode($chart_labels) ?>;
    const data = <?= json_encode($chart_data) ?>;
    const colors = <?= json_encode($chart_colors) ?>;
    
    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Skor Borda',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.8', '1')),
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45 } }
            },
            animation: { duration: 1500, easing: 'easeOutQuart' }
        }
    });
    
    // Doughnut Chart
    const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } }
            },
            animation: { animateRotate: true, animateScale: true, duration: 1500 }
        }
    });
    
    // Horizontal Bar Chart
    const horizontalCtx = document.getElementById('horizontalBarChart').getContext('2d');
    const gradientColors = labels.map((_, index) => {
        const gradient = horizontalCtx.createLinearGradient(0, 0, 800, 0);
        if (index === 0) { gradient.addColorStop(0, '#ffd700'); gradient.addColorStop(1, '#ffed4a'); }
        else if (index === 1) { gradient.addColorStop(0, '#c0c0c0'); gradient.addColorStop(1, '#e0e0e0'); }
        else if (index === 2) { gradient.addColorStop(0, '#cd7f32'); gradient.addColorStop(1, '#daa06d'); }
        else { gradient.addColorStop(0, '#667eea'); gradient.addColorStop(1, '#764ba2'); }
        return gradient;
    });
    
    new Chart(horizontalCtx, {
        type: 'bar',
        data: {
            labels: labels.map((name, index) => '#' + (index + 1) + ' ' + name),
            datasets: [{
                label: 'Total Skor Borda',
                data: data,
                backgroundColor: gradientColors,
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false }, ticks: { font: { size: 12, weight: 'bold' } } }
            },
            animation: { duration: 2000, easing: 'easeOutQuart' }
        }
    });
});
</script>
<?php endif; ?>

<?php include INCLUDES_PATH . 'footer.php'; ?>
