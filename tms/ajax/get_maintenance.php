<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT m.*, v.registration_number FROM maintenance_records m JOIN vehicles v ON m.vehicle_id=v.id WHERE m.id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>