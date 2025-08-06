<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['product_id'])) {
    redirect('index.php');
}

$product_id = sanitize($_GET['product_id']);

// Get product info
$stmt = $conn->prepare("SELECT id, nomi FROM mahsulotlar WHERE id = :product_id");
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error_message'] = "Product not found!";
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $color = sanitize($_POST['color']);
    $color_code = sanitize($_POST['color_code']);
    $size = sanitize($_POST['size']);
    $material = sanitize($_POST['material']);
    $pattern = sanitize($_POST['pattern']);
    $price_adjustment = sanitize($_POST['price_adjustment']);
    $stock = sanitize($_POST['stock']);
    $sku = sanitize($_POST['sku']);
    $barcode = sanitize($_POST['barcode']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    try {
        $conn->beginTransaction();
        
        // If this is set as default, remove default status from other variants
        if ($is_default) {
            $stmt = $conn->prepare("
                UPDATE mahsulot_variantlari 
                SET standart = 0 
                WHERE mahsulot_id = :product_id
            ");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
        }
        
        // Insert new variant
        $stmt = $conn->prepare("
            INSERT INTO mahsulot_variantlari (
                mahsulot_id, rang, rang_kodi, olcham, material, naqsh,
                narx_ozgartirish, qoldiq_soni, sku, shtrix_kodi, standart
            ) VALUES (
                :product_id, :color, :color_code, :size, :material, :pattern,
                :price_adjustment, :stock, :sku, :barcode, :is_default
            )
        ");
        
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':color_code', $color_code);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':pattern', $pattern);
        $stmt->bindParam(':price_adjustment', $price_adjustment);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':barcode', $barcode);
        $stmt->bindParam(':is_default', $is_default);
        
        $stmt->execute();
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $variant_id = $conn->lastInsertId();
            $uploadDir = '../../uploads/products/variants/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageUrl = 'uploads/products/variants/' . $fileName;
                
                $updateStmt = $conn->prepare("
                    UPDATE mahsulot_variantlari 
                    SET rasm_url = :image_url 
                    WHERE id = :variant_id
                ");
                $updateStmt->bindParam(':image_url', $imageUrl);
                $updateStmt->bindParam(':variant_id', $variant_id);
                $updateStmt->execute();
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Variant added successfully!";
        redirect("variants.php?product_id=$product_id");
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get product variants
$variants = $conn->prepare("
    SELECT * FROM mahsulot_variantlari 
    WHERE mahsulot_id = :product_id 
    ORDER BY standart DESC, id
");
$variants->bindParam(':product_id', $product_id);
$variants->execute();
$product_variants = $variants->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-box-seam"></i> Variants for: <?= $product['nomi'] ?>
        </h5>
        <div>
            <a href="index.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                <i class="bi bi-plus-circle"></i> Add Variant
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (empty($product_variants)): ?>
            <div class="alert alert-info">No variants found for this product.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Material</th>
                            <th>Price Adj.</th>
                            <th>Stock</th>
                            <th>SKU</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_variants as $variant): ?>
                            <tr>
                                <td><?= $variant['id'] ?></td>
                                <td>
                                    <?php if ($variant['rasm_url']): ?>
                                        <img src="<?= '../../' . $variant['rasm_url'] ?>" alt="Variant image" class="img-thumbnail" style="max-width: 60px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($variant['rang']): ?>
                                        <span class="d-inline-block me-1" style="width: 15px; height: 15px; background-color: <?= $variant['rang_kodi'] ?>; border: 1px solid #ddd;"></span>
                                        <?= $variant['rang'] ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= $variant['olcham'] ?: '-' ?></td>
                                <td><?= $variant['material'] ?: '-' ?></td>
                                <td><?= $variant['narx_ozgartirish'] ? '$' . number_format($variant['narx_ozgartirish'], 2) : '-' ?></td>
                                <td><?= $variant['qoldiq_soni'] ?></td>
                                <td><?= $variant['sku'] ?: '-' ?></td>
                                <td>
                                    <?php if ($variant['standart']): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editVariantModal<?= $variant['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="delete_variant.php?product_id=<?= $product_id ?>&variant_id=<?= $variant['id'] ?>" 
                                       class="btn btn-sm btn-danger delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Edit Variant Modal -->
                            <div class="modal fade" id="editVariantModal<?= $variant['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Variant</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="update_variant.php" enctype="multipart/form-data">
                                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                            <input type="hidden" name="variant_id" value="<?= $variant['id'] ?>">
                                            
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Color</label>
                                                            <input type="text" class="form-control" name="color" value="<?= $variant['rang'] ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Color Code</label>
                                                            <input type="color" class="form-control form-control-color" name="color_code" value="<?= $variant['rang_kodi'] ?: '#ffffff' ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Size</label>
                                                            <input type="text" class="form-control" name="size" value="<?= $variant['olcham'] ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Material</label>
                                                            <input type="text" class="form-control" name="material" value="<?= $variant['material'] ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Pattern</label>
                                                            <input type="text" class="form-control" name="pattern" value="<?= $variant['naqsh'] ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Price Adjustment</label>
                                                            <input type="number" step="0.01" class="form-control" name="price_adjustment" value="<?= $variant['narx_ozgartirish'] ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Stock Quantity</label>
                                                            <input type="number" class="form-control" name="stock" value="<?= $variant['qoldiq_soni'] ?>">
                                                        </div>
                                                        
                                                        <div class="form-check mb-3">
                                                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default<?= $variant['id'] ?>" <?= $variant['standart'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="is_default<?= $variant['id'] ?>">Default Variant</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">SKU</label>
                                                            <input type="text" class="form-control" name="sku" value="<?= $variant['sku'] ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Barcode</label>
                                                            <input type="text" class="form-control" name="barcode" value="<?= $variant['shtrix_kodi'] ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Variant Image</label>
                                                    <input type="file" class="form-control" name="image">
                                                    
                                                    <?php if ($variant['rasm_url']): ?>
                                                        <div class="mt-2">
                                                            <img src="<?= '../../' . $variant['rasm_url'] ?>" alt="Current image" class="img-thumbnail" style="max-width: 150px;">
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image<?= $variant['id'] ?>">
                                                                <label class="form-check-label" for="remove_image<?= $variant['id'] ?>">Remove current image</label>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Variant Modal -->
<div class="modal fade" id="addVariantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Color Code</label>
                                <input type="color" class="form-control form-control-color" name="color_code" value="#ffffff">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Size</label>
                                <input type="text" class="form-control" name="size">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Material</label>
                                <input type="text" class="form-control" name="material">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Pattern</label>
                                <input type="text" class="form-control" name="pattern">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price Adjustment</label>
                                <input type="number" step="0.01" class="form-control" name="price_adjustment" value="0.00">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock" value="0">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_default" id="is_default_new">
                                <label class="form-check-label" for="is_default_new">Default Variant</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Variant Image</label>
                        <input type="file" class="form-control" name="image">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';