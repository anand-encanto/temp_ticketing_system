<?php

/**
 * Fix: Add due_at column to tickets
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');
echo "--- McDonald's Ticketing System: Due Date Fix ---\n\n";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

// Helper to check if column exists
function columnExists($conn, $table, $column)
{
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($res) > 0;
}

if (!columnExists($conn, 'tickets', 'due_at')) {
    echo "Adding 'due_at' column to tickets table... ";
    $sql = "ALTER TABLE `tickets` ADD COLUMN `due_at` TIMESTAMP NULL DEFAULT NULL AFTER `closed_at` ";
    if (mysqli_query($conn, $sql)) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column 'due_at' already exists. Skipping.\n";
}

mysqli_close($conn);
echo "\n--- Fix Process Finished ---";
