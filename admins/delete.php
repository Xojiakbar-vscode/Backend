<?php
require_once '../config.php';checkLogin();

// Only super admin can access this page
if ($_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page!";
    redirect('dashboard.php');
}

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$admin_id = sanitize($_GET['id']);

// Prevent deleting yourself
if ($admin_id == $_SESSION['admin_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account!";
    redirect('index.php');
}

try {
    $stmt = $conn->prepare("DELETE FROM adminlar WHERE id = :id");
    $stmt->bindParam(':id', $admin_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Admin muaffaqiyatli ochirildi!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "O'chirishda muammo yuzaga keldi " . $e->getMessage();
}

redirect('index.php');