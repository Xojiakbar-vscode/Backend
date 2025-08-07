<?php
require_once __DIR__ . '/../api_config.php';

header("Access-Control-Allow-Origin: http://localhost:5173"); // Frontend manzilini to'g'ri kiriting
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// OPTIONS so'rovlarini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Faqat GET so'rovlarini qabul qilamiz
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Faqat GET so\'rovlari qabul qilinadi'
    ]);
    exit();
}

try {
    // Sessionda foydalanuvchi borligini tekshiramiz
    if (isset($_SESSION['user'])) {
        // Parolni o'chirib tashlaymiz (xavfsizlik uchun)
        unset($_SESSION['user']['parol']);
        
        // Foydalanuvchi ma'lumotlarini qaytaramiz
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        // Foydalanuvchi kirish qilmagan
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
} catch (Exception $e) {
    // Xatolik yuz berganda
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server xatosi: ' . $e->getMessage()
    ]);
}
?>