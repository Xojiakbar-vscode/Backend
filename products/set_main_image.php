<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['product_id']) || !isset($_GET['image_id'])) {
    redirect('index.php');
}

$product_id = sanitize($_GET['product_id']);
$image_id = sanitize($_GET['image_id']);

try {
    $conn->beginTransaction();
    
    // Reset all main images for this product
    $stmt = $conn->prepare("UPDATE mahsulot_rasmlari SET asosiy_rasm = 0 WHERE mahsulot_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    // Set the selected image as main
    $stmt = $conn->prepare("UPDATE mahsulot_rasmlari SET asosiy_rasm = 1 WHERE id = :image_id AND mahsulot_id = :product_id");
    $stmt->bindParam(':image_id', $image_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['success_message'] = "Main image updated successfully!";
} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error updating main image: " . $e->getMessage();
}

redirect("update.php?id=$product_id");