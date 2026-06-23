<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$date = $_GET['date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT d.*, u.name FROM drivers d JOIN users u ON d.user_id=u.id WHERE u.status='active' AND NOT EXISTS (SELECT 1 FROM trips t WHERE t.driver_id=d.id AND t.trip_date=? AND t.status NOT IN ('completed','cancelled'))");
$stmt->execute([$date]);
echo json_encode($stmt->fetchAll());
?>