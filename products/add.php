<?php
require_once '../config.php';checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $category_id = sanitize($_POST['category_id']);
    $price = sanitize($_POST['price']);
    $discount_price = sanitize($_POST['discount_price']);
    $unit = sanitize($_POST['unit']);
    $min_order = sanitize($_POST['min_order']);
    $sku = sanitize($_POST['sku']);
    $barcode = sanitize($_POST['barcode']);
    $short_desc = sanitize($_POST['short_desc']);
    $description = sanitize($_POST['description']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $best_seller = isset($_POST['best_seller']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    
    try {
        $conn->beginTransaction();
        
        // Insert product
        $stmt = $conn->prepare("
            INSERT INTO mahsulotlar (
                nomi, slug, kategoriya_id, asosiy_narx, chegirma_narxi, olchov_birligi, 
                minimal_buyurtma, sku, shtrix_kodi, qisqa_tavsif, tavsif, 
                afzallikli, eng_sotilgan, yangi_kelgan, faol
            ) VALUES (
                :name, :slug, :category_id, :price, :discount_price, :unit, 
                :min_order, :sku, :barcode, :short_desc, :description, 
                :featured, :best_seller, :new_arrival, :active
            )
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':discount_price', $discount_price);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':min_order', $min_order);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':barcode', $barcode);
        $stmt->bindParam(':short_desc', $short_desc);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':best_seller', $best_seller);
        $stmt->bindParam(':new_arrival', $new_arrival);
        $stmt->bindParam(':active', $active);
        
        $stmt->execute();
        $product_id = $conn->lastInsertId();
        
        // Handle image upload
        if (!empty($_FILES['main_image']['name'])) {
            $uploadDir = '../../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['main_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
                $imageUrl = 'uploads/products/' . $fileName;
                
                $imgStmt = $conn->prepare("
                    INSERT INTO mahsulot_rasmlari (mahsulot_id, rasm_url, asosiy_rasm)
                    VALUES (:product_id, :image_url, 1)
                ");
                $imgStmt->bindParam(':product_id', $product_id);
                $imgStmt->bindParam(':image_url', $imageUrl);
                $imgStmt->execute();
            }
        }
        
        // Handle additional images
        if (!empty($_FILES['additional_images']['name'][0])) {
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . basename($_FILES['additional_images']['name'][$key]);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imageUrl = 'uploads/products/' . $fileName;
                        
                        $imgStmt = $conn->prepare("
                            INSERT INTO mahsulot_rasmlari (mahsulot_id, rasm_url)
                            VALUES (:product_id, :image_url)
                        ");
                        $imgStmt->bindParam(':product_id', $product_id);
                        $imgStmt->bindParam(':image_url', $imageUrl);
                        $imgStmt->execute();
                    }
                }
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Product added successfully!";
        redirect('index.php');
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get categories for dropdown
$categories = $conn->query("SELECT id, nomi FROM kategoriyalar ORDER BY nomi")->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Mahsulot qo'shish</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label required-field">Mahsulot nomi</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label required-field">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" required>
                        <small class="text-muted">Ismning URL uchun mos versiyasi (masalan, “mening mahsulotim”)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Tavsif</label>
                        <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="short_desc" class="form-label">Qisqa tavsif</label>
                        <textarea class="form-control" id="short_desc" name="short_desc" rows="2"></textarea>
                        <small class="text-muted">Mahsulotlar ro'yxatida paydo bo'ladi</small>
                    </div>
                </div>
                
                <div class="col-md-4">
    <div class="card mb-4">
        <div class="card-header">
            Mahsulot ma'lumotlari
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="category_id" class="form-label required-field">Kategoriya</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Kategoriyani tanlang</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= $category['nomi'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="price" class="form-label required-field">Narx</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            
            <div class="mb-3">
                <label for="discount_price" class="form-label">Chegirma narxi</label>
                <input type="number" step="0.01" class="form-control" id="discount_price" name="discount_price">
            </div>
            
            <div class="mb-3">
                <label for="unit" class="form-label required-field">O‘lchov birligi</label>
                <select class="form-select" id="unit" name="unit" required>
                    <option value="dona">Dona</option>
                    <option value="kg">Kilogramm</option>
                    <option value="metr">Metr</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="min_order" class="form-label">Minimal buyurtma</label>
                <input type="number" class="form-control" id="min_order" name="min_order" value="1" min="1">
            </div>
            
            <div class="mb-3">
                <label for="sku" class="form-label">SKU (Mahsulot kodi)</label>
                <input type="text" class="form-control" id="sku" name="sku">
            </div>
            
            <div class="mb-3">
                <label for="barcode" class="form-label">Shtrix kod</label>
                <input type="text" class="form-control" id="barcode" name="barcode">
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            Mahsulot holati
        </div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="featured" name="featured">
                <label class="form-check-label" for="featured">Tanlangan mahsulot</label>
            </div>
            
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="best_seller" name="best_seller">
                <label class="form-check-label" for="best_seller">Eng ko‘p sotilgan</label>
            </div>
            
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="new_arrival" name="new_arrival" checked>
                <label class="form-check-label" for="new_arrival">Yangi kelgan</label>
            </div>
            
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                <label class="form-check-label" for="active">Faol</label>
            </div>
        </div>
    </div>
</div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                Mahsulot rasmlari
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="main_image" class="form-label required-field">Asosiy rasm</label>
                    <input type="file" class="form-control" id="main_image" name="main_image" required>
                    <small class="text-muted">Bu asosiy ko‘rsatiladigan rasm bo‘ladi</small>
                </div>
                
                <div class="mb-3">
                    <label for="additional_images" class="form-label">Qo‘shimcha rasmlar</label>
                    <input type="file" class="form-control" id="additional_images" name="additional_images[]" multiple>
                    <small class="text-muted">Bir nechta rasm tanlash uchun CTRL tugmasini bosing</small>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">Mahsulotni saqlash</button>
<a href="index.php" class="btn btn-secondary">Bekor qilish</a>
</form>
</div>
</div>

<script>
    // Generate slug from product name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove non-word chars
            .replace(/\s+/g, '-')     // Replace spaces with -
            .replace(/--+/g, '-')      // Replace multiple - with single -
            .trim();
        document.getElementById('slug').value = slug;
    });
</script>

<?php
require_once '../footer.php';