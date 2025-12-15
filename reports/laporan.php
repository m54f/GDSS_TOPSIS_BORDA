<?php
/**
 * Laporan - GDSS Relawan BPBD
 */
require_once dirname(__DIR__) . '/config/database.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

// Ambil data hasil Borda
$hasil_borda = mysqli_query($conn, "SELECT h.*, a.nama_tim, a.kode_alternatif, a.ketua_tim, a.wilayah
                                     FROM hasil_borda h
                                     JOIN alternatif a ON h.id_alternatif = a.id_alternatif
                                     ORDER BY h.ranking ASC");

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-file-earmark-text"></i> Laporan Hasil</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-printer"></i> Laporan Ranking Tim Relawan</h5>
            <button onclick="window.print()" class="btn btn-light btn-sm">
                <i class="bi bi-printer-fill"></i> Cetak
            </button>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h4>LAPORAN HASIL PEMILIHAN TIM RELAWAN TERBAIK</h4>
                <h5>BPBD KOTA MEDAN</h5>
                <p class="text-muted">Metode Borda - <?= date('d F Y') ?></p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">Ranking</th>
                            <th>Kode</th>
                            <th>Nama Tim</th>
                            <th>Ketua Tim</th>
                            <th>Wilayah</th>
                            <th class="text-center">Total Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($hasil_borda)): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($row['ranking'] <= 3): ?>
                                    <span class="badge bg-<?= ['', 'warning', 'secondary', 'dark'][$row['ranking']] ?> text-<?= $row['ranking'] == 1 ? 'dark' : 'white' ?>">
                                        #<?= $row['ranking'] ?>
                                    </span>
                                <?php else: ?>
                                    #<?= $row['ranking'] ?>
                                <?php endif; ?>
                            </td>
                            <td><code><?= $row['kode_alternatif'] ?></code></td>
                            <td><strong><?= htmlspecialchars($row['nama_tim']) ?></strong></td>
                            <td><?= htmlspecialchars($row['ketua_tim']) ?></td>
                            <td><?= htmlspecialchars($row['wilayah']) ?></td>
                            <td class="text-center"><span class="badge bg-primary"><?= number_format($row['total_skor_borda'], 2) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-6 offset-md-6 text-center">
                    <p>Medan, <?= date('d F Y') ?></p>
                    <p>Kepala BPBD Kota Medan</p>
                    <br><br><br>
                    <p><strong>(.................................)</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .breadcrumb, .btn, footer { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include INCLUDES_PATH . 'footer.php'; ?>
