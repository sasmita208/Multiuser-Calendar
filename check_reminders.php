<?php
include 'config.php';

// Current time
$now = new DateTime();

// Fetch events with reminders
$sql = "SELECT * FROM events WHERE reminder_minutes_before IS NOT NULL";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $event_time = new DateTime($row['start_time']);
    $reminder_time = clone $event_time;
    $reminder_time->modify("-" . $row['reminder_minutes_before'] . " minutes");

    // Check if current time is exactly the reminder time
    if ($now->format('Y-m-d H:i') == $reminder_time->format('Y-m-d H:i')) {
        // Trigger notification (e.g., store in notifications table)
        $stmt = $conn->prepare("INSERT INTO notifications (event_id, user_id, message) VALUES (?, ?, ?)");
        $msg = "Reminder: " . $row['title'] . " at " . $event_time->format('H:i');
        $stmt->bind_param("iis", $row['id'], $row['created_by'], $msg);
        $stmt->execute();
    }
}

$conn->close();
?>
