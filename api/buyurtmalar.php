<?php
require_once __DIR__ . '/../api_config.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];
$token = authenticate();

switch ($method) {
    case 'GET':
        // Foydalanuvchi buyurtmalarini olish
        try {
            // Token orqali foydalanuvchi ID sini olish
            $stmt = $conn->prepare("SELECT id FROM foydalanuvchilar WHERE token = :token");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                javob(null, 404, 'Foydalanuvchi topilmadi');
            }
            
            $stmt = $conn->prepare("SELECT * FROM buyurtmalar WHERE foydalanuvchi_id = :user_id ORDER BY yaratilgan_vaqt DESC");
            $stmt->execute([':user_id' => $user['id']]);
            $buyurtmalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Har bir buyurtma uchun elementlarni olish
            foreach ($buyurtmalar as &$buyurtma) {
                $stmt = $conn->prepare("SELECT * FROM buyurtma_elementlari WHERE buyurtma_id = :id");
                $stmt->execute([':id' => $buyurtma['id']]);
                $buyurtma['elementlar'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            javob($buyurtmalar);
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    case 'POST':
        // Yangi buyurtma yaratish
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['elementlar', 'yetkazish_manzili'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                javob(null, 400, "$field maydoni to'ldirilishi shart");
            }
        }
        
        try {
            // Token orqali foydalanuvchi ID sini olish
            $stmt = $conn->prepare("SELECT id FROM foydalanuvchilar WHERE token = :token");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                javob(null, 404, 'Foydalanuvchi topilmadi');
            }
            
            // Buyurtma raqamini yaratish
            $buyurtma_raqami = 'TS-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Umumiy summani hisoblash
            $umumiy_summa = 0;
            foreach ($data['elementlar'] as $element) {
                $umumiy_summa += $element['miqdor'] * ($element['chegirma_narxi'] ?? $element['dona_narxi']);
            }
            
            // Buyurtmani yaratish
            $stmt = $conn->prepare("INSERT INTO buyurtmalar 
                (buyurtma_raqami, foydalanuvchi_id, holat, umumiy_summa, 
                yetkazish_summa, yakuniy_summa, tolov_usuli, 
                yetkazish_manzili, mijoz_izohi) 
                VALUES (:buyurtma_raqami, :foydalanuvchi_id, 'kutilyapti', 
                :umumiy_summa, :yetkazish_summa, :yakuniy_summa, 
                :tolov_usuli, :yetkazish_manzili, :mijoz_izohi)");
                
            $stmt->execute([
                ':buyurtma_raqami' => $buyurtma_raqami,
                ':foydalanuvchi_id' => $user['id'],
                ':umumiy_summa' => $umumiy_summa,
                ':yetkazish_summa' => $data['yetkazish_summa'] ?? 0,
                ':yakuniy_summa' => $umumiy_summa + ($data['yetkazish_summa'] ?? 0),
                ':tolov_usuli' => $data['tolov_usuli'] ?? 'naqd',
                ':yetkazish_manzili' => json_encode($data['yetkazish_manzili']),
                ':mijoz_izohi' => $data['mijoz_izohi'] ?? null
            ]);
            
            $buyurtma_id = $conn->lastInsertId();
            
            // Buyurtma elementlarini qo'shish
            foreach ($data['elementlar'] as $element) {
                $stmt = $conn->prepare("INSERT INTO buyurtma_elementlari 
                    (buyurtma_id, mahsulot_id, variant_id, mahsulot_nomi, 
                    variant_xususiyatlari, miqdor, dona_narxi, chegirma_narxi, 
                    umumiy_narxi, olchov_birligi) 
                    VALUES (:buyurtma_id, :mahsulot_id, :variant_id, :mahsulot_nomi, 
                    :variant_xususiyatlari, :miqdor, :dona_narxi, :chegirma_narxi, 
                    :umumiy_narxi, :olchov_birligi)");
                    
                $stmt->execute([
                    ':buyurtma_id' => $buyurtma_id,
                    ':mahsulot_id' => $element['mahsulot_id'],
                    ':variant_id' => $element['variant_id'] ?? null,
                    ':mahsulot_nomi' => $element['mahsulot_nomi'],
                    ':variant_xususiyatlari' => json_encode($element['variant_xususiyatlari'] ?? []),
                    ':miqdor' => $element['miqdor'],
                    ':dona_narxi' => $element['dona_narxi'],
                    ':chegirma_narxi' => $element['chegirma_narxi'] ?? null,
                    ':umumiy_narxi' => $element['miqdor'] * ($element['chegirma_narxi'] ?? $element['dona_narxi']),
                    ':olchov_birligi' => $element['olchov_birligi'] ?? 'dona'
                ]);
            }
            
            // Savatchani tozalash
            $stmt = $conn->prepare("DELETE FROM savatcha WHERE foydalanuvchi_id = :user_id");
            $stmt->execute([':user_id' => $user['id']]);
            
            javob(['buyurtma_raqami' => $buyurtma_raqami], 201, 'Buyurtma muvaffaqiyatli yaratildi');
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    default:
        javob(null, 405, 'So\'rov usuli qo\'llab-quvvatlanmaydi');
}
?>