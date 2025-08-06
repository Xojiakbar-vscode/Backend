<?php
require_once '../config.php';checkLogin();

// Only super admin can access this page
if ($_SESSION['admin_role'] === 'super_admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page!";
    redirect('dashboard.php');
}

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$admin_id = sanitize($_GET['id']);

// Get admin data
$stmt = $conn->prepare("SELECT * FROM adminlar WHERE id = :id");
$stmt->bindParam(':id', $admin_id);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $_SESSION['error_message'] = "Admin user not found!";
    redirect('index.php');
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
        // Validate passwords if provided
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                throw new Exception("Passwords don't match!");
            }
            
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters!");
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $password_hash = $admin['parol'];
        }
        
        // Check if username or email already exists (excluding current admin)
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM adminlar 
            WHERE (foydalanuvchi_nomi = :username OR email = :email)
            AND id != :admin_id
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username or email already exists!");
        }
        
        // Update admin
        $stmt = $conn->prepare("
            UPDATE adminlar SET
                foydalanuvchi_nomi = :username,
                email = :email,
                parol = :password,
                ism = :name,
                telefon = :phone,
                rol = :role,
                faol = :active
            WHERE id = :admin_id
        ");
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':active', $active);
        $stmt->bindParam(':admin_id', $admin_id);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Admin user updated successfully!";
        redirect('index.php');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once '../header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Adminni tahrirlash</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Admin sahifasiga qaytish</a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label required-field">Foydalanuvchi_nomi</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $admin['foydalanuvchi_nomi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $admin['email'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Yangi parol</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Joriy parolni saqlash uchun bo'sh qoldiring</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Qayta kiriting yangi parolni</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">To'liq ism</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $admin['ism'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required-field">Telefon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= $admin['telefon'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label required-field">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="kontent_menejeri" <?= $admin['rol'] == 'kontent_menejeri' ? 'selected' : '' ?>>Kontent menejeri</option>
                            <option value="buyurtma_menejeri" <?= $admin['rol'] == 'buyurtma_menejeri' ? 'selected' : '' ?>>Buyurtna Manageri</option>
                            <option value="mijoz_xizmati" <?= $admin['rol'] == 'mijoz_xizmati' ? 'selected' : '' ?>>MIzozga xizmat korsatish</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" <?= $admin['faol'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Adminni yangilash</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';