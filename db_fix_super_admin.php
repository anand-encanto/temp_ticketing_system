<?php

/**
 * Fix Super Admin Role on Live Server
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');
echo "--- McDonald's Ticketing System: Super Admin Fix ---\n\n";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

echo "Updating 'role' column ENUM to include 'super_admin'... ";
$alter_sql = "ALTER TABLE users MODIFY COLUMN role ENUM('standard', 'department_head', 'admin', 'executive', 'super_admin') NOT NULL DEFAULT 'standard'";
if (mysqli_query($conn, $alter_sql)) {
    echo "SUCCESS\n";
} else {
    echo "FAILED: " . mysqli_error($conn) . "\n";
}

$email = 'super_admin@mcdonalds.mu';
echo "Setting role to 'super_admin' for user $email... ";
$update_sql = "UPDATE users SET role = 'super_admin', is_active = 1 WHERE email = '$email'";
if (mysqli_query($conn, $update_sql)) {
    echo "SUCCESS\n";
} else {
    echo "FAILED: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
echo "\n--- Process Finished ---";
