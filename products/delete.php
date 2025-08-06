<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$product_id = sanitize($_GET['id']);

try {
    $conn->beginTransaction();
    
    // Delete product images first
    $stmt = $conn->prepare("DELETE FROM mahsulot_rasmlari WHERE mahsulot_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    // Delete product variants
    $stmt = $conn->prepare("DELETE FROM mahsulot_variantlari WHERE mahsulot_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    // Delete the product
    $stmt = $conn->prepare("DELETE FROM mahsulotlar WHERE id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['success_message'] = "Product deleted successfully!";
} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
}

redirect('index.php');