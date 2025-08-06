<?php
require_once '../config.php';checkLogin();

require_once '../header.php';
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people"></i> Foydalanuvchilar</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                       <th>ID</th>
                        <th>Ism</th>
                        <th>Elektron pochta</th>
                        <th>Telefon</th>
                        <th>Tasdiqlangan</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT id, ism, familiya, email, telefon, email_tasdiqlangan, telefon_tasdiqlangan, faol
                        FROM foydalanuvchilar
                        ORDER BY yaratilgan_vaqt DESC
                    ");
                    
                    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $verified = ($user['email_tasdiqlangan'] && $user['telefon_tasdiqlangan']) ? 'Yes' : 'No';
                        $verifiedClass = ($user['email_tasdiqlangan'] && $user['telefon_tasdiqlangan']) ? 'success' : 'warning';
                        $status = $user['faol'] ? 'Active' : 'Inactive';
                        $statusClass = $user['faol'] ? 'success' : 'secondary';
                        
                        echo "<tr>
                            <td>{$user['id']}</td>
                            <td>{$user['ism']} {$user['familiya']}</td>
                            <td>{$user['email']}</td>
                            <td>{$user['telefon']}</td>
                            <td><span class='badge bg-{$verifiedClass}'>{$verified}</span></td>
                            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
                            <td>
                                <a href='view.php?id={$user['id']}' class='btn btn-sm btn-primary' data-bs-toggle='tooltip' title='View'><i class='bi bi-eye'></i></a>
                                <a href='#' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i></a>
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