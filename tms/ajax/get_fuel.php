<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT f.*, v.registration_number FROM fuel_records f JOIN vehicles v ON f.vehicle_id=v.id WHERE f.id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>