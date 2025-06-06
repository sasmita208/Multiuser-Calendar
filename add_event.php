<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$host = "localhost";
$user = "root";
$pass = "YourNewPassword123!";
$db = "dbs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$title = $data['title'];
$description = $data['description'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$creator_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO event (title, description, start_time, end_time, creator_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $creator_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "event_id" => $stmt->insert_id]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
