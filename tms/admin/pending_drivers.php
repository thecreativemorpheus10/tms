<?php
require_once '../includes/header.php';
requireRole('admin');

if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='driver'");
    $stmt->execute([$id]);
    logActivity($_SESSION['user_id'], 'Approve Driver', "Approved user ID $id");
    $_SESSION['message'] = 'Driver approved.';
    header('Location: pending_drivers.php');
    exit;
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $pdo->prepare("UPDATE users SET status='inactive' WHERE id=? AND role='driver'");
    $stmt->execute([$id]);
    logActivity($_SESSION['user_id'], 'Reject Driver', "Rejected user ID $id");
    $_SESSION['message'] = 'Driver rejected.';
    header('Location: pending_drivers.php');
    exit;
}
$pending = $pdo->query("SELECT u.*, d.license_number, d.phone FROM users u JOIN drivers d ON u.id=d.user_id WHERE u.role='driver' AND u.status='pending' ORDER BY u.created_at DESC")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-user-plus me-2"></i>Pending Drivers</h1></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if(empty($pending)): ?><p class="text-muted">No pending driver registrations.</p><?php else: ?>
<div class="table-container">
    <table class="table table-striped table-hover table-bordered">
        <thead><tr><th>Name</th><th>Email</th><th>License</th><th>Phone</th><th>Registered</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
        <?php foreach($pending as $p): ?><tr><td><?= htmlspecialchars($p['name']) ?></td><td><?= htmlspecialchars($p['email']) ?></td><td><?= htmlspecialchars($p['license_number']) ?></td><td><?= htmlspecialchars($p['phone']) ?></td><td><?= $p['created_at'] ?></td><td class="text-center"><a href="?approve=<?= $p['id'] ?>" class="btn btn-sm btn-success btn-action" onclick="return confirm('Approve this driver?')"><i class="fas fa-check"></i> Approve</a> <a href="?reject=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Reject this driver?')"><i class="fas fa-times"></i> Reject</a></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require_once '../includes/footer.php'; ?>