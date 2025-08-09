<?php
require_once '../config.php';checkLogin();

// Only super admin can access this page
if ($_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page!";
    redirect('../dashboard.php');
}

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Admin Users</h5>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Admin</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foydalanuvchi nomi</th>
                        <th>Ism</th>
                        <th>Elektron pochta</th>
                        <th>Rol</th>
                        <th>Holat</th>
                        <th>Oxirgi kirish</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT id, foydalanuvchi_nomi, ism, email, telefon, rol, faol, oxirgi_kirish
                        FROM adminlar
                        ORDER BY rol, foydalanuvchi_nomi
                    ");
                    
                    while ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status = $admin['faol'] ? 'Faol' : 'Nofaol';
                        $statusClass = $admin['faol'] ? 'success' : 'secondary';
                        
                        echo "<tr>
                            <td>{$admin['id']}</td>
                            <td>{$admin['foydalanuvchi_nomi']}</td>
                            <td>{$admin['ism']}</td>
                            <td>{$admin['email']}</td>
                            <td>" . ucfirst(str_replace('_', ' ', $admin['rol'])) . "</td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>" . ($admin['oxirgi_kirish'] ? date('M d, Y H:i', strtotime($admin['oxirgi_kirish'])) : 'Never') . "</td>
                            <td>
                                <a href='edit.php?id={$admin['id']}' class='btn btn-sm btn-warning' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i></a>
                                " . ($admin['id'] != $_SESSION['admin_id'] ? 
                                    "<a href='delete.php?id={$admin['id']}' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>" : 
                                    "<button class='btn btn-sm btn-secondary' disabled><i class='bi bi-trash'></i></button>") . "
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