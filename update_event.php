<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    // Optional: Verify the user has permission to update this event

    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, start_time = ?, end_time = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $id);

    if ($stmt->execute()) {
        echo "Event updated successfully!";
    } else {
        echo "Error updating event: " . $stmt->error;
    }

    $stmt->close();
}
?>
