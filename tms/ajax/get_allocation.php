<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT a.*, v.registration_number, u.name as driver_name FROM vehicle_allocations a JOIN vehicles v ON a.vehicle_id=v.id JOIN drivers d ON a.driver_id=d.id JOIN users u ON d.user_id=u.id WHERE a.id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>