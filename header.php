<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Foydalanuvchi kirganligini tekshirish
function tekshirish() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
tekshirish();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>style.css">
</head>
<body>
<div class="wrapper d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-dark text-white" id="sidebar">
        <div class="sidebar-header">
            <h4>Admin Panel</h4>
        </div>
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Boshqaruv paneli
                </a>
            </li>

            <!-- Mahsulotlar -->
            <li class="nav-item">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#mahsulotlarMenu">
                    <i class="bi bi-box-seam me-2"></i> Mahsulotlar
                </a>
                <div class="collapse" id="mahsulotlarMenu">
                    <ul class="nav flex-column ps-4">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>products/index.php">
                                <i class="bi bi-list-ul me-2"></i> Barcha mahsulotlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>products/add.php">
                                <i class="bi bi-plus-circle me-2"></i> Yangi mahsulot
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- Kategoriyalar -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>categories/index.php">
                    <i class="bi bi-tags me-2"></i> Kategoriyalar
                </a>
            </li>

            <!-- Buyurtmalar -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>orders/index.php">
                    <i class="bi bi-cart me-2"></i> Buyurtmalar
                </a>
            </li>

            <!-- Foydalanuvchilar -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>users/index.php">
                    <i class="bi bi-people me-2"></i> Foydalanuvchilar
                </a>
            </li>

            <!-- Chegirmalar -->
            <li class="nav-item">
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#chegirmalarMenu">
                    <i class="bi bi-percent me-2"></i> Chegirmalar
                </a>
                <div class="collapse" id="chegirmalarMenu">
                    <ul class="nav flex-column ps-4">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>discounts/index.php">
                                <i class="bi bi-list-ul me-2"></i> Barcha chegirmalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>discounts/add.php">
                                <i class="bi bi-plus-circle me-2"></i> Yangi chegirma
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Yetkazib berish -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>shipping/index.php">
                    <i class="bi bi-truck me-2"></i> Yetkazib berish
                </a>
            </li>

            <!-- Sharhlar -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>reviews/index.php">
                    <i class="bi bi-star me-2"></i> Sharhlar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>admins/index.php">
                    <i class="bi bi-person-circle me-1"></i>Admins
                </a>
            </li>

            <!-- Adminlar faqat super_admin uchun -->
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>admins/index.php">
                    <i class="bi bi-person-badge me-2"></i> Adminlar
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right me-2"></i> Chiqish
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content w-100" id="mainContent">
        <!-- Navbar -->
       <nav class="navbar navbar-expand-lg navbar-light border-bottom">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary btn-sm" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Chiqish</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

        <!-- Sahifa kontenti boshlanishi -->
        <div class="container-fluid p-4">
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('closed');
    document.getElementById('mainContent').classList.toggle('closed');
});
</script>
