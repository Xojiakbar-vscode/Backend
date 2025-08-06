<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['product_id']) || !isset($_GET['variant_id'])) {
    redirect('index.php');
}

$product_id = sanitize($_GET['product_id']);
$variant_id = sanitize($_GET['variant_id']);

try {
    // Get variant image path if exists
    $stmt = $conn->prepare("SELECT rasm_url FROM mahsulot_variantlari WHERE id = :variant_id");
    $stmt->bindParam(':variant_id', $variant_id);
    $stmt->execute();
    $variant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($variant && $variant['rasm_url']) {
        $image_path = '../../' . $variant['rasm_url'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete the variant
    $stmt = $conn->prepare("DELETE FROM mahsulot_variantlari WHERE id = :variant_id");
    $stmt->bindParam(':variant_id', $variant_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Variant deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting variant: " . $e->getMessage();
}

redirect("variants.php?product_id=$product_id");