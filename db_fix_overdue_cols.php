<?php

/**
 * Fix: Add Overdue Tracking columns to tickets
 */

$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

header('Content-Type: text/plain');
echo "--- McDonald's Ticketing System: SLA Tracking Fix ---\n\n";

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

// Add is_overdue
if (!columnExists($conn, 'tickets', 'is_overdue')) {
    echo "Adding 'is_overdue' column... ";
    $sql = "ALTER TABLE `tickets` ADD COLUMN `is_overdue` TINYINT(1) DEFAULT 0 AFTER `due_at` ";
    mysqli_query($conn, $sql);
    echo "SUCCESS\n";
}

// Add last_reminder_sent_at
if (!columnExists($conn, 'tickets', 'last_reminder_sent_at')) {
    echo "Adding 'last_reminder_sent_at' column... ";
    $sql = "ALTER TABLE `tickets` ADD COLUMN `last_reminder_sent_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_overdue` ";
    mysqli_query($conn, $sql);
    echo "SUCCESS\n";
}

mysqli_close($conn);
echo "\n--- Fix Process Finished ---";
