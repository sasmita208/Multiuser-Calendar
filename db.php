<?php
$host = "localhost";     // or 127.0.0.1
$dbname = "dbs";
$username = "root";
$password = "YourNewPassword123!";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
