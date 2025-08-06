<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$method_id = sanitize($_GET['id']);

try {
    $stmt = $conn->prepare("DELETE FROM yetkazish_usullari WHERE id = :id");
    $stmt->bindParam(':id', $method_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Shipping method deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting shipping method: " . $e->getMessage();
}

redirect('index.php');