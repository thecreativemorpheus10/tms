<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $email = trim($_POST['email']); $password = $_POST['password'];
    $confirm = $_POST['confirm_password']; $license = trim($_POST['license_number']);
    $phone = trim($_POST['phone']); $address = trim($_POST['address']);
    $hire_date = $_POST['hire_date'] ?? date('Y-m-d'); $license_expiry = $_POST['license_expiry'] ?? null;
    if (empty($name) || empty($email) || empty($password) || empty($license)) $error = 'All required fields.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $error = 'Email already registered.';
        else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'driver', 'pending')");
                $stmt->execute([$name, $email, $hashed]);
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number, phone, address, hire_date, license_expiry) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $license, $phone, $address, $hire_date, $license_expiry]);
                $pdo->commit();
                $success = 'Registration successful! Wait for admin approval.';
                logActivity(null, 'Driver Registration', "Driver $name ($email) registered pending.");
            } catch (Exception $e) { $pdo->rollBack(); $error = 'Registration failed: '.$e->getMessage(); }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Register as Driver</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    body{background:#f4f6f9;display:flex;align-items:center;min-height:100vh;}
    .register-card{max-width:600px;margin:auto;padding:20px;background:white;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
    .register-card .logo{text-align:center;margin-bottom:20px;}
    .register-card .logo i{font-size:3rem;color:#1a3b5d;}
</style>
</head>
<body>
<div class="register-card">
    <div class="logo"><i class="fas fa-user-plus"></i><h3>Driver Registration</h3></div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <div class="text-center mt-3">
            <a href="../auth/login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Go to Login</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Full Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Password *</label><input type="password" name="password" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Confirm Password *</label><input type="password" name="confirm_password" class="form-control" required></div>
            </div>
            <div class="mb-3"><label>License Number *</label><input type="text" name="license_number" class="form-control" required></div>
            <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="mb-3"><label>Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <div class="row">
                <div class="col-md-6"><label>Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-6"><label>License Expiry</label><input type="date" name="license_expiry" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">Register</button>
            <div class="text-center mt-3">
                <a href="../auth/login.php">Already have an account? Login</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>