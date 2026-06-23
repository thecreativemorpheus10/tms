<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT t.*, v.registration_number, u.name as driver_name, r.name as route_name FROM trips t JOIN vehicles v ON t.vehicle_id=v.id JOIN drivers d ON t.driver_id=d.id JOIN users u ON d.user_id=u.id JOIN routes r ON t.route_id=r.id WHERE t.id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>