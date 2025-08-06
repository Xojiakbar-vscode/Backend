<?php
require_once '../config.php';checkLogin();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$order_id = sanitize($_GET['id']);

// Get order data
$stmt = $conn->prepare("
    SELECT b.*, f.ism, f.familiya, f.email, f.telefon, f.manzil, f.shahar, f.pochta_indeksi, f.mamlakat
    FROM buyurtmalar b
    JOIN foydalanuvchilar f ON b.foydalanuvchi_id = f.id
    WHERE b.id = :order_id
");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error_message'] = "Order not found!";
    redirect('index.php');
}

// Get order items
$items = $conn->prepare("
    SELECT oi.*, m.nomi as mahsulot_nomi
    FROM buyurtma_elementlari oi
    JOIN mahsulotlar m ON oi.mahsulot_id = m.id
    WHERE oi.buyurtma_id = :order_id
");
$items->bindParam(':order_id', $order_id);
$items->execute();
$order_items = $items->fetchAll(PDO::FETCH_ASSOC);

// Get shipping address
$shipping_address = json_decode($order['yetkazish_manzili'], true);

// Get billing address if exists
$billing_address = !empty($order['hisob_manzili']) ? json_decode($order['hisob_manzili'], true) : null;

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-cart"></i> Order #<?= $order['buyurtma_raqami'] ?></h5>
        <a href="index.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Buyrutmalarga qaytish</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Buyurtma tavsilotlari
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Buyurtma raqami</th>
                                <td><?= $order['buyurtma_raqami'] ?></td>
                            </tr>
                            <tr>
                                <th>Buyurtma Vaqti</th>
                                <td><?= date('M d, Y H:i', strtotime($order['yaratilgan_vaqt'])) ?></td>
                            </tr>
                            <tr>
                                <th>Buyurtma holati:</th>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['holat'] == 'kutilyapti' ? 'kutilyapti' : 
                                        ($order['holat'] == 'jarayonda' ? 'jarayonda' : 
                                        ($order['holat'] == 'yuborilgan' ? 'yuborilgan' : 
                                        ($order['holat'] == 'yetkazilgan' ? 'yetkazilgan' : 
                                        ($order['holat'] == 'bekor_qilingan' ? 'Xavfli' : 'ogohlantirish')))) ?>">
                                        <?= ucfirst($order['holat']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['tolov_holati'] == 'kutilyapti' ? 'secondary' : 
                                        ($order['tolov_holati'] == 'tolangan' ? 'success' : 
                                        ($order['tolov_holati'] == 'muvaffaqiyatsiz' ? 'danger' : 'warning')) ?>">
                                        <?= ucfirst($order['tolov_holati']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td><?= ucfirst($order['tolov_usuli']) ?></td>
                            </tr>
                            <tr>
                                <th>Customer Note:</th>
                                <td><?= $order['mijoz_izohi'] ?: '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        Customer Details
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td><?= $order['ism'] ?> <?= $order['familiya'] ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= $order['email'] ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= $order['telefon'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Shipping Address
                    </div>
                    <div class="card-body">
                        <address>
                            <?= $shipping_address['ism'] ?> <?= $shipping_address['familiya'] ?><br>
                            <?= $shipping_address['manzil'] ?><br>
                            <?= $shipping_address['shahar'] ?>, <?= $shipping_address['viloyat'] ?><br>
                            <?= $shipping_address['pochta_indeksi'] ?><br>
                            <?= $shipping_address['mamlakat'] ?><br>
                            <strong>Phone:</strong> <?= $shipping_address['telefon'] ?>
                        </address>
                    </div>
                </div>
                
                <?php if ($billing_address): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        Billing Address
                    </div>
                    <div class="card-body">
                        <address>
                            <?= $billing_address['ism'] ?> <?= $billing_address['familiya'] ?><br>
                            <?= $billing_address['manzil'] ?><br>
                            <?= $billing_address['shahar'] ?>, <?= $billing_address['viloyat'] ?><br>
                            <?= $billing_address['pochta_indeksi'] ?><br>
                            <?= $billing_address['mamlakat'] ?><br>
                            <strong>Phone:</strong> <?= $billing_address['telefon'] ?>
                        </address>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                Order Items
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?= $item['mahsulot_nomi'] ?></td>
                                    <td>$<?= number_format($item['dona_narxi'], 2) ?></td>
                                    <td><?= $item['miqdor'] ?></td>
                                    <td>$<?= number_format($item['umumiy_narxi'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <td>$<?= number_format($order['umumiy_summa'], 2) ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Discount:</th>
                                <td>$<?= number_format($order['chegirma_summa'], 2) ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Shipping:</th>
                                <td>$<?= number_format($order['yetkazish_summa'], 2) ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Tax:</th>
                                <td>$<?= number_format($order['soliq_summa'], 2) ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <td>$<?= number_format($order['yakuniy_summa'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Order Notes
            </div>
            <div class="card-body">
                <form method="POST" action="update_note.php">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="mb-3">
                        <label for="admin_note" class="form-label">Admin Note</label>
                        <textarea class="form-control" id="admin_note" name="admin_note" rows="3"><?= $order['admin_izohi'] ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';