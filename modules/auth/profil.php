<?php
/**
 * Profil Page - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

$success = '';
$error = '';

// Ambil data user
$id_user = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE id_user = '$id_user'"));

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama_lengkap = escape($_POST['nama_lengkap']);
    $jabatan = escape($_POST['jabatan']);
    
    $query = "UPDATE user SET 
              nama_lengkap = '$nama_lengkap',
              jabatan = '$jabatan'
              WHERE id_user = '$id_user'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['jabatan'] = $jabatan;
        $success = 'Profil berhasil diupdate!';
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE id_user = '$id_user'"));
    } else {
        $error = 'Gagal update profil!';
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = md5($_POST['password_lama']);
    $password_baru = md5($_POST['password_baru']);
    $konfirmasi_password = md5($_POST['konfirmasi_password']);
    
    if ($password_lama != $user['password']) {
        $error = 'Password lama tidak sesuai!';
    } elseif ($password_baru != $konfirmasi_password) {
        $error = 'Konfirmasi password tidak sesuai!';
    } else {
        $query = "UPDATE user SET password = '$password_baru' WHERE id_user = '$id_user'";
        if (mysqli_query($conn, $query)) {
            $success = 'Password berhasil diubah!';
        } else {
            $error = 'Gagal mengubah password!';
        }
    }
}

// Statistik aktivitas user
$total_penilaian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penilaian WHERE id_user = '$id_user'"))['total'];
$tim_dinilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT id_alternatif) as total FROM penilaian WHERE id_user = '$id_user'"))['total'];

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-person-circle"></i> Profil Saya</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profil</li>
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
    
    <div class="row">
        <!-- Profil Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <div class="rounded-circle bg-gradient d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                    <p class="text-muted mb-2">@<?= htmlspecialchars($user['username']) ?></p>
                    <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'success' ?> mb-3">
                        <i class="bi bi-<?= $user['role'] == 'admin' ? 'shield-fill-check' : 'person-badge' ?>"></i>
                        <?= ucfirst($user['role']) ?>
                    </span>
                    <hr>
                    <p class="mb-1"><strong>Jabatan:</strong></p>
                    <p class="text-muted"><?= htmlspecialchars($user['jabatan']) ?></p>
                    <hr>
                    <p class="mb-1"><strong>Bergabung Sejak:</strong></p>
                    <p class="text-muted"><?= date('d F Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            
            <!-- Aktivitas Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Aktivitas Penilaian</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Penilaian:</span>
                        <strong class="text-primary"><?= $total_penilaian ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tim Dinilai:</span>
                        <strong class="text-success"><?= $tim_dinilai ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Edit -->
        <div class="col-md-8">
            <!-- Update Profil -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" 
                                   value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" 
                                   value="<?= htmlspecialchars($user['jabatan']) ?>">
                        </div>
                        
                        <button type="submit" name="update_profil" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Ganti Password -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-key"></i> Ganti Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formGantiPassword">
                        <div class="mb-3">
                            <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                            <input type="password" name="password_lama" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_baru" id="password_baru" class="form-control" required>
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" required>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Perhatian!</strong> Setelah mengganti password, Anda akan tetap login dengan session yang aktif.
                        </div>
                        
                        <button type="submit" name="ganti_password" class="btn btn-warning">
                            <i class="bi bi-shield-lock"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formGantiPassword').addEventListener('submit', function(e) {
    const password_baru = document.getElementById('password_baru').value;
    const konfirmasi = document.getElementById('konfirmasi_password').value;
    
    if (password_baru.length < 6) {
        e.preventDefault();
        showError('Password minimal 6 karakter!');
        return false;
    }
    
    if (password_baru !== konfirmasi) {
        e.preventDefault();
        showError('Konfirmasi password tidak sesuai!');
        return false;
    }
});
</script>

<?php include INCLUDES_PATH . 'footer.php'; ?>
