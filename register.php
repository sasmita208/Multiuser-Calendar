<?php
$host = "localhost";
$dbname = "dbs";
$user = "root";
$pass = "YourNewPassword123!";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $password_input = $_POST['password'] ?? '';

    $hashed_password = $password_input; // Now storing as plain text

$stmt = $conn->prepare("INSERT INTO users (email,name, password, created_at) VALUES (?, ?, ?, NOW())");

    $stmt->bind_param("sss", $email, $name, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href = 'login.html';</script>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
