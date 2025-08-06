<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$method_id = sanitize($_GET['id']);

// Yetkazib berish usulini olish
$stmt = $conn->prepare("SELECT * FROM yetkazish_usullari WHERE id = :id");
$stmt->bindParam(':id', $method_id);
$stmt->execute();
$method = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$method) {
    $_SESSION['error_message'] = "Yetkazib berish usuli topilmadi!";
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $free_threshold = !empty($_POST['free_threshold']) ? sanitize($_POST['free_threshold']) : NULL;
    $delivery_time = sanitize($_POST['delivery_time']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("
            UPDATE yetkazish_usullari SET
                nomi = :name,
                tavsif = :description,
                narxi = :price,
                bepul_yetkazish_chegara = :free_threshold,
                yetkazish_muddati = :delivery_time,
                faol = :active
            WHERE id = :id
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':free_threshold', $free_threshold);
        $stmt->bindParam(':delivery_time', $delivery_time);
        $stmt->bindParam(':active', $active);
        $stmt->bindParam(':id', $method_id);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Yetkazib berish usuli muvaffaqiyatli yangilandi!";
        redirect('index.php');
    } catch (PDOException $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
}

require_once '../header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Yetkazib berish usulini tahrirlash</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Usullar ro‘yxatiga qaytish</a>
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
                        <input type="text" class="form-control" id="name" name="name" value="<?= $method['nomi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $method['tavsif'] ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label required-field">Narxi</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $method['narxi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="free_threshold" class="form-label">Bepul yetkazish chegarasi</label>
                        <input type="number" step="0.01" class="form-control" id="free_threshold" name="free_threshold" value="<?= $method['bepul_yetkazish_chegara'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="delivery_time" class="form-label required-field">Yetkazish muddati</label>
                        <input type="text" class="form-control" id="delivery_time" name="delivery_time" value="<?= $method['yetkazish_muddati'] ?>" required>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" <?= $method['faol'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Yangilash</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';
