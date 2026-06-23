<?php
require_once '../config/db.php';
$vehicle_id = (int)$_GET['vehicle_id'];
$stmt = $pdo->prepare("SELECT current_odometer FROM vehicles WHERE id=?");
$stmt->execute([$vehicle_id]);
$row = $stmt->fetch();
echo json_encode(['odometer' => $row ? $row['current_odometer'] : 0]);
?>