<?php
/**
 * Data Penilaian - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

// Ambil data penilaian
$where = "";
if (isAsesor()) {
    $where = "WHERE p.id_user = '{$_SESSION['user_id']}'";
}

$query = "SELECT p.*, a.nama_tim, a.kode_alternatif, k.nama_kriteria, k.kode_kriteria, 
                 s.nama_subkriteria, s.nilai, u.nama_lengkap as nama_asesor
          FROM penilaian p
          JOIN alternatif a ON p.id_alternatif = a.id_alternatif
          JOIN kriteria k ON p.id_kriteria = k.id_kriteria
          JOIN subkriteria s ON p.id_subkriteria = s.id_subkriteria
          JOIN user u ON p.id_user = u.id_user
          $where
          ORDER BY a.kode_alternatif, k.kode_kriteria";
$penilaian_list = mysqli_query($conn, $query);

// Ambil data ranking untuk modal
$query_ranking = "SELECT h.*, a.nama_tim, a.kode_alternatif 
                  FROM hasil_borda h 
                  JOIN alternatif a ON h.id_alternatif = a.id_alternatif 
                  ORDER BY h.ranking ASC";
$ranking_list = mysqli_query($conn, $query_ranking);

// Ambil data detail ranking per asesor
$query_detail = "SELECT d.*, a.nama_tim, a.kode_alternatif, u.nama_lengkap as nama_asesor
                 FROM detail_borda d
                 JOIN alternatif a ON d.id_alternatif = a.id_alternatif
                 JOIN user u ON d.id_user = u.id_user
                 ORDER BY u.nama_lengkap ASC, d.ranking_asesor ASC";
$detail_q = mysqli_query($conn, $query_detail);

$asesor_rankings = [];
while ($row = mysqli_fetch_assoc($detail_q)) {
    $asesor_rankings[$row['nama_asesor']][] = $row;
}

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-table"></i> Data Penilaian</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Data Penilaian</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Daftar Penilaian</h5>
            <div>
                <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#rankingModal">
                    <i class="bi bi-trophy"></i> Lihat Ranking
                </button>
                <?php if (!isAdmin()): ?>
                <a href="<?= BASE_URL ?>modules/penilaian/input.php" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Penilaian
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>Tim</th>
                            <th>Kriteria</th>
                            <th>Nilai</th>
                            <th>Asesor</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($penilaian_list)): ?>
                        <tr>
                            <td>
                                <code><?= $row['kode_alternatif'] ?></code>
                                <strong><?= htmlspecialchars($row['nama_tim']) ?></strong>
                            </td>
                            <td>
                                <code><?= $row['kode_kriteria'] ?></code>
                                <?= htmlspecialchars($row['nama_kriteria']) ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $row['nilai'] ?></span>
                                <small class="text-muted"><?= htmlspecialchars($row['nama_subkriteria']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['nama_asesor']) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($row['catatan']) ?></small></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ranking -->
<div class="modal fade" id="rankingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title"><i class="bi bi-trophy-fill"></i> Ranking Tim Terbaik</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="rankingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="global-tab" data-bs-toggle="tab" data-bs-target="#global" type="button" role="tab">
                            <i class="bi bi-globe"></i> Global
                        </button>
                    </li>
                    <?php $i = 0; foreach ($asesor_rankings as $asesor => $data): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="asesor-<?= $i ?>-tab" data-bs-toggle="tab" data-bs-target="#asesor-<?= $i ?>" type="button" role="tab">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($asesor) ?>
                        </button>
                    </li>
                    <?php $i++; endforeach; ?>
                </ul>
                
                <div class="tab-content" id="rankingTabsContent">
                    <!-- Global Ranking -->
                    <div class="tab-pane fade show active" id="global" role="tabpanel">
                        <?php if (mysqli_num_rows($ranking_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Kode</th>
                                        <th>Nama Tim</th>
                                        <th>Total Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($ranking_list, 0);
                                    while ($rank = mysqli_fetch_assoc($ranking_list)): 
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank['ranking'] == 1): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #1</span>
                                            <?php elseif ($rank['ranking'] == 2): ?>
                                                <span class="badge bg-secondary">#2</span>
                                            <?php elseif ($rank['ranking'] == 3): ?>
                                        <span class="badge" style="background-color: #cd7f32;">#3</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">#<?= $rank['ranking'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= $rank['kode_alternatif'] ?></code></td>
                                        <td><strong><?= htmlspecialchars($rank['nama_tim']) ?></strong></td>
                                        <td><span class="badge bg-primary"><?= number_format($rank['total_skor_borda'], 2) ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Belum ada hasil perhitungan ranking.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Per Assessor Ranking -->
                    <?php $i = 0; foreach ($asesor_rankings as $asesor => $data): ?>
                    <div class="tab-pane fade" id="asesor-<?= $i ?>" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Kode</th>
                                        <th>Nama Tim</th>
                                        <th>Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $rank): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= $rank['ranking_asesor'] ?></span></td>
                                        <td><code><?= $rank['kode_alternatif'] ?></code></td>
                                        <td><strong><?= htmlspecialchars($rank['nama_tim']) ?></strong></td>
                                        <td><?= number_format($rank['skor_borda'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="<?= BASE_URL ?>modules/borda/hasil.php" class="btn btn-primary">Lihat Detail Lengkap</a>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . 'footer.php'; ?>
