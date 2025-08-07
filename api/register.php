<?php
require_once __DIR__ . '/../api_config.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents("php://input"), true);

$required = ['ism', 'familiya', 'email', 'telefon', 'parol'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$field maydoni to'ldirilishi shart"]);
        exit();
    }
}

try {
    // Email unikal ekanligini tekshirish
    $stmt = $conn->prepare("SELECT id FROM foydalanuvchilar WHERE email = :email");
    $stmt->execute([':email' => $data['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bu email allaqachon ro\'yxatdan o\'tgan']);
        exit();
    }

    // Yangi foydalanuvchi qo'shish
    $stmt = $conn->prepare("INSERT INTO foydalanuvchilar 
        (ism, familiya, email, telefon, parol) 
        VALUES (:ism, :familiya, :email, :telefon, :parol)");
        
    $stmt->execute([
        ':ism' => $data['ism'],
        ':familiya' => $data['familiya'],
        ':email' => $data['email'],
        ':telefon' => $data['telefon'],
        ':parol' => password_hash($data['parol'], PASSWORD_BCRYPT)
    ]);
    
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Muvaffaqiyatli ro\'yxatdan o\'tdingiz']);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server xatosi: ' . $e->getMessage()]);
}
?>