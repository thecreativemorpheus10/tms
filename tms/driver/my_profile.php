<?php
require_once '../includes/header.php';
requireRole('driver');
$driver = getDriverByUserId($_SESSION['user_id']);
if (!$driver) die('Driver profile not found.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $stmt = $pdo->prepare("UPDATE drivers SET phone=?, address=? WHERE id=?");
    $stmt->execute([$phone, $address, $driver['id']]);
    logActivity($_SESSION['user_id'], 'Update Profile', 'Updated driver profile');
    $_SESSION['message'] = 'Profile updated.';
    header('Location: my_profile.php');
    exit;
}
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">My Profile</h1></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="POST">
    <div class="mb-3"><label>Name</label><input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['name']) ?>" disabled></div>
    <div class="mb-3"><label>Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['email']) ?>" disabled></div>
    <div class="mb-3"><label>License Number</label><input type="text" class="form-control" value="<?= htmlspecialchars($driver['license_number']) ?>" disabled></div>
    <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($driver['phone']) ?>"></div>
    <div class="mb-3"><label>Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($driver['address']) ?></textarea></div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
</form>
<?php require_once '../includes/footer.php'; ?>