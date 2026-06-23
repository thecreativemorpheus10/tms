<?php
// If a report type is requested, generate PDF and exit (NO HTML output)
if (isset($_GET['type'])) {
    require_once '../config/db.php';
    require_once '../includes/functions.php';
    require_once '../includes/fpdf.php';

    $type = $_GET['type'];

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Report: ' . ucfirst(str_replace('_', ' ', $type)));
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);

    switch ($type) {
        case 'vehicles':
            $data = $pdo->query("SELECT * FROM vehicles")->fetchAll();
            $pdf->Cell(30, 10, 'Reg', 1);
            $pdf->Cell(30, 10, 'Model', 1);
            $pdf->Cell(30, 10, 'Make', 1);
            $pdf->Cell(30, 10, 'Status', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(30, 10, $row['registration_number'], 1);
                $pdf->Cell(30, 10, $row['model'], 1);
                $pdf->Cell(30, 10, $row['make'], 1);
                $pdf->Cell(30, 10, $row['status'], 1);
                $pdf->Ln();
            }
            break;

        case 'drivers':
            $data = $pdo->query("SELECT d.*, u.name, u.email FROM drivers d JOIN users u ON d.user_id = u.id")->fetchAll();
            $pdf->Cell(30, 10, 'ID', 1);
            $pdf->Cell(40, 10, 'Name', 1);
            $pdf->Cell(50, 10, 'Email', 1);
            $pdf->Cell(40, 10, 'License', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(30, 10, $row['id'], 1);
                $pdf->Cell(40, 10, $row['name'], 1);
                $pdf->Cell(50, 10, $row['email'], 1);
                $pdf->Cell(40, 10, $row['license_number'], 1);
                $pdf->Ln();
            }
            break;

        case 'trips':
            $data = $pdo->query("SELECT t.*, v.registration_number, u.name as driver_name, r.name as route_name 
                                 FROM trips t 
                                 JOIN vehicles v ON t.vehicle_id = v.id 
                                 JOIN drivers d ON t.driver_id = d.id 
                                 JOIN users u ON d.user_id = u.id 
                                 JOIN routes r ON t.route_id = r.id")->fetchAll();
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(30, 10, 'Vehicle', 1);
            $pdf->Cell(30, 10, 'Driver', 1);
            $pdf->Cell(30, 10, 'Route', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(25, 10, 'Status', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(20, 10, $row['id'], 1);
                $pdf->Cell(30, 10, $row['registration_number'], 1);
                $pdf->Cell(30, 10, $row['driver_name'], 1);
                $pdf->Cell(30, 10, $row['route_name'], 1);
                $pdf->Cell(25, 10, $row['trip_date'], 1);
                $pdf->Cell(25, 10, $row['status'], 1);
                $pdf->Ln();
            }
            break;

        case 'fuel':
            $data = $pdo->query("SELECT f.*, v.registration_number FROM fuel_records f JOIN vehicles v ON f.vehicle_id = v.id")->fetchAll();
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(30, 10, 'Vehicle', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(25, 10, 'Liters', 1);
            $pdf->Cell(25, 10, 'Cost', 1);
            $pdf->Cell(30, 10, 'Odometer', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(20, 10, $row['id'], 1);
                $pdf->Cell(30, 10, $row['registration_number'], 1);
                $pdf->Cell(25, 10, $row['date'], 1);
                $pdf->Cell(25, 10, $row['liters'], 1);
                $pdf->Cell(25, 10, $row['cost'], 1);
                $pdf->Cell(30, 10, $row['odometer_reading'], 1);
                $pdf->Ln();
            }
            break;

        case 'maintenance':
            $data = $pdo->query("SELECT m.*, v.registration_number FROM maintenance_records m JOIN vehicles v ON m.vehicle_id = v.id")->fetchAll();
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(30, 10, 'Vehicle', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(25, 10, 'Type', 1);
            $pdf->Cell(40, 10, 'Description', 1);
            $pdf->Cell(25, 10, 'Cost', 1);
            $pdf->Cell(25, 10, 'Status', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(20, 10, $row['id'], 1);
                $pdf->Cell(30, 10, $row['registration_number'], 1);
                $pdf->Cell(25, 10, $row['date'], 1);
                $pdf->Cell(25, 10, $row['type'], 1);
                $pdf->Cell(40, 10, substr($row['description'], 0, 20), 1);
                $pdf->Cell(25, 10, $row['cost'], 1);
                $pdf->Cell(25, 10, $row['status'], 1);
                $pdf->Ln();
            }
            break;

        case 'fleet_utilization':
            $data = $pdo->query("SELECT v.registration_number, COUNT(t.id) as trip_count 
                                 FROM vehicles v 
                                 LEFT JOIN trips t ON v.id = t.vehicle_id 
                                 GROUP BY v.id")->fetchAll();
            $pdf->Cell(60, 10, 'Vehicle', 1);
            $pdf->Cell(40, 10, 'Trips Count', 1);
            $pdf->Ln();
            foreach ($data as $row) {
                $pdf->Cell(60, 10, $row['registration_number'], 1);
                $pdf->Cell(40, 10, $row['trip_count'], 1);
                $pdf->Ln();
            }
            break;

        default:
            $pdf->Cell(40, 10, 'No data for this report type.');
    }

    $pdf->Output('I', $type . '_report.pdf');
    exit;
}

// ---------- If no report type, show the admin interface ----------
require_once '../includes/header.php';
requireRole('admin');
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Generate Reports</h1>
</div>
<div class="row">
    <div class="col-md-3 mb-3"><a href="?type=vehicles" class="btn btn-primary w-100"><i class="fas fa-car"></i> Vehicles</a></div>
    <div class="col-md-3 mb-3"><a href="?type=drivers" class="btn btn-primary w-100"><i class="fas fa-users"></i> Drivers</a></div>
    <div class="col-md-3 mb-3"><a href="?type=trips" class="btn btn-primary w-100"><i class="fas fa-road"></i> Trips</a></div>
    <div class="col-md-3 mb-3"><a href="?type=fuel" class="btn btn-primary w-100"><i class="fas fa-gas-pump"></i> Fuel</a></div>
    <div class="col-md-3 mb-3"><a href="?type=maintenance" class="btn btn-primary w-100"><i class="fas fa-tools"></i> Maintenance</a></div>
    <div class="col-md-3 mb-3"><a href="?type=fleet_utilization" class="btn btn-primary w-100"><i class="fas fa-chart-bar"></i> Fleet Utilization</a></div>
</div>
<?php require_once '../includes/footer.php'; ?>