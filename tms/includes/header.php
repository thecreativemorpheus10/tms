<?php
require_once __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Determine active page for sidebar highlighting
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS Portal – <?= ucfirst($role) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables CSS & JS + Responsive extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ===== Global Reset ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ===== Layout ===== */
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #fff;
            padding: 20px 0;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-brand {
            text-align: center;
            padding: 10px 0 20px;
            border-bottom: 1px solid #1a2a3a;
        }
        .sidebar-brand h3 {
            font-weight: 300;
            margin: 0;
        }
        .sidebar-brand i {
            font-size: 2rem;
            display: block;
            margin-bottom: 5px;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .sidebar-menu li {
            padding: 10px 20px;
        }
        .sidebar-menu li a {
            color: #b0c4de;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.2s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            color: #fff;
            background: #1a2a3a;
            padding: 8px 12px;
            border-radius: 8px;
        }
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }

        /* ===== Main Content ===== */
        .main-content {
            flex: 1;
            padding: 20px 30px;
        }

        /* ===== Top Bar ===== */
        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .topbar .welcome {
            font-size: 1.2rem;
            font-weight: 500;
        }
        .topbar .welcome small {
            color: #6c757d;
            font-weight: 400;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .topbar .user-info .avatar {
            width: 40px;
            height: 40px;
            background: #1a3b5d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
        }

        /* ===== Footer ===== */
        .footer {
            background: #fff;
            border-radius: 12px;
            padding: 15px 20px;
            margin-top: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .footer .user-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .footer .user-details .avatar-sm {
            width: 32px;
            height: 32px;
            background: #1a3b5d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .footer .user-details .info {
            line-height: 1.2;
        }
        .footer .user-details .info .name {
            font-weight: 600;
        }
        .footer .user-details .info .role {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .footer .user-details .info .email {
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* ===== Responsive Tweaks ===== */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                display: none; /* Toggle with hamburger if needed */
            }
            .wrapper {
                flex-direction: column;
            }
            .main-content {
                padding: 15px;
            }
            .footer {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }

        /* ===== Table Enhancements (from earlier) ===== */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            background: white;
            padding: 0.5rem;
        }
        .table-container table {
            margin-bottom: 0;
            width: 100%;
            min-width: 600px;
        }
        .table thead th {
            background: #f8f9fa;
            color: #1a3b5d;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.03rem;
            white-space: nowrap;
        }
        .table tbody td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .table tbody tr:hover {
            background-color: #f1f4f8;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
            border-radius: 0.25rem;
            margin: 0 0.15rem;
        }
        .btn-action i {
            margin-right: 0;
        }
        .badge-status {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
            font-size: 0.75rem;
            border-radius: 20px;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: 0.2s;
            border-left: 4px solid #1a3b5d;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-card .stat-icon {
            font-size: 2rem;
            color: #1a3b5d;
            opacity: 0.7;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .stat-card .stat-sub {
            font-size: 0.85rem;
            color: #28a745;
        }
        .stat-card .stat-sub.text-danger {
            color: #dc3545;
        }
        .quick-actions .btn {
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: 0.2s;
        }
        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- ===== Sidebar ===== -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-truck"></i>
            <h3>TMS Portal</h3>
        </div>
        <ul class="sidebar-menu">
            <?php
            // Define menu items based on role
            $menu = [];
            if ($role === 'admin') {
                $menu = [
                    'dashboard'    => ['Dashboard', 'fa-tachometer-alt'],
                    'vehicles'     => ['Vehicles', 'fa-car'],
                    'drivers'      => ['Drivers', 'fa-users'],
                    'allocations'  => ['Allocations', 'fa-calendar-check'],
                    'routes'       => ['Routes', 'fa-route'],
                    'pending_drivers' => ['Pending Drivers', 'fa-user-plus'],
                    'reports'      => ['Reports', 'fa-file-pdf']
                ];
            } elseif ($role === 'officer') {
                $menu = [
                    'trips'        => ['Trips', 'fa-road'],
                    'fuel'         => ['Fuel Logs', 'fa-gas-pump'],
                    'maintenance'  => ['Maintenance', 'fa-tools']
                ];
            } elseif ($role === 'driver') {
                $menu = [
                    'dashboard'    => ['Dashboard', 'fa-tachometer-alt'],
                    'my_trips'     => ['My Trips', 'fa-list'],
                    'my_profile'   => ['My Profile', 'fa-user-edit']
                ];
            }

            // Build links
            foreach ($menu as $file => $item) {
                $active = ($currentPage === $file) ? 'active' : '';
                // Adjust paths: admin pages are in same folder, officer/driver in subfolders
                $link = $file . '.php';
                // If the current role is not admin, the pages are in the respective subfolder (e.g., officer/trips.php)
                // But we are already in that folder, so relative path works.
                echo "<li class='$active'><a href='$link'><i class='fas {$item[1]}'></i> {$item[0]}</a></li>";
            }
            ?>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- ===== Main Content ===== -->
    <main class="main-content">
        <!-- ===== Top Bar ===== -->
        <div class="topbar">
            <div class="welcome">
                <i class="fas fa-home me-2"></i> <?= ucfirst($currentPage) ?>
                <small>– Welcome, <?= htmlspecialchars($_SESSION['name']) ?></small>
            </div>
            <div class="user-info">
                <span><i class="fas fa-bell"></i></span>
                <span><i class="fas fa-cog"></i></span>
                <div class="avatar"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
            </div>
        </div>
        <!-- Page content starts here -->