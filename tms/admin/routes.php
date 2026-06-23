<?php
require_once '../includes/header.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $start = trim($_POST['start_location']);
    $end = trim($_POST['end_location']);
    $distance = (float)$_POST['distance'];
    $duration = (int)$_POST['estimated_duration'];
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO routes (name, start_location, end_location, distance, estimated_duration) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $start, $end, $distance, $duration]);
        logActivity($_SESSION['user_id'], 'Add Route', "Added route $name");
        $_SESSION['message'] = 'Route added.';
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE routes SET name=?, start_location=?, end_location=?, distance=?, estimated_duration=? WHERE id=?");
        $stmt->execute([$name, $start, $end, $distance, $duration, $id]);
        logActivity($_SESSION['user_id'], 'Edit Route', "Edited route ID $id");
        $_SESSION['message'] = 'Route updated.';
    }
    header('Location: routes.php');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM routes WHERE id=?")->execute([$id]);
    logActivity($_SESSION['user_id'], 'Delete Route', "Deleted route ID $id");
    $_SESSION['message'] = 'Deleted.';
    header('Location: routes.php');
    exit;
}
$routes = $pdo->query("SELECT * FROM routes ORDER BY id DESC")->fetchAll();
$message = $_SESSION['message'] ?? ''; unset($_SESSION['message']);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2"><i class="fas fa-route me-2"></i>Routes</h1><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#routeModal" onclick="resetForm()"><i class="fas fa-plus"></i> Add Route</button></div>
<?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="table-container">
    <table id="routesTable" class="table table-striped table-hover table-bordered">
        <thead><tr><th>ID</th><th>Name</th><th>Start</th><th>End</th><th>Distance (km)</th><th>Duration (min)</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($routes as $r): ?><tr><td><?= $r['id'] ?></td><td><?= htmlspecialchars($r['name']) ?></td><td><?= htmlspecialchars($r['start_location']) ?></td><td><?= htmlspecialchars($r['end_location']) ?></td><td><?= $r['distance'] ?></td><td><?= $r['estimated_duration'] ?></td><td class="text-center"><button class="btn btn-sm btn-warning btn-action" onclick="editRoute(<?= htmlspecialchars(json_encode($r)) ?>)"><i class="fas fa-edit"></i></button> <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="routeModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title" id="routeModalTitle">Add Route</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" id="routeAction" value="add"><input type="hidden" name="id" id="routeId" value="0"><div class="mb-3"><label>Name *</label><input type="text" name="name" id="routeName" class="form-control" required></div><div class="row"><div class="col-md-6"><label>Start Location *</label><input type="text" name="start_location" id="routeStart" class="form-control" required></div><div class="col-md-6"><label>End Location *</label><input type="text" name="end_location" id="routeEnd" class="form-control" required></div></div><div class="row"><div class="col-md-6"><label>Distance (km) *</label><input type="number" step="0.01" name="distance" id="routeDistance" class="form-control" required></div><div class="col-md-6"><label>Est. Duration (min) *</label><input type="number" name="estimated_duration" id="routeDuration" class="form-control" required></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<script>
function resetForm(){ document.getElementById('routeAction').value='add'; document.getElementById('routeId').value=0; document.getElementById('routeModalTitle').innerText='Add Route'; ['routeName','routeStart','routeEnd','routeDistance','routeDuration'].forEach(id=>document.getElementById(id).value=''); }
function editRoute(data){ document.getElementById('routeAction').value='edit'; document.getElementById('routeId').value=data.id; document.getElementById('routeModalTitle').innerText='Edit Route'; document.getElementById('routeName').value=data.name; document.getElementById('routeStart').value=data.start_location; document.getElementById('routeEnd').value=data.end_location; document.getElementById('routeDistance').value=data.distance; document.getElementById('routeDuration').value=data.estimated_duration; new bootstrap.Modal(document.getElementById('routeModal')).show(); }
$(document).ready(function(){ $('#routesTable').DataTable({ pageLength:10, responsive:true }); });
</script>
<?php require_once '../includes/footer.php'; ?>