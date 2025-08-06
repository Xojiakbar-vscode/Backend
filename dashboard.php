<?php
require_once 'config.php';

checkLogin();

require_once 'header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-speedometer2"></i> Dashboard
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Mahsulotlar soni</h5>
                                <?php
                                $stmt = $conn->query("SELECT COUNT(*) FROM mahsulotlar");
                                $productCount = $stmt->fetchColumn();
                                ?>
                                <h2 class="mb-0"><?= $productCount ?></h2>
                            </div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Buyrutmalar</h5>
                                <?php
                                $stmt = $conn->query("SELECT COUNT(*) FROM buyurtmalar");
                                $orderCount = $stmt->fetchColumn();
                                ?>
                                <h2 class="mb-0"><?= $orderCount ?></h2>
                            </div>
                            <i class="bi bi-cart fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Foydalanuvchilar</h5>
                                <?php
                                $stmt = $conn->query("SELECT COUNT(*) FROM foydalanuvchilar");
                                $userCount = $stmt->fetchColumn();
                                ?>
                                <h2 class="mb-0"><?= $userCount ?></h2>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Kategoriyalar</h5>
                                <?php
                                $stmt = $conn->query("SELECT COUNT(*) FROM kategoriyalar");
                                $categoryCount = $stmt->fetchColumn();
                                ?>
                                <h2 class="mb-0"><?= $categoryCount ?></h2>
                            </div>
                            <i class="bi bi-tags fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Oxirgi buyurtmalar
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        
                                        <th>Buyurtma #</th>
                                        <th>Mijoz</th>
                                        <th>Holat</th>
                                        <th>Jami</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->query("
                                        SELECT b.id, b.buyurtma_raqami, f.ism, f.familiya, b.holat, b.yakuniy_summa 
                                        FROM buyurtmalar b
                                        JOIN foydalanuvchilar f ON b.foydalanuvchi_id = f.id
                                        ORDER BY b.yaratilgan_vaqt DESC LIMIT 5
                                    ");
                                    
                                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>
                                            <td><a href='orders/view.php?id={$order['id']}'>{$order['buyurtma_raqami']}</a></td>
                                            <td>{$order['ism']} {$order['familiya']}</td>
                                            <td><span class='badge bg-info'>{$order['holat']}</span></td>
                                            <td>$" . number_format($order['yakuniy_summa'], 2) . "</td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                    
                        Oxirgi mahsulotlar

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mahsulot</th>
                                        <th>Kategoriya</th>
                                        <th>Narx</th>
                                        <th>Holat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->query("
                                        SELECT m.id, m.nomi, m.asosiy_narx, m.faol, k.nomi as kategoriya 
                                        FROM mahsulotlar m
                                        JOIN kategoriyalar k ON m.kategoriya_id = k.id
                                        ORDER BY m.yaratilgan_vaqt DESC LIMIT 5
                                    ");
                                    
                                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $status = $product['faol'] ? 'Active' : 'Inactive';
                                        $statusClass = $product['faol'] ? 'success' : 'secondary';
                                        
                                        echo "<tr>
                                            <td><a href='products/update.php?id={$product['id']}'>{$product['nomi']}</a></td>
                                            <td>{$product['kategoriya']}</td>
                                            <td>$" . number_format($product['asosiy_narx'], 2) . "</td>
                                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';