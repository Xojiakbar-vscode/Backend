<?php
require_once __DIR__ . '/../api_config.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

session_start();

function validateInput($data) {
    if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yaroqli foydalanuvchi ID kiritilmadi']);
        exit();
    }
    if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yaroqli mahsulot ID kiritilmadi']);
        exit();
    }
}

// GET - Sevimlilarni olish
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID kiritilmadi']);
        exit();
    }

    try {
       $stmt = $conn->prepare("
    SELECT 
        p.id, 
        p.nomi AS name, 
        p.qisqa_tavsif AS description, 
        p.asosiy_narx AS price, 
        p.chegirma_narxi AS discount_price,
        p.reyting AS rating, 
        p.sharhlar_soni AS review_count,
        CONCAT('uploads/products/', r.rasm_url) AS image,
        EXISTS(SELECT 1 FROM istaklar_royxati WHERE foydalanuvchi_id = :user_id AND mahsulot_id = p.id) AS is_favorite
    FROM istaklar_royxati w
    JOIN mahsulotlar p ON w.mahsulot_id = p.id
    LEFT JOIN mahsulot_rasmlari r ON r.mahsulot_id = p.id AND r.asosiy_rasm = 1
    WHERE w.foydalanuvchi_id = :user_id
");
        $stmt->execute([':user_id' => $_GET['user_id']]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $favorites
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
// POST - Sevimliga qo'shish
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    validateInput($input);

    try {
        $stmt = $conn->prepare("
            INSERT INTO istaklar_royxati (foydalanuvchi_id, mahsulot_id)
            VALUES (:user_id, :product_id)
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ");
        $stmt->execute([
            ':user_id' => $input['user_id'],
            ':product_id' => $input['product_id']
        ]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
// DELETE - Sevimlidan o'chirish
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    validateInput($input);

    try {
        $stmt = $conn->prepare("
            DELETE FROM istaklar_royxati 
            WHERE foydalanuvchi_id = :user_id AND mahsulot_id = :product_id
        ");
        $stmt->execute([
            ':user_id' => $input['user_id'],
            ':product_id' => $input['product_id']
        ]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'So\'rov usuli qo\'llab-quvvatlanmaydi']);
}
?>