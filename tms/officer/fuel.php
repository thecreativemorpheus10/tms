<?php
require_once '../includes/header.php';
requireRole('officer');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $trip_id = !empty($_POST['trip_id']) ? (int)$_POST['trip_id'] : null;
    $date = $_POST['date'];
    $liters = (float)$_POST['liters'];
    $cost = (float)$_POST['cost'];
    $odo = (int)$_POST['odometer_reading'];
    $stmt = $pdo->prepare("INSERT INTO fuel_records (vehicle_id, trip_id, date, liters, cost, odometer_reading, created_by) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$vehicle_id, $trip_id, $date, $liters, $cost, $odo, $_SESSION['user_id']]);
    logActivity($_SESSION['user_id'], 'Add Fuel Record', "Added fuel for vehicle $vehicle_id");
    $_SESSION['message'] = 'Fuel record added.';
    header('Location: fuel.php');
    exit;
}
$records = $pdo->query("SELECT f.*, v.registration_number FROM fuel_records f JOIN vehicles v ON f.vehicle_id=v.id ORDER BY f.id DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_number FROM vehicles")->fetchAll();
$trips = $pdo->query("SELECT id, trip_date FROM trips WHERE status='completed'")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-gas-pump me-2"></i>Fuel Logs</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fuelModal"><i class="fas fa-plus"></i> Add Fuel Record</button></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="table-container">
    <table id="fuelTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Vehicle</th><th>Date</th><th>Liters</th><th>Cost</th><th>Odometer</th></tr></thead>
        <tbody>
        <?php foreach($records as $f): ?><tr><td><?= $f['id'] ?></td><td><?= htmlspecialchars($f['registration_number']) ?></td><td><?= $f['date'] ?></td><td><?= $f['liters'] ?></td><td><?= $f['cost'] ?></td><td><?= $f['odometer_reading'] ?></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Add Modal -->
<div class="modal fade" id="fuelModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Add Fuel Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add"><div class="mb-3"><label>Vehicle</label><select name="vehicle_id" class="form-select" required><?php foreach($vehicles as $v) echo '<option value="'.$v['id'].'">'.$v['registration_number'].'</option>'; ?></select></div><div class="mb-3"><label>Associated Trip (optional)</label><select name="trip_id" class="form-select"><option value="">None</option><?php foreach($trips as $t) echo '<option value="'.$t['id'].'">Trip #'.$t['id'].' ('.$t['trip_date'].')</option>'; ?></select></div><div class="mb-3"><label>Date</label><input type="date" name="date" class="form-control" required></div><div class="row"><div class="col-md-6"><label>Liters</label><input type="number" step="0.01" name="liters" class="form-control" required></div><div class="col-md-6"><label>Cost</label><input type="number" step="0.01" name="cost" class="form-control" required></div></div><div class="mb-3"><label>Odometer Reading</label><input type="number" name="odometer_reading" class="form-control" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<script>$(document).ready(function(){ $('#fuelTable').DataTable({ pageLength:10, responsive:true }); });</script>
<?php require_once '../includes/footer.php'; ?>