<?php
require_once '../config.php';checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $free_threshold = !empty($_POST['free_threshold']) ? sanitize($_POST['free_threshold']) : NULL;
    $delivery_time = sanitize($_POST['delivery_time']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO yetkazish_usullari (
                nomi, tavsif, narxi, bepul_yetkazish_chegara, yetkazish_muddati, faol
            ) VALUES (
                :name, :description, :price, :free_threshold, :delivery_time, :active
            )
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':free_threshold', $free_threshold);
        $stmt->bindParam(':delivery_time', $delivery_time);
        $stmt->bindParam(':active', $active);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Yetkazib berish usuli muvaffaqiyatli qo‘shildi!";
        redirect('index.php');
    } catch (PDOException $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
}

require_once '../header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Yetkazib berish usulini qo‘shish</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Nomi</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label required-field">Narxi</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="free_threshold" class="form-label">Bepul yetkazib berish chegarasi</label>
                        <input type="number" step="0.01" class="form-control" id="free_threshold" name="free_threshold">
                        <small class="text-muted">Agar bepul yetkazib berish bo‘lmasa, bo‘sh qoldiring</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="delivery_time" class="form-label required-field">Yetkazish muddati</label>
                        <input type="text" class="form-control" id="delivery_time" name="delivery_time" required>
                        <small class="text-muted">Masalan: 3-5 ish kuni</small>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Qo‘shish</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';
