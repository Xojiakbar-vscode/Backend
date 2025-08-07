<?php
require_once __DIR__ . '/../api_config.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Foydalanuvchilar ro'yxatini olish (faqat admin uchun)
        try {
            // Sessionni tekshiramiz
            session_start();
            if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'admin@example.com') {
                javob(null, 403, "Ruxsat etilmagan");
            }
            
            $stmt = $conn->prepare("SELECT id, ism, familiya, email, telefon, faol FROM foydalanuvchilar");
            $stmt->execute();
            $foydalanuvchilar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            javob($foydalanuvchilar);
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    case 'POST':
        // Yangi foydalanuvchi qo'shish
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['ism', 'familiya', 'email', 'telefon', 'parol'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                javob(null, 400, "$field maydoni to'ldirilishi shart");
            }
        }
        
        // Email unikal ekanligini tekshiramiz
        try {
            $stmt = $conn->prepare("SELECT id FROM foydalanuvchilar WHERE email = :email");
            $stmt->execute([':email' => $data['email']]);
            if ($stmt->fetch()) {
                javob(null, 400, "Bu email allaqachon ro'yxatdan o'tgan");
            }
            
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
            
            $id = $conn->lastInsertId();
            javob(['id' => $id], 201, 'Foydalanuvchi muvaffaqiyatli qo\'shildi');
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    default:
        javob(null, 405, 'So\'rov usuli qo\'llab-quvvatlanmaydi');
}
?>