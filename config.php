<?php
$host = 'localhost';
$user = 'root';
$password = 'YourNewPassword123!';
$database = 'dbs';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
