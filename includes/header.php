<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDSS Relawan BPBD - Metode Borda</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-brand">
            <i class="bi bi-shield-check"></i> GDSS BPBD
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('dashboard.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <?php if (isAdmin()): ?>
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">DATA MASTER</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('modules/master/alternatif.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/master/alternatif.php">
                    <i class="bi bi-people"></i> Tim Relawan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('kriteria.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/master/kriteria.php">
                    <i class="bi bi-clipboard-check"></i> Kriteria
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('user.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/master/user.php">
                    <i class="bi bi-person-badge"></i> Asesor
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">PENILAIAN</small>
            </li>
            <?php if (!isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('input.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/penilaian/input.php">
                    <i class="bi bi-pencil-square"></i> Input Nilai
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('data.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/penilaian/data.php">
                    <i class="bi bi-table"></i> Data Penilaian
                </a>
            </li>
            
            <?php if (isBpbd()): ?>
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">PERHITUNGAN BORDA</small>
            </li>
            <?php endif; ?>
            
            <?php if (isBpbd()): ?>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('voting.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/borda/voting.php">
                    <i class="bi bi-calculator"></i> Proses Borda
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (isAdmin() || isBpbd()): ?>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('hasil.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/borda/hasil.php">
                    <i class="bi bi-trophy"></i> Hasil Borda
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">PERHITUNGAN TOPSIS</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('proses.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/borda/proses.php">
                    <i class="bi bi-gear"></i> Proses TOPSIS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('hasil_perhitungan.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>modules/borda/hasil_perhitungan.php">
                    <i class="bi bi-bar-chart"></i> Hasil TOPSIS
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">LAPORAN</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isCurrentPage('laporan.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>reports/laporan.php">
                    <i class="bi bi-file-earmark-text"></i> Laporan
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>modules/auth/logout.php" class="nav-link text-danger bg-white rounded">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light sticky-top">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">
                    <i class="bi bi-grid-3x3-gap"></i>
                    Group Decision Support System - Metode Borda
                </span>
                
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= $_SESSION['nama_lengkap'] ?? 'User' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/auth/profil.php">
                                <i class="bi bi-person"></i> Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>modules/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
