<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tailorshop');

// Start session only if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in for protected pages
function checkLogin() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

// Create database connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Asosiy URL ni aniqlash
define('BASE_URL', 'http://localhost/tailorshop/Backend/');
