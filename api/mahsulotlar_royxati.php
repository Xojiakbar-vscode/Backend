<?php
require_once __DIR__ . '/../api_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Mahsulotlar ro'yxatini olish
        try {
            $query = "SELECT m.id, m.nomi, m.asosiy_narx, m.chegirma_narxi, m.reyting, 
                     m.yangi_kelgan, m.eng_sotilgan, k.nomi as kategoriya
                     FROM mahsulotlar m
                     JOIN kategoriyalar k ON m.kategoriya_id = k.id
                     WHERE m.faol = 1";
            
            // Filtrlash parametrlari
            $params = [];
            
            if (isset($_GET['kategoriya'])) {
                $query .= " AND m.kategoriya_id = :kategoriya";
                $params[':kategoriya'] = $_GET['kategoriya'];
            }
            
            if (isset($_GET['qidiruv'])) {
                $query .= " AND m.nomi LIKE :qidiruv";
                $params[':qidiruv'] = '%' . $_GET['qidiruv'] . '%';
            }
            
            // Tartiblash
            $sort = $_GET['sort'] ?? 'yaratilgan_vaqt';
            $order = $_GET['order'] ?? 'DESC';
            $query .= " ORDER BY $sort $order";
            
            // Sahifalash
            $limit = $_GET['limit'] ?? 10;
            $page = $_GET['page'] ?? 1;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $mahsulotlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            javob($mahsulotlar);
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    case 'POST':
        // Yangi mahsulot qo'shish (faqat admin)
        authenticate();
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['nomi', 'asosiy_narx', 'kategoriya_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                javob(null, 400, "$field maydoni to'ldirilishi shart");
            }
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO mahsulotlar 
                (nomi, slug, tavsif, qisqa_tavsif, asosiy_narx, chegirma_narxi, 
                kategoriya_id, afzallikli, eng_sotilgan, yangi_kelgan) 
                VALUES (:nomi, :slug, :tavsif, :qisqa_tavsif, :asosiy_narx, 
                :chegirma_narxi, :kategoriya_id, :afzallikli, :eng_sotilgan, :yangi_kelgan)");
                
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nomi'])));
            
            $stmt->execute([
                ':nomi' => $data['nomi'],
                ':slug' => $slug,
                ':tavsif' => $data['tavsif'] ?? null,
                ':qisqa_tavsif' => $data['qisqa_tavsif'] ?? null,
                ':asosiy_narx' => $data['asosiy_narx'],
                ':chegirma_narxi' => $data['chegirma_narxi'] ?? null,
                ':kategoriya_id' => $data['kategoriya_id'],
                ':afzallikli' => $data['afzallikli'] ?? 0,
                ':eng_sotilgan' => $data['eng_sotilgan'] ?? 0,
                ':yangi_kelgan' => $data['yangi_kelgan'] ?? 1
            ]);
            
            $id = $conn->lastInsertId();
            javob(['id' => $id], 201, 'Mahsulot muvaffaqiyatli qo\'shildi');
        } catch(PDOException $e) {
            javob(null, 500, $e->getMessage());
        }
        break;
        
    default:
        javob(null, 405, 'So\'rov usuli qo\'llab-quvvatlanmaydi');
}
?>