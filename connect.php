<?php
$servername = "localhost";
$username = "root";        // or your MySQL username
$password = "YourNewPassword123!";            // or your actual password
$dbname = "dbs";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✅ Connected successfully!";
?>
