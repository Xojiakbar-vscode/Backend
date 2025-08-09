<?php
require_once '../config.php';

// === CORS va sessiya sozlamalari ===
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? null;

header('Content-Type: application/json');

// ===== GET — Mahsulotlar ro'yxati =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Rasmlar uchun to'liq URL yaratish
        foreach ($products as &$product) {
            if ($product['image_url']) {
                $product['image_url'] = 'http://localhost/tailorshop/Backend/' . $product['image_url'];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// ===== POST — Sevimlilarga qo'shish =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Avval tizimga kiring'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;

    if ($product_id) {
        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO yoqtrilgan_mahsulotlar (foydalanuvchi_id, mahsulot_id) VALUES (:user_id, :product_id)");
            $stmt->execute([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Sevimlilarga qo\'shildi'
            ]);
            exit;
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}

// ===== DELETE — Sevimlilardan olib tashlash =====
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!$user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Avval tizimga kiring'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;

    if ($product_id) {
        try {
            $stmt = $conn->prepare("DELETE FROM yoqtrilgan_mahsulotlar WHERE foydalanuvchi_id = :user_id AND mahsulot_id = :product_id");
            $stmt->execute([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Sevimlilardan olib tashlandi'
            ]);
            exit;
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Metod qo\'llab-quvvatlanmaydi'
]);
