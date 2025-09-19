<?php
$page_title = "Audit Log";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Only allow admin access (you would need to implement admin role checking)
// For now, we'll restrict to user_id 1 as admin
if ($_SESSION['user_id'] != 1) {
    redirect('../index.php');
}

$database = new Database();
$db = $database->getConnection();

// Get audit log entries
$query = "SELECT a.*, u.username 
          FROM audit_log a 
          JOIN users u ON a.user_id = u.id 
          ORDER BY a.created_at DESC 
          LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$audit_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="mb-4">Audit Log</h1>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($audit_logs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo $log['username']; ?></td>
                                    <td><?php echo $log['action']; ?></td>
                                    <td><?php echo $log['table_name']; ?></td>
                                    <td><?php echo $log['record_id']; ?></td>
                                    <td><?php echo $log['ip_address']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>No audit log entries found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>