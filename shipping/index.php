<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-truck"></i> Shipping Methods</h5>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Method</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                       <th>ID</th>
                        <th>Ism</th>
                        <th>Narx</th>
                        <th>Bepul chegara</th>
                        <th>Etkazib berish muddati</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT id, nomi, narxi, bepul_yetkazish_chegara, yetkazish_muddati, faol
                        FROM yetkazish_usullari
                        ORDER BY narxi
                    ");
                    
                    while ($method = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status = $method['faol'] ? 'Active' : 'Inactive';
                        $statusClass = $method['faol'] ? 'success' : 'secondary';
                        
                        echo "<tr>
                            <td>{$method['id']}</td>
                            <td>{$method['nomi']}</td>
                            <td>$" . number_format($method['narxi'], 2) . "</td>
                            <td>" . ($method['bepul_yetkazish_chegara'] ? '$' . number_format($method['bepul_yetkazish_chegara'], 2) : '-') . "</td>
                            <td>{$method['yetkazish_muddati']}</td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>
                                <a href='edit.php?id={$method['id']}' class='btn btn-sm btn-warning' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i></a>
                                <a href='delete.php?id={$method['id']}' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>
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