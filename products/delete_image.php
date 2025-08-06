<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['product_id']) || !isset($_GET['image_id'])) {
    redirect('index.php');
}

$product_id = sanitize($_GET['product_id']);
$image_id = sanitize($_GET['image_id']);

try {
    // First get the image URL to delete the file
    $stmt = $conn->prepare("SELECT rasm_url FROM mahsulot_rasmlari WHERE id = :image_id AND mahsulot_id = :product_id");
    $stmt->bindParam(':image_id', $image_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 1) {
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_path = '../../' . $image['rasm_url'];
        
        // Delete the image record from database
        $deleteStmt = $conn->prepare("DELETE FROM mahsulot_rasmlari WHERE id = :image_id");
        $deleteStmt->bindParam(':image_id', $image_id);
        $deleteStmt->execute();
        
        // Delete the actual file
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        $_SESSION['success_message'] = "Image deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Image not found!";
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Error deleting image: " . $e->getMessage();
}

redirect("update.php?id=$product_id");