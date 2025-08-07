<?php
require_once __DIR__ . '/../api_config.php';

// CORS sozlamalari
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Faqat POST so\'rovlari qabul qilinadi'
    ]);
    exit();
}

try {
    // Sessionni tozalash
    session_unset();
    session_destroy();
    
    // Muvaffaqiyatli javob
    echo json_encode([
        'success' => true,
        'message' => 'Muvaffaqiyatli chiqish'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Chiqishda xatolik: ' . $e->getMessage()
    ]);
}
?>