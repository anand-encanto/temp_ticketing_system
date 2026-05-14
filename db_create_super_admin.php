<?php

/**
 * Create Super Admin User
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');
echo "--- McDonald's Ticketing System: Super Admin Creation ---\n\n";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

$name = 'Super Admin';
$email = 'super_admin@mcdonalds.mu';
$username = 'super_admin';
$password = password_hash('password', PASSWORD_BCRYPT);
$role = 'super_admin';
$location_id = 1; // Default to first location

// Ensure the role column supports 'super_admin'
mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN role ENUM('standard', 'department_head', 'admin', 'executive', 'super_admin') NOT NULL DEFAULT 'standard'");

// Check if user already exists
$check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' OR username = '$username'");
if (mysqli_num_rows($check) > 0) {
    echo "User already exists. Updating role to super_admin... ";
    $sql = "UPDATE users SET role = '$role', is_active = 1 WHERE email = '$email' OR username = '$username'";
} else {
    echo "Creating new Super Admin user... ";
    $sql = "INSERT INTO users (name, email, username, password, role, location_id, is_active) 
            VALUES ('$name', '$email', '$username', '$password', '$role', $location_id, 1)";
}

if (mysqli_query($conn, $sql)) {
    echo "SUCCESS\n";
    echo "\nCredentials:\n";
    echo "Username/Email: $email\n";
    echo "Password: password\n";
} else {
    echo "FAILED: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
echo "\n--- Process Finished ---";
