<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Xatoliklarni ko'rsatish
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// JSON javob qaytarish funksiyasi
function javob($data = null, $status = 200, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Ma'lumotlar bazasi ulanishi
try {
    $conn = new PDO("mysql:host=localhost;dbname=tailorshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    javob(null, 500, "Database connection failed: " . $e->getMessage());
}

// Asosiy autentifikatsiya funksiyasi
function authenticate() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        javob(null, 401, 'Access denied. No token provided.');
    }

    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    
    // Bu yerda token tekshiruvi bo'lishi kerak
    // Soddalik uchun biz faqat mavjudligini tekshiramiz
    if (empty($token)) {
        javob(null, 401, 'Invalid token.');
    }
    
    return $token;
}
?>