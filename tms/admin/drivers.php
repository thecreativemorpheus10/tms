<?php
require_once '../includes/header.php';
requireRole('admin');

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE drivers SET phone=?, address=?, status=? WHERE id=?");
    $stmt->execute([$phone, $address, $status, $id]);
    logActivity($_SESSION['user_id'], 'Edit Driver', "Updated driver ID $id");
    $_SESSION['message'] = 'Driver updated successfully.';
    header('Location: drivers.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $driver = getDriver($id);
    if ($driver) {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$driver['user_id']]);
        logActivity($_SESSION['user_id'], 'Delete Driver', "Deleted driver {$driver['name']}");
        $_SESSION['message'] = 'Driver deleted.';
    }
    header('Location: drivers.php');
    exit;
}

// Fetch all drivers with user info
$drivers = $pdo->query("
    SELECT d.*, u.name, u.email, u.status as user_status
    FROM drivers d
    JOIN users u ON d.user_id = u.id
    ORDER BY d.id DESC
")->fetchAll();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users me-2"></i>Drivers</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriverModal">
        <i class="fas fa-plus"></i> Add Driver
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Responsive Table Wrapper -->
<div class="table-container">
    <table id="driversTable" class="table table-striped table-hover table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>License</th>
                <th>Phone</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drivers as $d): ?>
                <tr>
                    <td><?= $d['id'] ?></td>
                    <td><?= htmlspecialchars($d['name']) ?></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= htmlspecialchars($d['license_number']) ?></td>
                    <td><?= htmlspecialchars($d['phone']) ?></td>
                    <td>
                        <span class="badge badge-status bg-<?= $d['user_status'] === 'active' ? 'success' : 'danger' ?>">
                            <?= ucfirst($d['user_status']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning btn-action" 
                                onclick="editDriver(<?= htmlspecialchars(json_encode($d)) ?>)"
                                title="Edit Driver">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?= $d['id'] ?>" 
                           class="btn btn-sm btn-danger btn-action"
                           onclick="return confirm('Delete this driver and user account?')"
                           title="Delete Driver">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($drivers)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No drivers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ===== Edit Modal ===== -->
<div class="modal fade" id="editDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editDriverId">

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Add Driver Modal ===== -->
<div class="modal fade" id="addDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="drivers_add.php">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        New drivers can also self‑register. Use this form to add manually.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">License Number *</label>
                        <input type="text" name="license_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editDriver(data) {
        document.getElementById('editDriverId').value = data.id;
        document.getElementById('editPhone').value = data.phone || '';
        document.getElementById('editAddress').value = data.address || '';
        document.getElementById('editStatus').value = data.user_status;
        new bootstrap.Modal(document.getElementById('editDriverModal')).show();
    }

    $(document).ready(function() {
        $('#driversTable').DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [6] } // Disable sorting on actions column
            ],
            language: {
                search: "Search drivers:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ drivers"
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>