<?php
require_once __DIR__ . '/../api_config.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Faqat POST so\'rovlari qabul qilinadi']);
  exit();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['id'] != $_POST['userId']) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Ruxsat etilmagan']);
  exit();
}

try {
  // Rasmni tekshirish
  if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('Rasm yuklashda xatolik');
  }

  // Rasm formatini tekshirish
  $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    throw new Exception('Faqat JPEG, PNG va GIF formatidagi rasmlar qabul qilinadi');
  }

  // Rasm hajmini tekshirish (max 2MB)
  if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
    throw new Exception('Rasm hajmi 2MB dan katta bo\'lmasligi kerak');
  }

  // Yangi fayl nomi
  $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
  $filename = 'profile_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
  $uploadPath = __DIR__ . '/../../uploads/profiles/' . $filename;

  // Papka mavjudligini tekshirish
  if (!file_exists(dirname($uploadPath))) {
    mkdir(dirname($uploadPath), 0777, true);
  }

  // Faylni ko'chirish
  if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    throw new Exception('Rasmni saqlashda xatolik');
  }

  // Bazaga yangilash
  $imageUrl = '/uploads/profiles/' . $filename;
  $stmt = $conn->prepare("UPDATE foydalanuvchilar SET rasm_url = :rasm_url WHERE id = :id");
  $stmt->execute([
    ':rasm_url' => $imageUrl,
    ':id' => $_SESSION['user']['id']
  ]);

  // Sessionni yangilash
  $_SESSION['user']['rasm_url'] = $imageUrl;

  echo json_encode([
    'success' => true,
    'message' => 'Profil rasmi muvaffaqiyatli yangilandi',
    'imageUrl' => $imageUrl
  ]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}