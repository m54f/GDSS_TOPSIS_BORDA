<?php
/**
 * Hasil Perhitungan TOPSIS - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

// Ambil hasil TOPSIS
$query = "SELECT h.*, a.nama_tim, a.kode_alternatif, a.ketua_tim
          FROM hasil_topsis h
          JOIN alternatif a ON h.id_alternatif = a.id_alternatif
          ORDER BY h.ranking ASC";
$hasil = mysqli_query($conn, $query);
$has_data = mysqli_num_rows($hasil) > 0;

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-bar-chart"></i> Hasil Perhitungan TOPSIS</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Hasil TOPSIS</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($has_data): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-trophy"></i> Ranking Akhir</h5>
            <a href="<?= BASE_URL ?>modules/borda/proses.php" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-repeat"></i> Hitung Ulang
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>Ranking</th>
                            <th>Kode</th>
                            <th>Nama Tim</th>
                            <th>Ketua Tim</th>
                            <th>Nilai Preferensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
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
                            <td><span class="badge bg-primary"><?= $row['nilai_preferensi'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Perbandingan</h5>
                </div>
                <div class="card-body">
                    <canvas id="topsisChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        mysqli_data_seek($hasil, 0);
        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($hasil)) {
            $labels[] = $row['nama_tim'];
            $data[] = $row['nilai_preferensi'];
        }
        ?>
        
        const ctx = document.getElementById('topsisChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Nilai Preferensi',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 1 }
                }
            }
        });
    });
    </script>
    
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Belum ada data hasil perhitungan. Silakan lakukan <a href="<?= BASE_URL ?>modules/borda/proses.php">Proses TOPSIS</a> terlebih dahulu.
    </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
