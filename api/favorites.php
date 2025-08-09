<?php
session_start();

// CORS sozlamalari
header("Access-Control-Allow-Origin: http://localhost:3000"); // React domeni
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// OPTIONS so‘rovlarini qaytarish (CORS uchun)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// Avtorizatsiya tekshirish
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Avval tizimga kiring']);
    exit;
}

// JSON dan kelgan malumot
$data = json_decode(file_get_contents("php://input"), true);
$product_id = $data['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Mahsulot ID kiritilmadi']);
    exit;
}

// Ma'lumotlar bazasiga ulanish
require_once '../db.php'; // MySQL ulanish fayli

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sevimlilarga qo'shish
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE product_id=product_id");
    $stmt->bind_param("ii", $user_id, $product_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Sevimlilardan o‘chirish
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Noto‘g‘ri metod']);
