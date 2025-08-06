<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tags"></i> Kategoriya</h5>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Kategoriya qo'shish</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism</th>
                        <th>Slug</th>
                        <th>Ota-ona</th>
                        <th>Tasdiqlangan</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT k1.id, k1.nomi, k1.slug, k1.afzallikli, k1.korinish_tartibi, 
                               k2.nomi as parent_name
                        FROM kategoriyalar k1
                        LEFT JOIN kategoriyalar k2 ON k1.ota_kategoriya_id = k2.id
                        ORDER BY k1.korinish_tartibi, k1.nomi
                    ");
                    
                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $featured = $category['afzallikli'] ? 'Ha' : 'Yoq';
                        $featuredClass = $category['afzallikli'] ? 'success' : 'secondary';
                        
                        echo "<tr>
                            <td>{$category['id']}</td>
                            <td>{$category['nomi']}</td>
                            <td>{$category['slug']}</td>
                         <td>" . ($category['parent_name'] ?? '-') . "</td>;

                            <td><span class='badge bg-{$featuredClass}'>{$featured}</span></td>
                            <td><span class='badge bg-success'>Faol</span></td>
                            <td>
                                <a href='update.php?id={$category['id']}' class='btn btn-sm btn-warning' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i></a>
                                <a href='delete.php?id={$category['id']}' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>
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