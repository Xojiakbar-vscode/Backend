<?php
require_once '../config.php';checkLogin();

// Only super admin can access this page
if ($_SESSION['admin_role'] === 'super_admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page!";
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $role = sanitize($_POST['role']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    try {
        // Validate passwords
        if ($password !== $confirm_password) {
            throw new Exception("Passwords don't match!");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters!");
        }
        
        // Check if username or email already exists
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM adminlar 
            WHERE foydalanuvchi_nomi = :username OR email = :email
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username or email already exists!");
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin
        $stmt = $conn->prepare("
            INSERT INTO adminlar (
                foydalanuvchi_nomi, email, parol, ism, telefon, rol, faol
            ) VALUES (
                :username, :email, :password, :name, :phone, :role, :active
            )
        ");
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':active', $active);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Admin user created successfully!";
        redirect('index.php');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once '../header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-plus"></i>Admin foydalanuvchisini qo'shish</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label required-field">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label required-field">Parol</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label required-field">Parolni qayta kiriting</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Ism Familiya</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required-field">Telefon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label required-field">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="kontent_menejeri">Kontent menejeri</option>
                            <option value="buyurtma_menejeri">Buyurtma menejeri</option>
                            <option value="mijoz_xizmati">Mijozlarga xizmat ko'rsatish</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Admin qo'shish</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';