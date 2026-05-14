<?php

/**
 * Fix: Create SLA Levels Table
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');
echo "--- McDonald's Ticketing System: SLA Table Fix ---\n\n";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

// 1. Create sla_levels table
$sql1 = "CREATE TABLE IF NOT EXISTS `sla_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` varchar(255) NOT NULL,
  `reminder_interval_minutes` int(11) NOT NULL,
  `resolution_time_minutes` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priority_unique` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

echo "Creating sla_levels table... ";
if (mysqli_query($conn, $sql1)) {
    echo "SUCCESS\n";
} else {
    echo "FAILED: " . mysqli_error($conn) . "\n";
}

// 2. Insert default SLA rules
$sql2 = "INSERT IGNORE INTO `sla_levels` (`priority`, `reminder_interval_minutes`, `resolution_time_minutes`) VALUES 
('Urgent', 60, 60),    -- P1: 1 Hour
('High', 120, 120),    -- P2: 2 Hours
('Medium', 240, 240),  -- P3: 4 Hours
('Low', 1440, 1440);   -- P4: 24 Hours";

echo "Inserting default SLA rules... ";
if (mysqli_query($conn, $sql2)) {
    echo "SUCCESS\n";
} else {
    echo "FAILED: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
echo "\n--- Fix Process Finished ---";
