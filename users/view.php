<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$user_id = sanitize($_GET['id']);

// Get user data
$stmt = $conn->prepare("
    SELECT id, ism, familiya, email, telefon, rasm_url, manzil, shahar, pochta_indeksi, mamlakat,
           email_tasdiqlangan, telefon_tasdiqlangan, faol, oxirgi_kirish, yaratilgan_vaqt
    FROM foydalanuvchilar
    WHERE id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "User not found!";
    redirect('index.php');
}

// Get user orders count
$orders_count = $conn->prepare("SELECT COUNT(*) FROM buyurtmalar WHERE foydalanuvchi_id = :user_id");
$orders_count->bindParam(':user_id', $user_id);
$orders_count->execute();
$total_orders = $orders_count->fetchColumn();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person"></i> User Details</h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Users</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <?php if ($user['rasm_url']): ?>
                            <img src="<?= '../../' . $user['rasm_url'] ?>" alt="User avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; margin: 0 auto;">
                                <i class="bi bi-person" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4><?= $user['ism'] ?> <?= $user['familiya'] ?></h4>
                        
                        <div class="d-flex justify-content-center mb-3">
                            <span class="badge bg-<?= $user['faol'] ? 'success' : 'secondary' ?> me-2">
                                <?= $user['faol'] ? 'Active' : 'Inactive' ?>
                            </span>
                            
                            <?php if ($user['email_tasdiqlangan']): ?>
                                <span class="badge bg-success me-2" title="Email Verified">
                                    <i class="bi bi-envelope-check"></i>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning me-2" title="Email Not Verified">
                                    <i class="bi bi-envelope-exclamation"></i>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($user['telefon_tasdiqlangan']): ?>
                                <span class="badge bg-success" title="Phone Verified">
                                    <i class="bi bi-telephone-check"></i>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning" title="Phone Not Verified">
                                    <i class="bi bi-telephone-exclamation"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-envelope"></i> Send Email
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-chat"></i> Send Message
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        User Stats
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Orders:</th>
                                <td><?= $total_orders ?></td>
                            </tr>
                            <tr>
                                <th>Last Login:</th>
                                <td><?= $user['oxirgi_kirish'] ? date('M d, Y H:i', strtotime($user['oxirgi_kirish'])) : 'Never' ?></td>
                            </tr>
                            <tr>
                                <th>Member Since:</th>
                                <td><?= date('M d, Y', strtotime($user['yaratilgan_vaqt'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        Personal Information
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>First Name:</th>
                                <td><?= $user['ism'] ?></td>
                            </tr>
                            <tr>
                                <th>Last Name:</th>
                                <td><?= $user['familiya'] ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>
                                    <?= $user['email'] ?>
                                    <?php if ($user['email_tasdiqlangan']): ?>
                                        <span class="badge bg-success ms-2">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-2">Not Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>
                                    <?= $user['telefon'] ?>
                                    <?php if ($user['telefon_tasdiqlangan']): ?>
                                        <span class="badge bg-success ms-2">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-2">Not Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        Address Information
                    </div>
                    <div class="card-body">
                        <address>
                            <?= $user['manzil'] ?><br>
                            <?= $user['shahar'] ?><br>
                            <?= $user['pochta_indeksi'] ?><br>
                            <?= $user['mamlakat'] ?>
                        </address>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Recent Orders
                    </div>
                    <div class="card-body">
                        <?php if ($total_orders > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $orders = $conn->prepare("
                                            SELECT id, buyurtma_raqami, yaratilgan_vaqt, holat, yakuniy_summa
                                            FROM buyurtmalar
                                            WHERE foydalanuvchi_id = :user_id
                                            ORDER BY yaratilgan_vaqt DESC
                                            LIMIT 5
                                        ");
                                        $orders->bindParam(':user_id', $user_id);
                                        $orders->execute();
                                        
                                        while ($order = $orders->fetch(PDO::FETCH_ASSOC)) {
                                            $statusClass = '';
                                            switch ($order['holat']) {
                                                case 'kutilyapti': $statusClass = 'secondary'; break;
                                                case 'jarayonda': $statusClass = 'info'; break;
                                                case 'yuborilgan': $statusClass = 'primary'; break;
                                                case 'yetkazilgan': $statusClass = 'success'; break;
                                                case 'bekor_qilingan': $statusClass = 'danger'; break;
                                                case 'qaytarilgan': $statusClass = 'warning'; break;
                                            }
                                            
                                            echo "<tr>
                                                <td>{$order['buyurtma_raqami']}</td>
                                                <td>" . date('M d, Y', strtotime($order['yaratilgan_vaqt'])) . "</td>
                                                <td><span class='badge bg-{$statusClass}'>" . ucfirst($order['holat']) . "</span></td>
                                                <td>$" . number_format($order['yakuniy_summa'], 2) . "</td>
                                                <td>
                                                    <a href='../orders/view.php?id={$order['id']}' class='btn btn-sm btn-outline-primary'><i class='bi bi-eye'></i></a>
                                                </td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_orders > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="../orders/index.php?user_id=<?= $user_id ?>" class="btn btn-sm btn-primary">
                                        View All Orders
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">This user hasn't placed any orders yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';