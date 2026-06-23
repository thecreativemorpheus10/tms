<?php
require_once '../includes/header.php';
requireRole('officer');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $driver_id = (int)$_POST['driver_id'];
    $route_id = (int)$_POST['route_id'];
    $trip_date = $_POST['trip_date'];
    $departure_time = $_POST['departure_time'];
    $allocation_id = !empty($_POST['allocation_id']) ? (int)$_POST['allocation_id'] : null;
    if (!isVehicleAvailable($vehicle_id, $trip_date, $departure_time)) {
        $_SESSION['error'] = 'Vehicle not available on this date/time.';
    } elseif (!isDriverAvailable($driver_id, $trip_date, $departure_time)) {
        $_SESSION['error'] = 'Driver not available on this date/time.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO trips (vehicle_id, driver_id, route_id, allocation_id, trip_date, departure_time, status, created_by) VALUES (?,?,?,?,?,?,'scheduled',?)");
        $stmt->execute([$vehicle_id, $driver_id, $route_id, $allocation_id, $trip_date, $departure_time, $_SESSION['user_id']]);
        $trip_id = $pdo->lastInsertId();
        logActivity($_SESSION['user_id'], 'Create Trip', "Created trip ID $trip_id");
        $trip = $pdo->prepare("SELECT t.*, v.registration_number, r.name as route_name FROM trips t JOIN vehicles v ON t.vehicle_id=v.id JOIN routes r ON t.route_id=r.id WHERE t.id=?");
        $trip->execute([$trip_id]);
        $trip = $trip->fetch();
        $driver = getDriver($driver_id);
        $driver_email = $driver['email'] ?? null;
        if ($driver_email) sendTripAssignmentEmail($driver_email, $trip);
        $_SESSION['message'] = 'Trip created and driver notified.';
    }
    header('Location: trips.php');
    exit;
}
if (isset($_POST['close'])) {
    $id = (int)$_POST['id'];
    $distance = (float)$_POST['distance_traveled'];
    $fuel = (float)$_POST['fuel_consumed'];
    $arrival = $_POST['arrival_time'];
    $stmt = $pdo->prepare("UPDATE trips SET status='completed', arrival_time=?, distance_traveled=?, fuel_consumed=? WHERE id=?");
    $stmt->execute([$arrival, $distance, $fuel, $id]);
    logActivity($_SESSION['user_id'], 'Close Trip', "Closed trip ID $id");
    $_SESSION['message'] = 'Trip completed.';
    header('Location: trips.php');
    exit;
}
if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $stmt = $pdo->prepare("UPDATE trips SET status='cancelled' WHERE id=?");
    $stmt->execute([$id]);
    logActivity($_SESSION['user_id'], 'Cancel Trip', "Cancelled trip ID $id");
    $_SESSION['message'] = 'Trip cancelled.';
    header('Location: trips.php');
    exit;
}
$trips = $pdo->query("SELECT t.*, v.registration_number, u.name as driver_name, r.name as route_name FROM trips t JOIN vehicles v ON t.vehicle_id=v.id JOIN drivers d ON t.driver_id=d.id JOIN users u ON d.user_id=u.id JOIN routes r ON t.route_id=r.id ORDER BY t.id DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_number FROM vehicles WHERE status='active'")->fetchAll();
$drivers = $pdo->query("SELECT d.id, u.name FROM drivers d JOIN users u ON d.user_id=u.id WHERE u.status='active'")->fetchAll();
$routes = $pdo->query("SELECT * FROM routes")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
$error = $_SESSION['error'] ?? ''; unset($_SESSION['error']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-road me-2"></i>Trips</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tripModal"><i class="fas fa-plus"></i> New Trip</button></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="table-container">
    <table id="tripsTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Vehicle</th><th>Driver</th><th>Route</th><th>Date</th><th>Departure</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($trips as $t): ?><tr><td><?= $t['id'] ?></td><td><?= htmlspecialchars($t['registration_number']) ?></td><td><?= htmlspecialchars($t['driver_name']) ?></td><td><?= htmlspecialchars($t['route_name']) ?></td><td><?= $t['trip_date'] ?></td><td><?= $t['departure_time'] ?></td><td><span class="badge badge-status bg-<?= $t['status']=='completed'?'success':($t['status']=='cancelled'?'danger':'info') ?>"><?= $t['status'] ?></span></td>
        <td class="text-center"><?php if($t['status']=='scheduled'): ?><button class="btn btn-sm btn-info btn-action" onclick="closeTrip(<?= $t['id'] ?>)">Close</button> <a href="?cancel=<?= $t['id'] ?>" class="btn btn-sm btn-warning btn-action" onclick="return confirm('Cancel trip?')"><i class="fas fa-times"></i></a><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Add Trip Modal -->
<div class="modal fade" id="tripModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">New Trip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add"><div class="mb-3"><label>Vehicle</label><select name="vehicle_id" id="tripVehicle" class="form-select" required><?php foreach($vehicles as $v) echo '<option value="'.$v['id'].'">'.$v['registration_number'].'</option>'; ?></select></div><div class="mb-3"><label>Driver</label><select name="driver_id" id="tripDriver" class="form-select" required><?php foreach($drivers as $d) echo '<option value="'.$d['id'].'">'.$d['name'].'</option>'; ?></select></div><div class="mb-3"><label>Route</label><select name="route_id" class="form-select" required><?php foreach($routes as $r) echo '<option value="'.$r['id'].'">'.$r['name'].'</option>'; ?></select></div><div class="row"><div class="col-md-6"><label>Date</label><input type="date" name="trip_date" class="form-control" required></div><div class="col-md-6"><label>Departure Time</label><input type="time" name="departure_time" class="form-control" required></div></div><div class="mb-3"><label>Allocation (optional)</label><select name="allocation_id" class="form-select"><option value="">None</option><?php $allocs = $pdo->query("SELECT a.id, v.registration_number, u.name as dname FROM vehicle_allocations a JOIN vehicles v ON a.vehicle_id=v.id JOIN drivers d ON a.driver_id=d.id JOIN users u ON d.user_id=u.id WHERE a.status='active'")->fetchAll(); foreach($allocs as $a) echo '<option value="'.$a['id'].'">'.$a['registration_number'].' - '.$a['dname'].'</option>'; ?></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Create Trip</button></div></form></div></div></div>
<!-- Close Trip Modal -->
<div class="modal fade" id="closeModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Close Trip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="close" value="1"><input type="hidden" name="id" id="closeId"><div class="mb-3"><label>Arrival Time</label><input type="time" name="arrival_time" class="form-control" required></div><div class="row"><div class="col-md-6"><label>Distance (km)</label><input type="number" step="0.01" name="distance_traveled" class="form-control" required></div><div class="col-md-6"><label>Fuel Consumed (L)</label><input type="number" step="0.01" name="fuel_consumed" class="form-control" required></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-success">Complete Trip</button></div></form></div></div></div>
<script>
function closeTrip(id){ document.getElementById('closeId').value=id; new bootstrap.Modal(document.getElementById('closeModal')).show(); }
$(document).ready(function(){ $('#tripsTable').DataTable({ pageLength:10, responsive:true }); });
</script>
<?php require_once '../includes/footer.php'; ?>