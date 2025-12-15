<?php
/**
 * Login Page - GDSS Relawan BPBD
 */
require_once dirname(__DIR__, 2) . '/config/database.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = escape($_POST['username']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['jabatan'] = $user['jabatan'];
        
        // Redirect ke dashboard
        redirect('dashboard.php');
    } else {
        $error = 'Username atau password yang Anda masukkan salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - GDSS Relawan BPBD Kota Medan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></svg>');
            opacity: 0.3;
        }
        
        .login-header .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .login-header h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
        }
        
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin-bottom: 5px;
            position: relative;
        }
        
        .login-header small {
            opacity: 0.85;
            position: relative;
        }
        
        .login-body {
            padding: 40px 35px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating input {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px 20px;
            height: 60px;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .form-floating label {
            padding: 1rem 1.25rem;
            color: #6c757d;
        }
        
        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.2rem;
            pointer-events: none;
            z-index: 10;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login span {
            position: relative;
            z-index: 1;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #999;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        
        .info-box .badge {
            background: white;
            color: #667eea;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 25px;
            animation: fadeIn 1s ease-in;
        }
        
        .footer-text p {
            color: white;
            font-size: 0.9rem;
            margin: 5px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: white;
        }
        
        @media (max-width: 576px) {
            .login-header h3 {
                font-size: 1.5rem;
            }
            
            .login-body {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3>GDSS Relawan BPBD</h3>
                <p>Group Decision Support System</p>
                <small>Pemilihan Tim Relawan Terbaik - Metode Borda</small>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error!</strong> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Form Login -->
                <form method="POST" action="" id="loginForm">
                    <div class="form-floating position-relative">
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Username"
                               required 
                               autofocus>
                        <label for="username">
                            <i class="bi bi-person-circle me-2"></i>Username
                        </label>
                        <i class="bi bi-person-fill input-icon"></i>
                    </div>
                    
                    <div class="form-floating position-relative">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Password"
                               required>
                        <label for="password">
                            <i class="bi bi-lock-fill me-2"></i>Password
                        </label>
                        <i class="bi bi-key-fill input-icon"></i>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-login">
                        <span>
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Masuk ke Sistem
                        </span>
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="divider">
                    <span>Informasi Login</span>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <i class="bi bi-info-circle-fill text-primary mb-2" style="font-size: 1.5rem;"></i>
                    <p class="mb-2 text-muted small">Akun default untuk testing:</p>
                    <div class="badge">
                        <i class="bi bi-person-badge me-1"></i>
                        admin / admin123
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            <p class="mb-1">
                <i class="bi bi-building"></i>
                <strong>Badan Penanggulangan Bencana Daerah</strong>
            </p>
            <p class="mb-0">Kota Medan - Sumatera Utara</p>
            <p style="font-size: 0.8rem; opacity: 0.9;">
                © <?= date('Y') ?> BPBD Kota Medan. All Rights Reserved.
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto hide alert after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (typeof bootstrap !== 'undefined') {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (username === '' || password === '') {
                e.preventDefault();
                alert('Username dan password harus diisi!');
                return false;
            }
        });
        
        // Add animation on input focus
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
