<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$review_id = sanitize($_GET['id']);

try {
    // Get review images to delete files
    $images = $conn->prepare("SELECT * FROM sharh_rasmlari WHERE sharh_id = :review_id");
    $images->bindParam(':review_id', $review_id);
    $images->execute();
    
    while ($image = $images->fetch(PDO::FETCH_ASSOC)) {
        $image_path = '../../' . $image['rasm_url'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $conn->beginTransaction();
    
    // Delete review images
    $stmt = $conn->prepare("DELETE FROM sharh_rasmlari WHERE sharh_id = :review_id");
    $stmt->bindParam(':review_id', $review_id);
    $stmt->execute();
    
    // Delete the review
    $stmt = $conn->prepare("DELETE FROM mahsulot_sharhlari WHERE id = :review_id");
    $stmt->bindParam(':review_id', $review_id);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['success_message'] = "Review deleted successfully!";
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error deleting review: " . $e->getMessage();
}

redirect('index.php');