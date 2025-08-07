<?php

header("Access-Control-Allow-Origin: *"); // barcha domenlarga ruxsat
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config.php'; // agar kerak bo‘lsa


// Agar foydalanuvchi login bo'lsa session orqali id olamiz
$user_id = $_SESSION['user_id'] ?? null;

try {
    // Mahsulotlarni olish
    $sql = "
        SELECT 
            m.id,
            m.nomi AS name,
            m.qisqa_tavsif AS short_description,
            m.asosiy_narx AS price,
            m.chegirma_narxi AS discount_price,
            m.reyting AS rating,
            m.sharhlar_soni AS review_count,
            r.rasm_url AS image_url,
            IF(w.id IS NULL, 0, 1) AS is_favorite
        FROM mahsulotlar m
        LEFT JOIN mahsulot_rasmlari r 
            ON r.mahsulot_id = m.id AND r.asosiy_rasm = 1
        " . ($user_id ? "LEFT JOIN istaklar_royxati w 
            ON w.mahsulot_id = m.id AND w.foydalanuvchi_id = :user_id" : "LEFT JOIN istaklar_royxati w ON 1=0") . "
        WHERE m.faol = 1
        ORDER BY m.yaratilgan_vaqt DESC
    ";

    $stmt = $conn->prepare($sql);

    if ($user_id) {
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agar rasm bo'lmasa, placeholder beramiz
    foreach ($products as &$p) {
        if (!$p['image_url']) {
            $p['image_url'] = '/placeholder-product.jpg';
        }
    }

    echo json_encode([
        "success" => true,
        "data" => $products
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Xatolik yuz berdi: " . $e->getMessage()
    ]);
}
