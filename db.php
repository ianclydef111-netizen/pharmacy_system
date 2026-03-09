<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pharmacy_db');

/*Connect to Database using PDO*/
function connect() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

session_start();

function redirect($url) {
    header("Location: $url");
    exit();
}

/* Check if user is logged in */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/* Stop access if not logged in */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

/* Stop access if not admin */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        redirect('dashboard.php?error=Admins only.');
    }
}

/* Clean output to prevent XSS */
function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
