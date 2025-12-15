<?php
/**
 * Kelola User/Asesor - GDSS Relawan BPBD
 */
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = escape($_POST['username']);
    $password = md5($_POST['password']);
    $nama = escape($_POST['nama_lengkap']);
    $role = escape($_POST['role']);
    $jabatan = escape($_POST['jabatan']);
    
    $check = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO user (username, password, nama_lengkap, role, jabatan) 
                  VALUES ('$username', '$password', '$nama', '$role', '$jabatan')";
        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan user!';
        }
    }
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = intval($_POST['id_user']);
    $nama = escape($_POST['nama_lengkap']);
    $role = escape($_POST['role']);
    $jabatan = escape($_POST['jabatan']);
    
    $query = "UPDATE user SET nama_lengkap = '$nama', role = '$role', jabatan = '$jabatan' WHERE id_user = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = 'User berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate user!';
    }
}

// Proses Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $id = intval($_POST['id_user']);
    $new_pass = md5('123456');
    if (mysqli_query($conn, "UPDATE user SET password = '$new_pass' WHERE id_user = '$id'")) {
        $success = 'Password berhasil direset ke: 123456';
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM penilaian WHERE id_user = '$id'");
        mysqli_query($conn, "DELETE FROM detail_borda WHERE id_user = '$id'");
        if (mysqli_query($conn, "DELETE FROM user WHERE id_user = '$id'")) {
            $success = 'User berhasil dihapus!';
        }
    } else {
        $error = 'Tidak dapat menghapus akun sendiri!';
    }
}

// Ambil data
$user_list = mysqli_query($conn, "SELECT * FROM user ORDER BY role, nama_lengkap");

include INCLUDES_PATH . 'header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-person-badge"></i> Kelola User/Asesor</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">User</li>
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
        <!-- Form Tambah -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Tambah User</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required placeholder="username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="asesor">Asesor (Penilai)</option>
                                <option value="borda">BPBD (Decision Maker)</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" placeholder="Jabatan/posisi">
                        </div>
                        <button type="submit" name="tambah" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Tambah User
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabel Data -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Data User</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-datatable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Jabatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $users = [];
                                while ($row = mysqli_fetch_assoc($user_list)) {
                                    $users[] = $row;
                                }
                                foreach ($users as $row): 
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td>
                                        <?php
                                        $role_class = ['admin' => 'danger', 'borda' => 'warning', 'asesor' => 'success'];
                                        $role_icon = ['admin' => 'shield-fill-check', 'borda' => 'star-fill', 'asesor' => 'person-badge'];
                                        ?>
                                        <span class="badge bg-<?= $role_class[$row['role']] ?? 'secondary' ?>">
                                            <i class="bi bi-<?= $role_icon[$row['role']] ?? 'person' ?>"></i>
                                            <?= ucfirst($row['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $row['id_user'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                    data-bs-target="#resetModal<?= $row['id_user'] ?>">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete('<?= BASE_URL ?>modules/master/user.php?hapus=<?= $row['id_user'] ?>', '<?= htmlspecialchars($row['nama_lengkap']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals outside table -->
<?php foreach ($users as $row): ?>
<!-- Modal Edit -->
<div class="modal fade" id="editModal<?= $row['id_user'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-gradient text-white">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" 
                               value="<?= htmlspecialchars($row['nama_lengkap']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="asesor" <?= $row['role'] == 'asesor' ? 'selected' : '' ?>>Asesor</option>
                            <option value="borda" <?= $row['role'] == 'borda' ? 'selected' : '' ?>>BPBD</option>
                            <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" 
                               value="<?= htmlspecialchars($row['jabatan']) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetModal<?= $row['id_user'] ?>" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                    <i class="bi bi-key text-warning" style="font-size: 3rem;"></i>
                    <p class="mt-2">Reset password untuk <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong>?</p>
                    <p class="text-muted small">Password baru: <code>123456</code></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reset_password" class="btn btn-warning">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include INCLUDES_PATH . 'footer.php'; ?>
