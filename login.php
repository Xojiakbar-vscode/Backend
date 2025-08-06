<?php
require_once 'config.php';

// Agar session yoqilmagan bo'lsa, yoqamiz
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1️⃣ Avtomatik default admin yaratish (faqat jadval bo'sh bo'lsa)
try {
    $check = $conn->query("SELECT COUNT(*) FROM adminlar")->fetchColumn();
    if ($check == 0) {
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO adminlar 
            (foydalanuvchi_nomi, email, parol, ism, telefon, rol, faol) 
            VALUES 
            ('admin', 'admin@example.com', :pass, 'Admin', '+998901234567', 'super_admin', 1)");
        $stmt->execute(['pass' => $defaultPassword]);
    }
} catch (PDOException $e) {
    die("Admin yaratishda xato: " . $e->getMessage());
}

// 2️⃣ Agar allaqachon login bo'lsa -> dashboard.php ga o'tkazamiz
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// 3️⃣ Login formdan kelgan ma'lumotni tekshirish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM adminlar WHERE foydalanuvchi_nomi = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() === 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $admin['parol'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['foydalanuvchi_nomi'];
                $_SESSION['admin_role'] = $admin['rol'];
                
                // Oxirgi kirish vaqtini yangilash
                $updateStmt = $conn->prepare("UPDATE adminlar SET oxirgi_kirish = NOW() WHERE id = :id");
                $updateStmt->bindParam(':id', $admin['id']);
                $updateStmt->execute();
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "❌ Username yoki parol noto'g'ri";
            }
        } else {
            $error = "❌ Username yoki parol noto'g'ri";
        }
    } catch(PDOException $e) {
        $error = "Database xatosi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">🔑 Admin Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Foydalanuvchi nomi</label>
                    <input type="text" class="form-control" id="username" name="username"  required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Parol</label>
                    <input type="password" class="form-control" id="password" name="password"  required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Kirish</button>
            </form>
        </div>
    </div>
</body>
</html>
