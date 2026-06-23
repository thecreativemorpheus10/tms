<?php
require_once '../includes/header.php';
requireRole('driver');
$driver = getDriverByUserId($_SESSION['user_id']);
if (!$driver) { echo '<div class="alert alert-danger">Driver profile not found.</div>'; require_once '../includes/footer.php'; exit; }
$upcoming = $pdo->prepare("SELECT t.*, v.registration_number, r.name as route_name FROM trips t JOIN vehicles v ON t.vehicle_id=v.id JOIN routes r ON t.route_id=r.id WHERE t.driver_id=? AND t.status IN ('scheduled','ongoing') ORDER BY t.trip_date ASC");
$upcoming->execute([$driver['id']]);
$upcoming = $upcoming->fetchAll();
$history = $pdo->prepare("SELECT * FROM trips WHERE driver_id=? AND status IN ('completed','cancelled') ORDER BY trip_date DESC LIMIT 5");
$history->execute([$driver['id']]);
$history = $history->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">Driver Dashboard</h1></div>
<div class="row">
    <div class="col-md-6"><div class="card"><div class="card-header">Upcoming Trips</div><div class="card-body"><?php if(empty($upcoming)) echo '<p class="text-muted">No upcoming trips.</p>'; else { echo '<ul class="list-group">'; foreach($upcoming as $t) echo '<li class="list-group-item d-flex justify-content-between align-items-center">'.htmlspecialchars($t['registration_number']).' - '.htmlspecialchars($t['route_name']).' <span class="badge bg-info">'.$t['trip_date'].' '.$t['departure_time'].'</span></li>'; echo '</ul>'; } ?></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-header">Recent Trip History</div><div class="card-body"><?php if(empty($history)) echo '<p class="text-muted">No history.</p>'; else { echo '<ul class="list-group">'; foreach($history as $t) echo '<li class="list-group-item d-flex justify-content-between align-items-center">Trip #'.$t['id'].' <span class="badge bg-'.($t['status']=='completed'?'success':'danger').'">'.$t['status'].'</span></li>'; echo '</ul>'; } ?></div></div></div>
</div>
<?php require_once '../includes/footer.php'; ?>