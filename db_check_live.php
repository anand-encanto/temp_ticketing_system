<?php
/**
 * Database Connection Test Script
 * Upload this to your live server to verify access.
 * DELETE THIS FILE AFTER USE.
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');

echo "--- McDonald's Ticketing System: Live DB Connection Test ---\n";
echo "Testing connection to: $db at $host\n\n";

// 1. Attempt Connection
$conn = @mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("FAILURE: Could not connect to MySQL server.\nError: " . mysqli_connect_error());
}

echo "SUCCESS: Connected to MySQL server successfully.\n";

// 2. Attempt to select the database
if (mysqli_select_db($conn, $db)) {
    echo "SUCCESS: Database '$db' selected successfully.\n";
    
    // 3. Optional: Check if the 'tickets' table exists to verify project state
    $res = mysqli_query($conn, "SHOW TABLES LIKE 'tickets'");
    if (mysqli_num_rows($res) > 0) {
        echo "INFO: 'tickets' table found. System is ready for updates.\n";
    } else {
        echo "WARNING: 'tickets' table not found. Is this the correct database?\n";
    }
} else {
    echo "FAILURE: Could not select database '$db'.\nError: " . mysqli_error($conn) . "\n";
    echo "Check if the database name is exactly '$db'.\n";
}

mysqli_close($conn);
echo "\n--- Test Complete ---\n";
echo "IMPORTANT: PLEASE DELETE THIS FILE FROM YOUR SERVER NOW.";
?>