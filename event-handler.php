<?php
$servername = "localhost";
$username = "root";
$password = "YourNewPassword123!";
$dbname = "dbs";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get events
$events = [];
$event_sql = "SELECT * FROM event ORDER BY event_date ASC";
$event_result = $conn->query($event_sql);
if ($event_result->num_rows > 0) {
    while ($row = $event_result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get notifications
$notifications = [];
$notif_sql = "SELECT * FROM notification ORDER BY created_at DESC";
$notif_result = $conn->query($notif_sql);
if ($notif_result->num_rows > 0) {
    while ($row = $notif_result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Send JSON response
echo json_encode([
    "events" => $events,
    "notifications" => $notifications
]);

$conn->close();
?>
