<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) $error = 'Please fill all fields.';
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') $error = 'Account not active.';
            else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                logActivity($user['id'], 'Login', 'User logged in');
                $services = getUpcomingServices();
                if (!empty($services)) {
                    $subject = 'Vehicle Service Reminders';
                    $body = 'Vehicles due within 7 days:<br><ul>';
                    foreach ($services as $v) $body .= '<li>'.$v['registration_number'].' - due on '.$v['next_service_date'].'</li>';
                    $body .= '</ul>';
                    $recipients = array_map('trim', explode(',', ALERT_RECIPIENTS));
                    foreach ($recipients as $rec) sendMail($rec, $subject, $body);
                }
                switch ($user['role']) {
                    case 'admin': header('Location: ../admin/dashboard.php'); break;
                    case 'officer': header('Location: ../officer/trips.php'); break;
                    case 'driver': header('Location: ../driver/dashboard.php'); break;
                }
                exit;
            }
        } else $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>TMS Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>body{background:#f4f6f9;display:flex;align-items:center;min-height:100vh;}
.login-card{max-width:400px;margin:auto;padding:30px;background:white;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
.login-card .logo{text-align:center;margin-bottom:25px;}
.login-card .logo i{font-size:3rem;color:#1a3b5d;}
.btn-primary{background:#1a3b5d;border-color:#1a3b5d;}
.register-link{text-align:center;margin-top:15px;}
</style>
</head>
<body>
<div class="login-card">
    <div class="logo"><i class="fas fa-truck"></i><h3>Transport Management System</h3></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="register-link">Don't have an account? <a href="../driver/register_driver.php">Register as Driver</a></div>
</div>
</body>
</html>