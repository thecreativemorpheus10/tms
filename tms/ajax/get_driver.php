<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>