<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-percent"></i> Chegirmalar</h5>
        <a href="add_discount.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Discount</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism</th>
                        <th>Tur</th>
                        <th>Qiymat</th>
                        <th>Kod</th>
                        <th>Boshlanish sanasi</th>
                        <th>Yakunlash sanasi</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT id, nomi, chegirma_turi, chegirma_qiymati, kodi,
                               boshlanish_vaqti, tugash_vaqti, faol
                        FROM chegirmalar
                        ORDER BY boshlanish_vaqti DESC
                    ");
                    
                    while ($discount = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status = $discount['faol'] ? 'Faol' : 'Nofaol';
                        $statusClass = $discount['faol'] ? 'success' : 'secondary';
                        
                        $current_time = time();
                        $start_time = strtotime($discount['boshlanish_vaqti']);
                        $end_time = strtotime($discount['tugash_vaqti']);
                        
                        if ($current_time < $start_time) {
                            $status = 'Scheduled';
                            $statusClass = 'info';
                        } elseif ($current_time > $end_time) {
                            $status = 'Expired';
                            $statusClass = 'danger';
                        }
                        
                        echo "<tr>
                            <td>{$discount['id']}</td>
                            <td>{$discount['nomi']}</td>
                            <td>" . ucfirst($discount['chegirma_turi']) . "</td>
                            <td>" . ($discount['chegirma_turi'] == 'foiz' ? $discount['chegirma_qiymati'] . '%' : '$' . $discount['chegirma_qiymati']) . "</td>
                            <td>{$discount['kodi']}</td>
                            <td>" . date('M d, Y', $start_time) . "</td>
                            <td>" . date('M d, Y', $end_time) . "</td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>
                                <a href='edit_discount.php?id={$discount['id']}' class='btn btn-sm btn-warning' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i></a>
                                <a href='delete_discount.php?id={$discount['id']}' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>
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