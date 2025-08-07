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

session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['email']) || empty($data['parol'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email va parol kiritilishi shart']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, ism, familiya, email, rasm_url, telefon, parol FROM foydalanuvchilar WHERE email = :email");
    $stmt->execute([':email' => $data['email']]);
    $foydalanuvchi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foydalanuvchi) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Foydalanuvchi topilmadi']);
        exit();
    }

    if (!password_verify($data['parol'], $foydalanuvchi['parol'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Noto\'g\'ri parol']);
        exit();
    }

    // Parolni o'chirib tashlaymiz
    unset($foydalanuvchi['parol']);
    
    // Sessionga foydalanuvchi ma'lumotlarini saqlaymiz
    $_SESSION['user'] = $foydalanuvchi;
    
    echo json_encode([
        'success' => true,
        'message' => 'Muvaffaqiyatli kirish',
        'rasm_url' => $user['rasm_url'],
        'user' => $foydalanuvchi
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server xatosi: ' . $e->getMessage()]);
}
?>