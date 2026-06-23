<?php
require_once '../includes/header.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add') {
        $vehicle_id = (int)$_POST['vehicle_id'];
        $driver_id = (int)$_POST['driver_id'];
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        $purpose = trim($_POST['purpose']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_allocations WHERE vehicle_id = ? AND status='active' AND (start_date <= ? AND end_date >= ?)");
        $stmt->execute([$vehicle_id, $end, $start]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Vehicle already allocated for this period.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO vehicle_allocations (vehicle_id, driver_id, assigned_by, start_date, end_date, purpose) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$vehicle_id, $driver_id, $_SESSION['user_id'], $start, $end, $purpose]);
            logActivity($_SESSION['user_id'], 'Add Allocation', "Allocated vehicle $vehicle_id to driver $driver_id");
            $_SESSION['message'] = 'Allocation added.';
        }
    } elseif ($action === 'end') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE vehicle_allocations SET status='ended' WHERE id=?");
        $stmt->execute([$id]);
        logActivity($_SESSION['user_id'], 'End Allocation', "Ended allocation ID $id");
        $_SESSION['message'] = 'Allocation ended.';
    }
    header('Location: allocations.php');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM vehicle_allocations WHERE id=?")->execute([$id]);
    logActivity($_SESSION['user_id'], 'Delete Allocation', "Deleted allocation ID $id");
    $_SESSION['message'] = 'Deleted.';
    header('Location: allocations.php');
    exit;
}
$allocations = $pdo->query("SELECT a.*, v.registration_number, u.name as driver_name FROM vehicle_allocations a JOIN vehicles v ON a.vehicle_id=v.id JOIN drivers d ON a.driver_id=d.id JOIN users u ON d.user_id=u.id ORDER BY a.id DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_number FROM vehicles WHERE status='active'")->fetchAll();
$drivers = $pdo->query("SELECT d.id, u.name FROM drivers d JOIN users u ON d.user_id=u.id WHERE u.status='active'")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
$error = $_SESSION['error'] ?? ''; unset($_SESSION['error']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-calendar-check me-2"></i>Vehicle Allocations</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocModal"><i class="fas fa-plus"></i> New Allocation</button></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="table-container">
    <table id="allocTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Vehicle</th><th>Driver</th><th>Start</th><th>End</th><th>Purpose</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($allocations as $a): ?><tr><td><?= $a['id'] ?></td><td><?= htmlspecialchars($a['registration_number']) ?></td><td><?= htmlspecialchars($a['driver_name']) ?></td><td><?= $a['start_date'] ?></td><td><?= $a['end_date'] ?></td><td><?= htmlspecialchars($a['purpose']) ?></td><td><span class="badge badge-status bg-<?= $a['status']=='active'?'success':'secondary' ?>"><?= ucfirst($a['status']) ?></span></td>
        <td class="text-center"><?php if($a['status']=='active'): ?><button class="btn btn-sm btn-info btn-action" onclick="endAlloc(<?= $a['id'] ?>)">End</button><?php endif; ?> <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Add Modal -->
<div class="modal fade" id="allocModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">New Allocation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add"><div class="mb-3"><label>Vehicle</label><select name="vehicle_id" class="form-select" required><?php foreach($vehicles as $v) echo '<option value="'.$v['id'].'">'.$v['registration_number'].'</option>'; ?></select></div><div class="mb-3"><label>Driver</label><select name="driver_id" class="form-select" required><?php foreach($drivers as $d) echo '<option value="'.$d['id'].'">'.$d['name'].'</option>'; ?></select></div><div class="row"><div class="col-md-6"><label>Start Date</label><input type="date" name="start_date" class="form-control" required></div><div class="col-md-6"><label>End Date</label><input type="date" name="end_date" class="form-control" required></div></div><div class="mb-3"><label>Purpose</label><textarea name="purpose" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<script>
function endAlloc(id){ if(confirm('End this allocation?')){ fetch('allocations.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=end&id='+id}).then(()=>location.reload()); } }
$(document).ready(function(){ $('#allocTable').DataTable({ pageLength:10, responsive:true }); });
</script>
<?php require_once '../includes/footer.php'; ?>