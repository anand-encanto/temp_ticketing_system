<?php
$host = 'localhost';
$user = 'mudonald_ticketing';
$pass = 'g.+gLE!tpleL';
$db   = 'mudonald_ticket';

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

$email = 'super_admin@mcdonalds.mu';
$query = "SELECT id, name, email, role, is_active FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
} else {
    echo "User not found.\n";
}

mysqli_close($conn);
