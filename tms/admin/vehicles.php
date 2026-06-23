<?php
require_once '../includes/header.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = (int)($_POST['id'] ?? 0);
    $reg = trim($_POST['registration_number']);
    $model = trim($_POST['model']);
    $make = trim($_POST['make']);
    $year = (int)$_POST['year'];
    $capacity = (int)$_POST['capacity'];
    $fuel_type = $_POST['fuel_type'];
    $status = $_POST['status'];
    $odo = (int)$_POST['current_odometer'];
    $last = $_POST['last_service_date'] ?: null;
    $next = $_POST['next_service_date'] ?: null;
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO vehicles (registration_number, model, make, year, capacity, fuel_type, status, current_odometer, last_service_date, next_service_date) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$reg, $model, $make, $year, $capacity, $fuel_type, $status, $odo, $last, $next]);
        logActivity($_SESSION['user_id'], 'Add Vehicle', "Added $reg");
        $_SESSION['message'] = 'Vehicle added.';
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE vehicles SET registration_number=?, model=?, make=?, year=?, capacity=?, fuel_type=?, status=?, current_odometer=?, last_service_date=?, next_service_date=? WHERE id=?");
        $stmt->execute([$reg, $model, $make, $year, $capacity, $fuel_type, $status, $odo, $last, $next, $id]);
        logActivity($_SESSION['user_id'], 'Edit Vehicle', "Edited ID $id");
        $_SESSION['message'] = 'Vehicle updated.';
    }
    header('Location: vehicles.php');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $v = getVehicle($id);
    if ($v) { $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$id]); logActivity($_SESSION['user_id'], 'Delete Vehicle', "Deleted {$v['registration_number']}"); $_SESSION['message'] = 'Deleted.'; }
    header('Location: vehicles.php');
    exit;
}
$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-car me-2"></i>Vehicles</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal" onclick="resetForm()"><i class="fas fa-plus"></i> Add Vehicle</button></div>
<?php if($message): ?><div class="alert alert-success alert-dismissible"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="table-container">
    <table id="vehiclesTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Registration</th><th>Model</th><th>Make</th><th>Year</th><th>Status</th><th>Odometer</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($vehicles as $v): ?><tr><td><?= $v['id'] ?></td><td><?= htmlspecialchars($v['registration_number']) ?></td><td><?= htmlspecialchars($v['model']) ?></td><td><?= htmlspecialchars($v['make']) ?></td><td><?= $v['year'] ?></td><td><span class="badge badge-status bg-<?= $v['status']=='active'?'success':($v['status']=='inactive'?'danger':'warning') ?>"><?= ucfirst($v['status']) ?></span></td><td><?= $v['current_odometer'] ?></td><td class="text-center"><button class="btn btn-sm btn-warning btn-action" onclick="editVehicle(<?= htmlspecialchars(json_encode($v)) ?>)"><i class="fas fa-edit"></i></button> <a href="?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="vehicleModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Vehicle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" id="formAction" value="add"><input type="hidden" name="id" id="vehicleId" value="0"><div class="mb-3"><label>Registration *</label><input type="text" name="registration_number" id="reg_no" class="form-control" required></div><div class="row"><div class="col-md-6"><label>Model *</label><input type="text" name="model" id="model" class="form-control" required></div><div class="col-md-6"><label>Make *</label><input type="text" name="make" id="make" class="form-control" required></div></div><div class="row"><div class="col-md-6"><label>Year *</label><input type="number" name="year" id="year" class="form-control" required></div><div class="col-md-6"><label>Capacity</label><input type="number" name="capacity" id="capacity" class="form-control"></div></div><div class="row"><div class="col-md-6"><label>Fuel Type</label><select name="fuel_type" id="fuel_type" class="form-select"><option value="Petrol">Petrol</option><option value="Diesel">Diesel</option><option value="Electric">Electric</option><option value="Hybrid">Hybrid</option></select></div><div class="col-md-6"><label>Status</label><select name="status" id="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="under_maintenance">Under Maintenance</option></select></div></div><div class="mb-3"><label>Current Odometer</label><input type="number" name="current_odometer" id="current_odometer" class="form-control"></div><div class="row"><div class="col-md-6"><label>Last Service</label><input type="date" name="last_service_date" id="last_service_date" class="form-control"></div><div class="col-md-6"><label>Next Service</label><input type="date" name="next_service_date" id="next_service_date" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<script>
function resetForm(){ document.getElementById('formAction').value='add'; document.getElementById('vehicleId').value=0; document.getElementById('modalTitle').innerText='Add Vehicle'; ['reg_no','model','make','year','capacity','current_odometer','last_service_date','next_service_date'].forEach(id=>document.getElementById(id).value=''); document.getElementById('fuel_type').value='Petrol'; document.getElementById('status').value='active'; }
function editVehicle(data){ document.getElementById('formAction').value='edit'; document.getElementById('vehicleId').value=data.id; document.getElementById('modalTitle').innerText='Edit Vehicle'; document.getElementById('reg_no').value=data.registration_number; document.getElementById('model').value=data.model; document.getElementById('make').value=data.make; document.getElementById('year').value=data.year; document.getElementById('capacity').value=data.capacity; document.getElementById('fuel_type').value=data.fuel_type; document.getElementById('status').value=data.status; document.getElementById('current_odometer').value=data.current_odometer; document.getElementById('last_service_date').value=data.last_service_date; document.getElementById('next_service_date').value=data.next_service_date; new bootstrap.Modal(document.getElementById('vehicleModal')).show(); }
$(document).ready(function(){ $('#vehiclesTable').DataTable({ pageLength:10, responsive:true, order:[[0,'desc']] }); });
</script>
<?php require_once '../includes/footer.php'; ?>