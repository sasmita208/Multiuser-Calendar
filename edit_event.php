<?php
include 'config.php';
session_start();

if (!isset($_GET['id'])) {
    die("Event ID is missing.");
}

$event_id = $_GET['id'];

// Optional: Check if the logged-in user has permission to edit this event

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
</head>
<body>
<h2>Edit Event</h2>
<form action="update_event.php" method="POST">
    <input type="hidden" name="id" value="<?= $event['id'] ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required><br>
    <textarea name="description"><?= htmlspecialchars($event['description']) ?></textarea><br>
    <input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime($event['start_time'])) ?>" required><br>
    <input type="datetime-local" name="end_time" value="<?= date('Y-m-d\TH:i', strtotime($event['end_time'])) ?>" required><br>
    <button type="submit">Update Event</button>
</form>
</body>
</html>
