<?php
require_once 'config.php';
;checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $current_password = sanitize($_POST['current_password']);
    $new_password = sanitize($_POST['new_password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    try {
        // Get current admin data
        $stmt = $conn->prepare("SELECT * FROM adminlar WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Validate current password if changing password
        if (!empty($new_password)) {
            if (!password_verify($current_password, $admin['parol'])) {
                throw new Exception("Current password is incorrect");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords don't match");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters");
            }
            
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $password_hash = $admin['parol'];
        }
        
        // Update admin profile
        $stmt = $conn->prepare("
            UPDATE adminlar SET
                ism = :name,
                email = :email,
                telefon = :phone,
                parol = :password
            WHERE id = :id
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        
        // Update session data
        $_SESSION['admin_username'] = $admin['foydalanuvchi_nomi'];
        
        $_SESSION['success_message'] = "Profile updated successfully!";
        redirect('profile.php');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current admin data
$stmt = $conn->prepare("SELECT * FROM adminlar WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-circle"></i> My Profile</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= $admin['foydalanuvchi_nomi'] ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $admin['ism'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $admin['email'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required-field">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= $admin['telefon'] ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Change Password
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<?php
require_once 'footer.php';