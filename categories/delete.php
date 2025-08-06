<?php
require_once '../config.php';checkLogin();

// Agar id kelmasa, kategoriyalar ro‘yxatiga qaytarish
if (!isset($_GET['id'])) {
    redirect('index.php');
}

$category_id = sanitize($_GET['id']);

try {
    // 1️⃣ Kategoriyada mahsulotlar bormi tekshirish
    $stmt = $conn->prepare("SELECT COUNT(*) FROM mahsulotlar WHERE kategoriya_id = :category_id");
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    $product_count = $stmt->fetchColumn();
    
    if ($product_count > 0) {
        $_SESSION['error_message'] = "Mahsulotlari bor kategoriyani o‘chirish mumkin emas! Avval mahsulotlarni o‘chir yoki boshqa kategoriyaga o‘tkaz.";
        redirect('index.php');
    }
    
    // 2️⃣ Kategoriyada ichki (ota-kichik) kategoriyalar bormi tekshirish
    $stmt = $conn->prepare("SELECT COUNT(*) FROM kategoriyalar WHERE ota_kategoriya_id = :category_id");
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    $subcategory_count = $stmt->fetchColumn();
    
    if ($subcategory_count > 0) {
        $_SESSION['error_message'] = "Ichki kategoriyalari bor kategoriyani o‘chirish mumkin emas! Avval ichki kategoriyalarni o‘chir yoki boshqa joyga o‘tkaz.";
        redirect('index.php');
    }
    
    // 3️⃣ Kategoriyani o‘chirish
    $stmt = $conn->prepare("DELETE FROM kategoriyalar WHERE id = :category_id");
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Kategoriya muvaffaqiyatli o‘chirildi!";
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Kategoriyani o‘chirishda xatolik: " . $e->getMessage();
}

// Oxirida kategoriyalar ro‘yxatiga qaytarish
redirect('index.php');
