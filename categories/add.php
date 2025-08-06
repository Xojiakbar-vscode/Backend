<?php
require_once '../config.php';checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $parent_id = !empty($_POST['parent_id']) ? sanitize($_POST['parent_id']) : NULL;
    $description = sanitize($_POST['description']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $order = sanitize($_POST['order']);
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO kategoriyalar (nomi, slug, ota_kategoriya_id, tavsif, afzallikli, korinish_tartibi)
            VALUES (:name, :slug, :parent_id, :description, :featured, :order)
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':order', $order);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Kategoriya muvaffaqiyatli qo‘shildi!";
        redirect('index.php');
    } catch(PDOException $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
}

// Ota kategoriyalar ro‘yxati (pastdagi "ota kategoriya" tanlash uchun)
$parent_categories = $conn->query("SELECT id, nomi FROM kategoriyalar WHERE ota_kategoriya_id IS NULL ORDER BY nomi")->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Kategoriya qo‘shish</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Kategoriya nomi</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label required-field">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" required>
                        <small class="text-muted">URL-ga mos ko‘rinish (masalan: 'mening-kategoriyam')</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            Kategoriya ma’lumotlari
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Ota kategoriya</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">Ota kategoriya yo‘q</option>
                                    <?php foreach ($parent_categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= $category['nomi'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order" class="form-label">Ko‘rinish tartibi</label>
                                <input type="number" class="form-control" id="order" name="order" value="0">
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                <label class="form-check-label" for="featured">Afzallikli kategoriya</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Kategoriyani saqlash</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<script>
    // Kategoriya nomidan slug hosil qilish
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Harf va raqam bo‘lmagan belgilarni olib tashlash
            .replace(/\s+/g, '-')     // Bo‘sh joylarni '-' bilan almashtirish
            .replace(/--+/g, '-')      // Bir nechta '-' ni bitta '-' qilish
            .trim();
        document.getElementById('slug').value = slug;
    });
</script>

<?php
require_once '../footer.php';
?>
