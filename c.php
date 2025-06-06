<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "YourNewPassword123!", "dbs");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch events created by or shared with the user
$query = "
    SELECT DISTINCT e.id, e.title, e.description, e.start_time, e.end_time
    FROM event e
    LEFT JOIN eventparticipant ep ON e.id = ep.event_id
    WHERE e.creator_id = ? OR ep.user_id = ?
    ORDER BY e.start_time ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        "id" => $row["id"],
        "title" => $row["title"],
        "description" => $row["description"],
        "date" => date("Y-m-d", strtotime($row["start_time"])),
        "startTime" => date("H:i", strtotime($row["start_time"])),
        "endTime" => date("H:i", strtotime($row["end_time"]))
    ];
}

$stmt->close();
$conn->close();

echo json_encode($events);
?>
