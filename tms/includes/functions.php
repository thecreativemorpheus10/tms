<?php
require_once __DIR__ . '/../config/db.php';

function logActivity($user_id, $action, $details = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $action, $details, $ip]);
}

function getUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getDriverByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function getDriver($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, u.name, u.email FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getVehicle($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getRoute($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function isVehicleAvailable($vehicle_id, $trip_date, $departure_time, $arrival_time = null) {
    global $pdo;
    $vehicle = getVehicle($vehicle_id);
    if (!$vehicle || $vehicle['status'] !== 'active') return false;
    $sql = "SELECT COUNT(*) FROM trips WHERE vehicle_id = ? AND trip_date = ? AND status NOT IN ('completed','cancelled')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$vehicle_id, $trip_date]);
    return $stmt->fetchColumn() == 0;
}

function isDriverAvailable($driver_id, $trip_date, $departure_time, $arrival_time = null) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM trips WHERE driver_id = ? AND trip_date = ? AND status NOT IN ('completed','cancelled')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$driver_id, $trip_date]);
    return $stmt->fetchColumn() == 0;
}

function sendMail($to, $subject, $body) {
    require_once __DIR__ . '/mailer.php';
    return send_mail($to, $subject, $body);
}

function getPendingDriversCount() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'driver' AND status = 'pending'");
    return $stmt->fetchColumn();
}

function getVehicleStatusCounts() {
    global $pdo;
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM vehicles GROUP BY status");
    return $stmt->fetchAll();
}

function getFuelConsumptionLast6Months() {
    global $pdo;
    $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(liters) as total_liters 
            FROM fuel_records 
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getMaintenanceByType() {
    global $pdo;
    $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM maintenance_records GROUP BY type");
    return $stmt->fetchAll();
}

function getUpcomingServices() {
    global $pdo;
    $sql = "SELECT * FROM vehicles WHERE next_service_date IS NOT NULL 
            AND next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getRecentTrips($limit = 5) {
    global $pdo;
    $limit = (int)$limit; // Force integer to avoid SQL syntax errors
    $sql = "SELECT t.*, v.registration_number, u.name as driver_name, r.name as route_name 
            FROM trips t 
            JOIN vehicles v ON t.vehicle_id = v.id 
            JOIN drivers d ON t.driver_id = d.id 
            JOIN users u ON d.user_id = u.id 
            JOIN routes r ON t.route_id = r.id 
            ORDER BY t.created_at DESC LIMIT $limit";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function hasRole($role) { return isset($_SESSION['role']) && $_SESSION['role'] === $role; }

function requireRole($role) {
    if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
    if ($_SESSION['role'] !== $role) die('Access denied.');
}

function sanitize($input) { return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8'); }

function getDriverName($driver_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.name FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$driver_id]);
    $row = $stmt->fetch();
    return $row ? $row['name'] : 'Unknown';
}
?>