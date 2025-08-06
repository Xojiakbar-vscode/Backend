<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-cart"></i> Buyrutmalar</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Buyurtma #</th>
                        <th>Mijoz</th>
                        <th>Sana</th>
                        <th>Holat</th>
                        <th>Toʻlov</th>
                        <th>Jami</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT b.id, b.buyurtma_raqami, f.ism, f.familiya, b.yaratilgan_vaqt, 
                               b.holat, b.tolov_holati, b.yakuniy_summa
                        FROM buyurtmalar b
                        JOIN foydalanuvchilar f ON b.foydalanuvchi_id = f.id
                        ORDER BY b.yaratilgan_vaqt DESC
                    ");
                    
                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $statusClass = '';
                        switch ($order['holat']) {
                            case 'kutilyapti': $statusClass = 'secondary'; break;
                            case 'jarayonda': $statusClass = 'info'; break;
                            case 'yuborilgan': $statusClass = 'primary'; break;
                            case 'yetkazilgan': $statusClass = 'success'; break;
                            case 'bekor_qilingan': $statusClass = 'danger'; break;
                            case 'qaytarilgan': $statusClass = 'warning'; break;
                        }
                        
                        $paymentClass = '';
                        switch ($order['tolov_holati']) {
                            case 'kutilyapti': $paymentClass = 'secondary'; break;
                            case 'tolangan': $paymentClass = 'success'; break;
                            case 'muvaffaqiyatsiz': $paymentClass = 'danger'; break;
                            case 'qaytarilgan': $paymentClass = 'warning'; break;
                        }
                        
                        echo "<tr>
                            <td>{$order['buyurtma_raqami']}</td>
                            <td>{$order['ism']} {$order['familiya']}</td>
                            <td>" . date('M d, Y', strtotime($order['yaratilgan_vaqt'])) . "</td>
                            <td><span class='badge bg-{$statusClass}'>" . ucfirst($order['holat']) . "</span></td>
                            <td><span class='badge bg-{$paymentClass}'>" . ucfirst($order['tolov_holati']) . "</span></td>
                            <td>$" . number_format($order['yakuniy_summa'], 2) . "</td>
                            <td>
                                <a href='view.php?id={$order['id']}' class='btn btn-sm btn-primary' data-bs-toggle='tooltip' title='View'><i class='bi bi-eye'></i></a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';