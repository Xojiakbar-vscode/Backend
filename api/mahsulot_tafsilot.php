<?php
require_once __DIR__ . '/../api_config.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET' && $id) {
    try {
        // Asosiy mahsulot ma'lumotlari
        $stmt = $conn->prepare("SELECT m.*, k.nomi as kategoriya 
                              FROM mahsulotlar m
                              JOIN kategoriyalar k ON m.kategoriya_id = k.id
                              WHERE m.id = :id");
        $stmt->execute([':id' => $id]);
        $mahsulot = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$mahsulot) {
            javob(null, 404, 'Mahsulot topilmadi');
        }
        
        // Mahsulot rasmlari
        $stmt = $conn->prepare("SELECT * FROM mahsulot_rasmlari WHERE mahsulot_id = :id ORDER BY korinish_tartibi");
        $stmt->execute([':id' => $id]);
        $rasmlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Variantlar
        $stmt = $conn->prepare("SELECT * FROM mahsulot_variantlari WHERE mahsulot_id = :id");
        $stmt->execute([':id' => $id]);
        $variantlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Sharhlar
        $stmt = $conn->prepare("SELECT s.*, f.ism, f.familiya 
                              FROM mahsulot_sharhlari s
                              JOIN foydalanuvchilar f ON s.foydalanuvchi_id = f.id
                              WHERE s.mahsulot_id = :id AND s.tasdiqlangan = 1
                              ORDER BY s.yaratilgan_vaqt DESC");
        $stmt->execute([':id' => $id]);
        $sharhlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Sharhlar uchun rasmlar
        foreach ($sharhlar as &$sharh) {
            $stmt = $conn->prepare("SELECT * FROM sharh_rasmlari WHERE sharh_id = :id");
            $stmt->execute([':id' => $sharh['id']]);
            $sharh['rasmlar'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $result = [
            'mahsulot' => $mahsulot,
            'rasmlar' => $rasmlar,
            'variantlar' => $variantlar,
            'sharhlar' => $sharhlar
        ];
        
        javob($result);
    } catch(PDOException $e) {
        javob(null, 500, $e->getMessage());
    }
} else {
    javob(null, 405, 'So\'rov usuli qo\'llab-quvvatlanmaydi');
}
?>