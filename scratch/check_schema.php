<?php
// Use local credentials from .env
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$db   = 'ticketing_system';

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("FAILURE: Could not connect to database.\n" . mysqli_connect_error());
}

echo "Table structure for users:\n";
$result = mysqli_query($conn, "DESCRIBE users");
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}

mysqli_close($conn);
