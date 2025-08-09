<?php
require_once '../config.php';

// CORS va JSON header (agar React frontend bilan ishlasangiz)
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// GET dan product_id olish va tekshirish
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id || $product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Noto‘g‘ri mahsulot ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sessiyadagi user id (check_auth bilan moslashuv uchun)
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id'])) {
    $user_id = (int) $_SESSION['user']['id'];
} else {
    $user_id = 0; // 0 bilan yuborsak, is_favorite har doim 0 chiqadi
}

try {
    $sql = "
        SELECT 
            m.id, 
            m.nomi AS name, 
            m.qisqa_tavsif AS short_description, 
            m.asosiy_narx AS price, 
            m.chegirma_narxi AS discount_price, 
            m.reyting AS rating, 
            m.sharhlar_soni AS review_count, 
            pr.rasm_url AS image_url,
            CASE WHEN y.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite
        FROM mahsulotlar m
        LEFT JOIN (
            SELECT mahsulot_id, rasm_url
            FROM mahsulot_rasmlari
            WHERE id IN (
                SELECT MIN(id) FROM mahsulot_rasmlari GROUP BY mahsulot_id
            )
        ) pr ON m.id = pr.mahsulot_id
        LEFT JOIN yoqtrilgan_mahsulotlar y 
            ON m.id = y.mahsulot_id AND y.foydalanuvchi_id = :user_id
        WHERE m.id = :product_id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Mahsulot topilmadi'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $product], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB xatosi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
