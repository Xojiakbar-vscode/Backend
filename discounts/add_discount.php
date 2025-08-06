<?php
require_once '../config.php';checkLogin();

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
        $stmt = $conn->prepare("
            INSERT INTO chegirmalar (
                nomi, tavsif, chegirma_turi, chegirma_qiymati, kodi,
                minimal_buyurtma_summa, boshlanish_vaqti, tugash_vaqti,
                maksimal_foydalanish, faol
            ) VALUES (
                :name, :description, :discount_type, :discount_value, :code,
                :min_order, :start_date, :end_date, :max_uses, :active
            )
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
        
        $stmt->execute();
        
        $discount_id = $conn->lastInsertId();
        
        // Tanlangan mahsulotlarga chegirma biriktirish
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
        
        $_SESSION['success_message'] = "Chegirma muvaffaqiyatli qo‘shildi!";
        redirect('index.php');
        
    } catch (PDOException $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
}

// Aktiv mahsulotlarni olish
$products = $conn->query("SELECT id, nomi FROM mahsulotlar WHERE faol = 1 ORDER BY nomi")->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-percent"></i> Chegirma qo‘shish</h5>
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
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_type" class="form-label required-field">Chegirma turi</label>
                        <select class="form-select" id="discount_type" name="discount_type" required>
                            <option value="foiz">Foiz</option>
                            <option value="belgilangan_summa">Belgilangan summa</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_value" class="form-label required-field">Chegirma qiymati</label>
                        <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label required-field">Chegirma kodi</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                        <small class="text-muted">Mijozlar chegirmadan foydalanish uchun shu kodni kiritadi</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="min_order" class="form-label">Minimal buyurtma summasi</label>
                        <input type="number" step="0.01" class="form-control" id="min_order" name="min_order">
                        <small class="text-muted">Agar cheklov bo‘lmasa bo‘sh qoldiring</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label required-field">Boshlanish sanasi</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label required-field">Tugash sanasi</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_uses" class="form-label">Maksimal foydalanish soni</label>
                        <input type="number" class="form-control" id="max_uses" name="max_uses">
                        <small class="text-muted">Cheklanmagan bo‘lsa bo‘sh qoldiring</small>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                        <label class="form-check-label" for="active">Faol</label>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    Mahsulotlarga qo‘llash
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Mahsulotlarni tanlang</label>
                        <select class="form-select" id="products" name="products[]" multiple size="5">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= $product['nomi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">CTRL bosib bir nechta mahsulot tanlash mumkin. Agar hammasiga qo‘llanilsin desangiz bo‘sh qoldiring.</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Chegirma yaratish</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<script>
    // Sana maydonlarini avtomatik to‘ldirish
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const startDate = now.toISOString().slice(0, 16);
        
        now.setDate(now.getDate() + 7); // 7 kun qo‘shish
        const endDate = now.toISOString().slice(0, 16);
        
        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;
    });
</script>

<?php
require_once '../footer.php';
