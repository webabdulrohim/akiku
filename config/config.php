<?php
/**
 * Core Stone Indonesia - Configuration File
 * Database and Application Settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'core_stone_db');

// Application Settings
define('APP_NAME', 'Core Stone Indonesia');
define('APP_URL', 'http://localhost/core-stone');
define('ADMIN_EMAIL', 'admin@corestone.id');

// Tripay Payment Gateway Configuration
define('TRIPAY_MERCHANT_CODE', ''); // Fill after registration
define('TRIPAY_PRIVATE_KEY', ''); // Fill after registration
define('TRIPAY_PUBLIC_KEY', ''); // Fill after registration
define('TRIPAY_MODE', 'sandbox'); // sandbox or production

// Session Settings
ini_set('session.cookie_httponly', 1);
session_start();

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
