<?php
require_once '../includes/header.php';
requireRole('driver');
$driver = getDriverByUserId($_SESSION['user_id']);
if (!$driver) die('Driver profile not found.');
$trips = $pdo->prepare("SELECT t.*, v.registration_number, r.name as route_name FROM trips t JOIN vehicles v ON t.vehicle_id=v.id JOIN routes r ON t.route_id=r.id WHERE t.driver_id=? ORDER BY t.trip_date DESC");
$trips->execute([$driver['id']]);
$trips = $trips->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-list me-2"></i>My Trips</h1></div>
<div class="table-container">
    <table id="myTripsTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Vehicle</th><th>Route</th><th>Date</th><th>Departure</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($trips as $t): ?><tr><td><?= $t['id'] ?></td><td><?= htmlspecialchars($t['registration_number']) ?></td><td><?= htmlspecialchars($t['route_name']) ?></td><td><?= $t['trip_date'] ?></td><td><?= $t['departure_time'] ?></td><td><span class="badge badge-status bg-<?= $t['status']=='completed'?'success':($t['status']=='cancelled'?'danger':'info') ?>"><?= $t['status'] ?></span></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>$(document).ready(function(){ $('#myTripsTable').DataTable({ pageLength:10, responsive:true }); });</script>
<?php require_once '../includes/footer.php'; ?>