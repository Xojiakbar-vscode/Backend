<?php
require_once '../config.php';checkLogin();

// Agar ID kelmasa, kategoriyalar ro‘yxatiga qaytarish
if (!isset($_GET['id'])) {
    redirect('index.php');
}

$category_id = sanitize($_GET['id']);

// 🔹 Kategoriya ma’lumotlarini olish
$stmt = $conn->prepare("SELECT * FROM kategoriyalar WHERE id = :id");
$stmt->bindParam(':id', $category_id);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['error_message'] = "Kategoriya topilmadi!";
    redirect('index.php');
}

// 🔹 Forma yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $parent_id = !empty($_POST['parent_id']) ? sanitize($_POST['parent_id']) : NULL;
    $description = sanitize($_POST['description']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $order = sanitize($_POST['order']);
    
    try {
        $stmt = $conn->prepare("
            UPDATE kategoriyalar SET
                nomi = :name,
                slug = :slug,
                ota_kategoriya_id = :parent_id,
                tavsif = :description,
                afzallikli = :featured,
                korinish_tartibi = :order
            WHERE id = :id
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':order', $order);
        $stmt->bindParam(':id', $category_id);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Kategoriya muvaffaqiyatli yangilandi!";
        redirect('index.php');
    } catch(PDOException $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
}

// 🔹 Ota kategoriyalarni olish (hozirgi kategoriya va uning bolalarini chiqarib tashlash)
$parent_categories = $conn->query("
    SELECT id, nomi 
    FROM kategoriyalar 
    WHERE id != $category_id AND (ota_kategoriya_id IS NULL OR ota_kategoriya_id != $category_id)
    ORDER BY nomi
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Kategoriyani tahrirlash</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Kategoriyalar ro‘yxatiga qaytish</a>
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
                        <input type="text" class="form-control" id="name" name="name" value="<?= $category['nomi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label required-field">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= $category['slug'] ?>" required>
                        <small class="text-muted">URL uchun mos ko‘rinishi (masalan: 'mening-kategoriyam')</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $category['tavsif'] ?></textarea>
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
                                    <?php foreach ($parent_categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $category['ota_kategoriya_id'] ? 'selected' : '' ?>>
                                            <?= $cat['nomi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order" class="form-label">Ko‘rinish tartibi</label>
                                <input type="number" class="form-control" id="order" name="order" value="<?= $category['korinish_tartibi'] ?>">
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" <?= $category['afzallikli'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="featured">Afzallikli kategoriya</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Kategoriyani yangilash</button>
            <a href="index.php" class="btn btn-secondary">Bekor qilish</a>
        </form>
    </div>
</div>

<script>
    // 🔹 Kategoriya nomidan slug yaratish
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Harf va raqam bo‘lmagan belgilarni olib tashlash
            .replace(/\s+/g, '-')     // Bo‘sh joylarni - bilan almashtirish
            .replace(/--+/g, '-')      // Bir nechta - ni bitta - ga almashtirish
            .trim();
        document.getElementById('slug').value = slug;
    });
</script>

<?php
require_once '../footer.php';
