-- --------------------------------------------------------
-- Host: 127.0.0.1
-- Generation Time: 
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: departments
-- --------------------------------------------------------
CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departments` (`id`, `name`, `created_at`) VALUES
(1, 'Transport', '2025-01-01 00:00:00'),
(2, 'Logistics', '2025-01-01 00:00:00');

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','officer','driver') NOT NULL,
  `status` enum('active','pending','inactive') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Passwords are 'admin123' (hashed using password_hash)
INSERT INTO `users` (`id`, `department_id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 1, 'Admin User', 'admin@tms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', '2025-01-01 00:00:00'),
(2, 1, 'Officer User', 'officer@tms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 'active', '2025-01-01 00:00:00'),
(3, 1, 'Driver User', 'driver@tms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'active', '2025-01-01 00:00:00');

-- --------------------------------------------------------
-- Table: vehicles
-- --------------------------------------------------------
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `model` varchar(50) NOT NULL,
  `make` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `fuel_type` enum('Petrol','Diesel','Electric','Hybrid') DEFAULT 'Petrol',
  `status` enum('active','inactive','under_maintenance') NOT NULL DEFAULT 'active',
  `current_odometer` int(11) DEFAULT 0,
  `last_service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `vehicles` (`id`, `registration_number`, `model`, `make`, `year`, `capacity`, `fuel_type`, `status`, `current_odometer`, `last_service_date`, `next_service_date`) VALUES
(1, 'KA-01-AB-1234', 'Innova', 'Toyota', 2020, 7, 'Diesel', 'active', 45000, '2025-02-01', '2025-05-01'),
(2, 'KA-02-CD-5678', 'Scorpio', 'Mahindra', 2019, 6, 'Diesel', 'active', 62000, '2025-01-15', '2025-04-15'),
(3, 'KA-03-EF-9012', 'City', 'Honda', 2021, 5, 'Petrol', 'under_maintenance', 28000, '2025-03-01', '2025-06-01');

-- --------------------------------------------------------
-- Table: drivers
-- --------------------------------------------------------
CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `drivers` (`id`, `user_id`, `license_number`, `phone`, `address`, `hire_date`, `license_expiry`, `status`) VALUES
(1, 3, 'DL-123456', '9876543210', '123 Main St, City', '2025-01-01', '2026-01-01', 'active');

-- --------------------------------------------------------
-- Table: routes
-- --------------------------------------------------------
CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_location` varchar(100) NOT NULL,
  `end_location` varchar(100) NOT NULL,
  `distance` decimal(10,2) NOT NULL,
  `estimated_duration` int(11) NOT NULL COMMENT 'in minutes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `routes` (`id`, `name`, `start_location`, `end_location`, `distance`, `estimated_duration`) VALUES
(1, 'City Center - Airport', 'City Center', 'Airport', 25.50, 45),
(2, 'Office - Warehouse', 'Office', 'Warehouse', 12.00, 20);

-- --------------------------------------------------------
-- Table: vehicle_allocations
-- --------------------------------------------------------
CREATE TABLE `vehicle_allocations` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No sample allocations initially.

-- --------------------------------------------------------
-- Table: trips
-- --------------------------------------------------------
CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `allocation_id` int(11) DEFAULT NULL,
  `trip_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time DEFAULT NULL,
  `distance_traveled` decimal(10,2) DEFAULT NULL,
  `fuel_consumed` decimal(10,2) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `trips` (`id`, `vehicle_id`, `driver_id`, `route_id`, `allocation_id`, `trip_date`, `departure_time`, `arrival_time`, `distance_traveled`, `fuel_consumed`, `status`, `created_by`) VALUES
(1, 1, 1, 1, NULL, '2025-04-01', '09:00:00', '10:00:00', 25.50, 3.20, 'completed', 2),
(2, 2, 1, 2, NULL, '2025-04-02', '14:00:00', NULL, NULL, NULL, 'scheduled', 2);

-- --------------------------------------------------------
-- Table: fuel_records
-- --------------------------------------------------------
CREATE TABLE `fuel_records` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `liters` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `odometer_reading` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `fuel_records` (`id`, `vehicle_id`, `trip_id`, `date`, `liters`, `cost`, `odometer_reading`, `created_by`) VALUES
(1, 1, 1, '2025-04-01', 10.00, 900.00, 45000, 2);

-- --------------------------------------------------------
-- Table: maintenance_records
-- --------------------------------------------------------
CREATE TABLE `maintenance_records` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `type` enum('routine','repair','inspection','other') NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `maintenance_records` (`id`, `vehicle_id`, `date`, `type`, `description`, `cost`, `status`, `created_by`) VALUES
(1, 1, '2025-03-28', 'routine', 'Oil change and filter', 2500.00, 'completed', 2);

-- --------------------------------------------------------
-- Table: activity_logs
-- --------------------------------------------------------
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Indexes and AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `departments` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `department_id` (`department_id`);
ALTER TABLE `vehicles` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `registration_number` (`registration_number`);
ALTER TABLE `drivers` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `user_id` (`user_id`);
ALTER TABLE `routes` ADD PRIMARY KEY (`id`);
ALTER TABLE `vehicle_allocations` ADD PRIMARY KEY (`id`), ADD KEY `vehicle_id` (`vehicle_id`), ADD KEY `driver_id` (`driver_id`), ADD KEY `assigned_by` (`assigned_by`);
ALTER TABLE `trips` ADD PRIMARY KEY (`id`), ADD KEY `vehicle_id` (`vehicle_id`), ADD KEY `driver_id` (`driver_id`), ADD KEY `route_id` (`route_id`), ADD KEY `allocation_id` (`allocation_id`), ADD KEY `created_by` (`created_by`);
ALTER TABLE `fuel_records` ADD PRIMARY KEY (`id`), ADD KEY `vehicle_id` (`vehicle_id`), ADD KEY `trip_id` (`trip_id`), ADD KEY `created_by` (`created_by`);
ALTER TABLE `maintenance_records` ADD PRIMARY KEY (`id`), ADD KEY `vehicle_id` (`vehicle_id`), ADD KEY `created_by` (`created_by`);
ALTER TABLE `activity_logs` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);

ALTER TABLE `departments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `vehicles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `drivers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `routes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `vehicle_allocations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `trips` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `fuel_records` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `maintenance_records` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `activity_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
ALTER TABLE `drivers` ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `vehicle_allocations` ADD CONSTRAINT `vehicle_allocations_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
                        ADD CONSTRAINT `vehicle_allocations_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
                        ADD CONSTRAINT `vehicle_allocations_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `trips` ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
                    ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
                    ADD CONSTRAINT `trips_ibfk_3` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
                    ADD CONSTRAINT `trips_ibfk_4` FOREIGN KEY (`allocation_id`) REFERENCES `vehicle_allocations` (`id`) ON DELETE SET NULL,
                    ADD CONSTRAINT `trips_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `fuel_records` ADD CONSTRAINT `fuel_records_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
                          ADD CONSTRAINT `fuel_records_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL,
                          ADD CONSTRAINT `fuel_records_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `maintenance_records` ADD CONSTRAINT `maintenance_records_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
                                  ADD CONSTRAINT `maintenance_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

COMMIT;