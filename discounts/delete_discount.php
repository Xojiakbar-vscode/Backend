<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$discount_id = sanitize($_GET['id']);

try {
    $conn->beginTransaction();
    
    // Remove product associations
    $stmt = $conn->prepare("DELETE FROM mahsulot_chegirmalari WHERE chegirma_id = :discount_id");
    $stmt->bindParam(':discount_id', $discount_id);
    $stmt->execute();
    
    // Delete the discount
    $stmt = $conn->prepare("DELETE FROM chegirmalar WHERE id = :discount_id");
    $stmt->bindParam(':discount_id', $discount_id);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['success_message'] = "Chegirma muaffaqiyatli tugadi";
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Chegirmani o'chirishda muammo yuzaga keldi " . $e->getMessage();
}

redirect('index.php');