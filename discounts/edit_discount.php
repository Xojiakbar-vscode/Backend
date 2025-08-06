<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$discount_id = sanitize($_GET['id']);

// Get discount data
$stmt = $conn->prepare("SELECT * FROM chegirmalar WHERE id = :id");
$stmt->bindParam(':id', $discount_id);
$stmt->execute();
$discount = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$discount) {
    $_SESSION['error_message'] = "Discount not found!";
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $discount_type = sanitize($_POST['discount_type']);
    $discount_value = sanitize($_POST['discount_value']);
    $code = sanitize($_POST['code']);
    $min_order = sanitize($_POST['min_order']);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $max_uses = sanitize($_POST['max_uses']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    try {
        $conn->beginTransaction();
        
        // Update discount
        $stmt = $conn->prepare("
            UPDATE chegirmalar SET
                nomi = :name,
                tavsif = :description,
                chegirma_turi = :discount_type,
                chegirma_qiymati = :discount_value,
                kodi = :code,
                minimal_buyurtma_summa = :min_order,
                boshlanish_vaqti = :start_date,
                tugash_vaqti = :end_date,
                maksimal_foydalanish = :max_uses,
                faol = :active
            WHERE id = :id
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':discount_type', $discount_type);
        $stmt->bindParam(':discount_value', $discount_value);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':min_order', $min_order);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':max_uses', $max_uses);
        $stmt->bindParam(':active', $active);
        $stmt->bindParam(':id', $discount_id);
        
        $stmt->execute();
        
        // Update product assignments
        $conn->prepare("DELETE FROM mahsulot_chegirmalari WHERE chegirma_id = :discount_id")
             ->execute([':discount_id' => $discount_id]);
        
        if (!empty($_POST['products'])) {
            foreach ($_POST['products'] as $product_id) {
                $product_id = sanitize($product_id);
                $stmt = $conn->prepare("
                    INSERT INTO mahsulot_chegirmalari (mahsulot_id, chegirma_id)
                    VALUES (:product_id, :discount_id)
                ");
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':discount_id', $discount_id);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Discount updated successfully!";
        redirect('index.php');
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get all products
$products = $conn->query("SELECT id, nomi FROM mahsulotlar WHERE faol = 1 ORDER BY nomi")->fetchAll(PDO::FETCH_ASSOC);

// Get products this discount applies to
$applied_products = $conn->prepare("
    SELECT mahsulot_id FROM mahsulot_chegirmalari 
    WHERE chegirma_id = :discount_id
");
$applied_products->bindParam(':discount_id', $discount_id);
$applied_products->execute();
$applied_product_ids = $applied_products->fetchAll(PDO::FETCH_COLUMN);

require_once '../header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Chegirmani tahrirlash</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Discounts</a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Chegirma nomi</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $discount['nomi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $discount['tavsif'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_type" class="form-label required-field">Chegirma turi</label>
                        <select class="form-select" id="discount_type" name="discount_type" required>
                            <option value="foiz" <?= $discount['chegirma_turi'] == 'foiz' ? 'selected' : '' ?>>Foiz</option>
                            <option value="belgilangan_summa" <?= $discount['chegirma_turi'] == 'belgilangan_summa' ? 'selected' : '' ?>>Belgilangan summa</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_value" class="form-label required-field">Chegirma qiymati</label>
                        <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" value="<?= $discount['chegirma_qiymati'] ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label required-field">Chegirma Kodi</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= $discount['kodi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="min_order" class="form-label">Minimal buyurtma summasi</label>
                        <input type="number" step="0.01" class="form-control" id="min_order" name="min_order" value="<?= $discount['minimal_buyurtma_summa'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label required-field">Boshlanadigan kun</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d\TH:i', strtotime($discount['boshlanish_vaqti'])) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label required-field">Tugash Sanasi</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= date('Y-m-d\TH:i', strtotime($discount['tugash_vaqti'])) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_uses" class="form-label">Eng kop foydalanish</label>
                        <input type="number" class="form-control" id="max_uses" name="max_uses" value="<?= $discount['maksimal_foydalanish'] ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" <?= $discount['faol'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    Mahsulotlarga murojaat qiling
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Mahsulot tanlash</label>
                        <select class="form-select" id="products" name="products[]" multiple size="5">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= in_array($product['id'], $applied_product_ids) ? 'Tanlangan' : '' ?>>
                                    <?= $product['nomi'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Bir nechta mahsulotni tanlash uchun CTRL tugmasini bosib turing. Barcha mahsulotlarga qo'llash uchun bo'sh qoldiring.</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Chegirmani yangilash</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';