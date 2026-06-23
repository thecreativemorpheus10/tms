<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
?>