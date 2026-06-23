<?php
require_once '../includes/header.php';
requireRole('officer');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $cost = !empty($_POST['cost']) ? (float)$_POST['cost'] : null;
    $status = $_POST['status'];
    $stmt = $pdo->prepare("INSERT INTO maintenance_records (vehicle_id, date, type, description, cost, status, created_by) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$vehicle_id, $date, $type, $description, $cost, $status, $_SESSION['user_id']]);
    logActivity($_SESSION['user_id'], 'Add Maintenance', "Added maintenance for vehicle $vehicle_id");
    $_SESSION['message'] = 'Maintenance record added.';
    header('Location: maintenance.php');
    exit;
}
$records = $pdo->query("SELECT m.*, v.registration_number FROM maintenance_records m JOIN vehicles v ON m.vehicle_id=v.id ORDER BY m.id DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_number FROM vehicles")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-tools me-2"></i>Maintenance Logs</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#maintModal"><i class="fas fa-plus"></i> Add Maintenance</button></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="table-container">
    <table id="maintTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Vehicle</th><th>Date</th><th>Type</th><th>Description</th><th>Cost</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($records as $m): ?><tr><td><?= $m['id'] ?></td><td><?= htmlspecialchars($m['registration_number']) ?></td><td><?= $m['date'] ?></td><td><?= $m['type'] ?></td><td><?= htmlspecialchars($m['description']) ?></td><td><?= $m['cost'] ?></td><td><span class="badge badge-status bg-<?= $m['status']=='completed'?'success':'warning' ?>"><?= ucfirst($m['status']) ?></span></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Add Modal -->
<div class="modal fade" id="maintModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Add Maintenance</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add"><div class="mb-3"><label>Vehicle</label><select name="vehicle_id" class="form-select" required><?php foreach($vehicles as $v) echo '<option value="'.$v['id'].'">'.$v['registration_number'].'</option>'; ?></select></div><div class="mb-3"><label>Date</label><input type="date" name="date" class="form-control" required></div><div class="mb-3"><label>Type</label><select name="type" class="form-select" required><option value="routine">Routine</option><option value="repair">Repair</option><option value="inspection">Inspection</option><option value="other">Other</option></select></div><div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2" required></textarea></div><div class="row"><div class="col-md-6"><label>Cost</label><input type="number" step="0.01" name="cost" class="form-control"></div><div class="col-md-6"><label>Status</label><select name="status" class="form-select"><option value="pending">Pending</option><option value="completed">Completed</option></select></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<script>$(document).ready(function(){ $('#maintTable').DataTable({ pageLength:10, responsive:true }); });</script>
<?php require_once '../includes/footer.php'; ?>