<?php
require_once __DIR__ . '/../api_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Foydalanuvchilar ro'yxatini olish
        try {
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
        
        try {
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