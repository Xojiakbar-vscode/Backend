<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Mahsulotlar</h5>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Mahsulot qo'shish</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rasm</th>
                        <th>Ism</th>
                        <th>Kategoriya</th>
                        <th>Narx</th>
                        <th>Aksiya</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT m.id, m.nomi, m.asosiy_narx, m.chegirma_narxi, m.faol, m.sku, 
                               k.nomi as kategoriya, 
                               (SELECT rasm_url FROM mahsulot_rasmlari WHERE mahsulot_id = m.id AND asosiy_rasm = 1 LIMIT 1) as rasm,
                               (SELECT SUM(qoldiq_soni) FROM mahsulot_variantlari WHERE mahsulot_id = m.id) as qoldiq
                        FROM mahsulotlar m
                        JOIN kategoriyalar k ON m.kategoriya_id = k.id
                        ORDER BY m.yaratilgan_vaqt DESC
                    ");
                    
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status = $product['faol'] ? 'Active' : 'Inactive';
                        $statusClass = $product['faol'] ? 'success' : 'secondary';
                        $discount = $product['chegirma_narxi'] > 0 ? 
                            '<small class="text-danger"><del>$'.number_format($product['asosiy_narxi'], 2).'</del></small> $'.number_format($product['chegirma_narxi'], 2) : 
                            '$'.number_format($product['asosiy_narx'], 2);
                        
                        echo "<tr>
                            <td>{$product['id']}</td>
                            <td><img src='../{$product['rasm']}' alt='{$product['nomi']}' class='img-thumbnail' style='max-width: 60px;'></td>
                            <td>{$product['nomi']}</td>
                            <td>{$product['kategoriya']}</td>
                            <td>{$discount}</td>
                            <td>" . ($product['qoldiq'] ?? 0) . "</td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>
                                <a href='update.php?id={$product['id']}' class='btn btn-sm btn-warning' title='Edit'><i class='bi bi-pencil'></i></a>
                                <a href='delete.php?id={$product['id']}' class='btn btn-sm btn-danger delete-btn' title='Delete'><i class='bi bi-trash'></i></a>
                                <a href='variants.php?mahsulot_id={$product['id']}' class='btn btn-sm btn-primary' title='Variants'>
                                    <i class='bi bi-diagram-2'></i>
                                </a>
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
