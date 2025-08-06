<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$review_id = sanitize($_GET['id']);

// Get review data
$stmt = $conn->prepare("
    SELECT r.*, m.nomi as mahsulot_nomi,
           CONCAT(f.ism, ' ', f.familiya) as mijoz_nomi
    FROM mahsulot_sharhlari r
    JOIN mahsulotlar m ON r.mahsulot_id = m.id
    JOIN foydalanuvchilar f ON r.foydalanuvchi_id = f.id
    WHERE r.id = :review_id
");
$stmt->bindParam(':review_id', $review_id);
$stmt->execute();
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    $_SESSION['error_message'] = "Review not found!";
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $approved = isset($_POST['approved']) ? 1 : 0;
    $admin_reply = sanitize($_POST['admin_reply']);
    
    try {
        $stmt = $conn->prepare("
            UPDATE mahsulot_sharhlari SET
                tasdiqlangan = :approved,
                admin_javobi = :admin_reply,
                javob_vaqti = NOW()
            WHERE id = :review_id
        ");
        
        $stmt->bindParam(':approved', $approved);
        $stmt->bindParam(':admin_reply', $admin_reply);
        $stmt->bindParam(':review_id', $review_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Review updated successfully!";
        redirect('view.php?id=' . $review_id);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get review images
$images = $conn->prepare("SELECT * FROM sharh_rasmlari WHERE sharh_id = :review_id");
$images->bindParam(':review_id', $review_id);
$images->execute();
$review_images = $images->fetchAll(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-star"></i> Review Details</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Reviews</a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            Review Information
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Product:</th>
                                    <td><?= $review['mahsulot_nomi'] ?></td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td><?= $review['mijoz_nomi'] ?></td>
                                </tr>
                                <tr>
                                    <th>Rating:</th>
                                    <td>
                                        <div class="rating">
                                            <?= str_repeat('<i class="bi bi-star-fill text-warning"></i>', $review['reyting']) ?>
                                            <?= str_repeat('<i class="bi bi-star text-secondary"></i>', 5 - $review['reyting']) ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Title:</th>
                                    <td><?= $review['sarlavha'] ?></td>
                                </tr>
                                <tr>
                                    <th>Review Date:</th>
                                    <td><?= date('M d, Y H:i', strtotime($review['yaratilgan_vaqt'])) ?></td>
                                </tr>
                            </table>
                            
                            <div class="mb-3">
                                <label class="form-label">Review Content</label>
                                <div class="border p-3 bg-light rounded">
                                    <?= nl2br($review['izoh']) ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($review_images)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Review Images</label>
                                    <div class="row">
                                        <?php foreach ($review_images as $image): ?>
                                            <div class="col-md-3 mb-3">
                                                <img src="<?= '../../' . $image['rasm_url'] ?>" alt="Review image" class="img-thumbnail">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            Admin Response
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="admin_reply" class="form-label">Your Response</label>
                                <textarea class="form-control" id="admin_reply" name="admin_reply" rows="3"><?= $review['admin_javobi'] ?></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="approved" name="approved" <?= $review['tasdiqlangan'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="approved">Approve this review</label>
                            </div>
                            
                            <?php if ($review['javob_vaqti']): ?>
                                <div class="alert alert-info">
                                    <strong>Last Response:</strong> <?= date('M d, Y H:i', strtotime($review['javob_vaqti'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Order Information
                        </div>
                        <div class="card-body">
                            <?php if ($review['buyurtma_id']): ?>
                                <?php
                                $order = $conn->prepare("
                                    SELECT buyurtma_raqami, yaratilgan_vaqt 
                                    FROM buyurtmalar 
                                    WHERE id = :order_id
                                ");
                                $order->bindParam(':order_id', $review['buyurtma_id']);
                                $order->execute();
                                $order_info = $order->fetch(PDO::FETCH_ASSOC);
                                ?>
                                
                                <table class="table table-sm">
                                    <tr>
                                        <th>Order #:</th>
                                        <td><?= $order_info['buyurtma_raqami'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Order Date:</th>
                                        <td><?= date('M d, Y', strtotime($order_info['yaratilgan_vaqt'])) ?></td>
                                    </tr>
                                </table>
                                
                                <a href="../orders/view.php?id=<?= $review['buyurtma_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    View Order Details
                                </a>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    This review is not associated with an order.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once '../footer.php';