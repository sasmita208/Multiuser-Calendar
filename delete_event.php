<?php
include __DIR__ . '/config.php';
session_start();
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please login first.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing event ID.");
}

$event_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the event exists and belongs to the user
$check = $conn->prepare("SELECT creator_id FROM event WHERE id = ?");
$check->bind_param("i", $event_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();
if ($event['creator_id'] != $user_id) {
    die("Unauthorized: You do not have permission to delete this event.");
}
$check->close();

// Delete related notifications first (foreign key constraint)
$delNotif = $conn->prepare("DELETE FROM notification WHERE event_id = ?");
$delNotif->bind_param("i", $event_id);
$delNotif->execute();
$delNotif->close();

// Delete the event
$stmt = $conn->prepare("DELETE FROM event WHERE id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    header("Location: c.html"); // Redirect to your calendar page
    exit;
} else {
    echo "Error deleting event: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
