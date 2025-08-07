<?php
require_once __DIR__ . '/../api_config.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
$method = $_SERVER['REQUEST_METHOD'];
$token = authenticate();

// Token orqali foydalanuvchi ID sini olish
$stmt = $conn->prepare("SELECT id FROM foydalanuvchilar WHERE token = :token");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    javob(null, 404, 'Foydalanuvchi topilmadi');
}

$user_id = $user['id'];

switch ($method) {
    case 'GET':
        // Savatchani olish
        try {
            $stmt = $conn->prepare("SELECT s.*, m.nomi as mahsulot_nomi, m.asosiy_narx, 
                                  m.chegirma_narxi, m.rasm_url, v.rang, v.olcham
                                  FROM savatcha s
                                  JOIN mahsulotlar m ON s.mahsulot_id = m.id
                                  LEFT JOIN mahsulot_variantlari v ON s.variant_id = v.id
                                  WHERE s.foydalanuvchi_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $savatcha = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            javob($savatcha);
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    case 'POST':
        // Savatchaga mahsulot qo'shish
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['mahsulot_id'])) {
            javob(null, 400, "mahsulot_id maydoni to'ldirilishi shart");
        }
        
        try {
            // Mahsulot mavjudligini tekshirish
            $stmt = $conn->prepare("SELECT id FROM mahsulotlar WHERE id = :id AND faol = 1");
            $stmt->execute([':id' => $data['mahsulot_id']]);
            $mahsulot = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$mahsulot) {
                javob(null, 404, 'Mahsulot topilmadi yoki faol emas');
            }
            
            // Variantni tekshirish (agar mavjud bo'lsa)
            if (!empty($data['variant_id'])) {
                $stmt = $conn->prepare("SELECT id FROM mahsulot_variantlari 
                                      WHERE id = :id AND mahsulot_id = :mahsulot_id");
                $stmt->execute([
                    ':id' => $data['variant_id'],
                    ':mahsulot_id' => $data['mahsulot_id']
                ]);
                $variant = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$variant) {
                    javob(null, 404, 'Noto\'g\'ri variant tanlandi');
                }
            }
            
            // Savatchada mavjudligini tekshirish
            $stmt = $conn->prepare("SELECT id, miqdor FROM savatcha 
                                   WHERE foydalanuvchi_id = :user_id 
                                   AND mahsulot_id = :mahsulot_id 
                                   AND variant_id " . (empty($data['variant_id']) ? "IS NULL" : "= :variant_id"));
            
            $params = [
                ':user_id' => $user_id,
                ':mahsulot_id' => $data['mahsulot_id']
            ];
            
            if (!empty($data['variant_id'])) {
                $params[':variant_id'] = $data['variant_id'];
            }
            
            $stmt->execute($params);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Yangilash
                $new_qty = $existing['miqdor'] + ($data['miqdor'] ?? 1);
                $stmt = $conn->prepare("UPDATE savatcha SET miqdor = :miqdor WHERE id = :id");
                $stmt->execute([
                    ':miqdor' => $new_qty,
                    ':id' => $existing['id']
                ]);
            } else {
                // Yangi qo'shish
                $stmt = $conn->prepare("INSERT INTO savatcha 
                    (foydalanuvchi_id, mahsulot_id, variant_id, miqdor) 
                    VALUES (:user_id, :mahsulot_id, :variant_id, :miqdor)");
                    
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':mahsulot_id' => $data['mahsulot_id'],
                    ':variant_id' => $data['variant_id'] ?? null,
                    ':miqdor' => $data['miqdor'] ?? 1
                ]);
            }
            
            javob(null, 201, 'Mahsulot savatchaga qo\'shildi');
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    case 'DELETE':
        // Savatchadan mahsulotni o'chirish
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['id'])) {
            javob(null, 400, "id maydoni to'ldirilishi shart");
        }
        
        try {
            $stmt = $conn->prepare("DELETE FROM savatcha 
                                  WHERE id = :id AND foydalanuvchi_id = :user_id");
            $stmt->execute([
                ':id' => $data['id'],
                ':user_id' => $user_id
            ]);
            
            if ($stmt->rowCount() > 0) {
                javob(null, 200, 'Mahsulot savatchadan olib tashlandi');
            } else {
                javob(null, 404, 'Savatchada bunday mahsulot topilmadi');
            }
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    default:
        javob(null, 405, 'So\'rov usuli qo\'llab-quvvatlanmaydi');
}
?>