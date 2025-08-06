<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-star"></i> Product Reviews</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT r.id, r.reyting, r.sarlavha, r.yaratilgan_vaqt, r.tasdiqlangan,
                               m.nomi as mahsulot_nomi,
                               CONCAT(f.ism, ' ', f.familiya) as mijoz_nomi
                        FROM mahsulot_sharhlari r
                        JOIN mahsulotlar m ON r.mahsulot_id = m.id
                        JOIN foydalanuvchilar f ON r.foydalanuvchi_id = f.id
                        ORDER BY r.yaratilgan_vaqt DESC
                    ");
                    
                    while ($review = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status = $review['tasdiqlangan'] ? 'Approved' : 'Pending';
                        $statusClass = $review['tasdiqlangan'] ? 'success' : 'warning';
                        
                        echo "<tr>
                            <td>{$review['id']}</td>
                            <td>{$review['mahsulot_nomi']}</td>
                            <td>{$review['mijoz_nomi']}</td>
                            <td>
                                <div class=\"rating\">
                                    " . str_repeat('<i class="bi bi-star-fill text-warning"></i>', $review['reyting']) . "
                                    " . str_repeat('<i class="bi bi-star text-secondary"></i>', 5 - $review['reyting']) . "
                                </div>
                            </td>
                            <td>{$review['sarlavha']}</td>
                            <td>" . date('M d, Y', strtotime($review['yaratilgan_vaqt'])) . "</td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>
                                <a href='view.php?id={$review['id']}' class='btn btn-sm btn-primary' data-bs-toggle='tooltip' title='View'><i class='bi bi-eye'></i></a>
                                <a href='delete.php?id={$review['id']}' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>
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