<?php
/**
 * Database Configuration & Helper Functions
 * GDSS Relawan BPBD - Metode Borda
 */

// Base Path Constants
define('BASE_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('INCLUDES_PATH', BASE_PATH . 'includes/');
define('MODULES_PATH', BASE_PATH . 'modules/');
define('ASSETS_PATH', BASE_PATH . 'assets/');

// Base URL (adjust if needed)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . $host . '/gdss_relawan_bpbd/gdss_relawan_bpbd/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gdss_relawan_bpbd');

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape string for SQL (use prepared statements for better security)
 */
function escape($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

/**
 * Format number with specified decimals
 */
function formatAngka($angka, $desimal = 2) {
    return number_format($angka, $desimal, '.', ',');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is BPBD (Borda decision maker)
 */
function isBpbd() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'borda';
}

/**
 * Check if user is asesor
 */
function isAsesor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'asesor';
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Redirect with absolute URL
 */
function redirectAbsolute($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    redirect('modules/auth/login.php');
}

/**
 * Get current page name
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Check if current page matches
 */
function isCurrentPage($page) {
    return getCurrentPage() === $page;
}
?>
