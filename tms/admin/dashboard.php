<?php
require_once '../includes/header.php';
requireRole('admin');

// Fetch statistics
$totalVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$activeVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'active'")->fetchColumn();
$totalDrivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
$activeDrivers = $pdo->query("SELECT COUNT(*) FROM drivers WHERE status = 'active'")->fetchColumn();
$activeTrips = $pdo->query("SELECT COUNT(*) FROM trips WHERE status IN ('scheduled','ongoing')")->fetchColumn();
$underMaintenance = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'under_maintenance'")->fetchColumn();

// Vehicle status for chart
$vehicleStatus = getVehicleStatusCounts();
$statusLabels = array_map('ucfirst', array_column($vehicleStatus, 'status'));
$statusCounts = array_column($vehicleStatus, 'count');

$recentTrips = getRecentTrips(5);
?>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $totalVehicles ?></div>
                    <div class="stat-label">Total Vehicles</div>
                    <div class="stat-sub"><i class="fas fa-check-circle me-1"></i><?= $activeVehicles ?> available</div>
                </div>
                <div class="stat-icon"><i class="fas fa-car"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #28a745;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $totalDrivers ?></div>
                    <div class="stat-label">Total Drivers</div>
                    <div class="stat-sub"><i class="fas fa-check-circle me-1"></i><?= $activeDrivers ?> available</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #ffc107;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $activeTrips ?></div>
                    <div class="stat-label">Active Trips</div>
                    <div class="stat-sub text-danger"><i class="fas fa-clock me-1"></i><?= $activeTrips ?> ongoing</div>
                </div>
                <div class="stat-icon"><i class="fas fa-road"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #dc3545;">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-number"><?= $underMaintenance ?></div>
                    <div class="stat-label">Under Maintenance</div>
                    <div class="stat-sub text-danger"><i class="fas fa-tools me-1"></i><?= $underMaintenance ?> vehicles</div>
                </div>
                <div class="stat-icon"><i class="fas fa-wrench"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart and Quick Actions -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Vehicle Status</h5>
            </div>
            <div class="card-body">
                <canvas id="vehicleStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center quick-actions">
                <div class="row g-3">
                    <div class="col-6"><a href="vehicles.php" class="btn btn-outline-primary w-100"><i class="fas fa-car me-2"></i>Manage Vehicles</a></div>
                    <div class="col-6"><a href="drivers.php" class="btn btn-outline-success w-100"><i class="fas fa-users me-2"></i>Manage Drivers</a></div>
                    <div class="col-6"><a href="../officer/trips.php" class="btn btn-outline-warning w-100"><i class="fas fa-road me-2"></i>Create Trip</a></div>
                    <div class="col-6"><a href="reports.php" class="btn btn-outline-danger w-100"><i class="fas fa-file-pdf me-2"></i>Generate Reports</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Trips Table -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Recent Trips</h5>
    </div>
    <div class="card-body">
        <?php if(empty($recentTrips)): ?>
            <p class="text-muted">No trips recorded yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentTrips as $trip): ?>
                            <tr>
                                <td><?= htmlspecialchars($trip['registration_number']) ?></td>
                                <td><?= htmlspecialchars($trip['driver_name']) ?></td>
                                <td><?= htmlspecialchars($trip['route_name']) ?></td>
                                <td><?= $trip['trip_date'] ?></td>
                                <td><span class="badge <?= $trip['status'] == 'completed' ? 'bg-success' : ($trip['status'] == 'cancelled' ? 'bg-danger' : 'bg-warning') ?>"><?= ucfirst($trip['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Vehicle Status Pie Chart
    const ctx = document.getElementById('vehicleStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>