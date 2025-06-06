<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

$host = "localhost";
$db = "dbs";
$user = "root";
$pass = "YourNewPassword123!";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit();
}

// Get upcoming events where user is creator or invited
$event_query = "
    SELECT DISTINCT e.*
    FROM event e
    LEFT JOIN eventparticipant ep ON e.id = ep.event_id
    WHERE (e.creator_id = ? OR ep.user_id = ?)
      AND e.start_time >= NOW()
    ORDER BY e.start_time ASC
";

$stmt = $conn->prepare($event_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$event_result = $stmt->get_result();

$events = [];
while ($row = $event_result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

// Get notifications for the logged-in user
$notifications = [];
$notif_query = "SELECT message FROM notification WHERE user_id = ?";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

while ($notif_row = $notif_result->fetch_assoc()) {
    $notifications[] = $notif_row;
}
$notif_stmt->close();

echo json_encode(['events' => $events, 'notifications' => $notifications]);
?>
