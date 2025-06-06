<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$host = "localhost";
$db = "dbs";
$user = "root";
$pass = "YourNewPassword123!";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';  // Ensure you've installed PHPMailer using Composer, or include files manually

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);

    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'] ?? null;
    $start_ampm = $_POST['start_ampm'];
    $end_time = $_POST['end_time'] ?? null;
    $end_ampm = $_POST['end_ampm'];

    $start_time_24 = date("H:i:s", strtotime("$start_time $start_ampm"));
    $end_time_24 = date("H:i:s", strtotime("$end_time $end_ampm"));

    $start_datetime = $event_date . " " . $start_time_24;
    $end_datetime = $event_date . " " . $end_time_24;

    $creator_id = $_SESSION['user_id'];
    $reminder_minutes = $_POST['reminder_minutes_before'] ?? 15;  // Default to 15 minutes if not set

    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurrence_rule = $is_recurring ? htmlspecialchars($_POST['recurrence_rule']) : null;

    // Insert event into the database
    $stmt = $conn->prepare("INSERT INTO event (title, description, start_time, end_time, creator_id, is_recurring, recurrence_rule, reminder_minutes_before) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissi", $title, $description, $start_datetime, $end_datetime, $creator_id, $is_recurring, $recurrence_rule, $reminder_minutes);
    $stmt->execute();

    // Get the event ID after insert
    $event_id = $stmt->insert_id;
    $stmt->close();

    // Send reminder email right after creating the event
    sendReminderEmail($event_id, $creator_id, $title, $start_datetime);

    // ✅ Add a notification for the creator
    $notif_message = "New event created: $title";
    $notif_stmt = $conn->prepare("INSERT INTO notification (event_id, user_id, message, sent_at) VALUES (?, ?, ?, NOW())");
    $notif_stmt->bind_param("iis", $event_id, $creator_id, $notif_message);
    $notif_stmt->execute();
    $notif_stmt->close();

    // ✅ Handle invited users
    if (!empty($_POST['invited_users'])) {
        $invited_raw = $_POST['invited_users'];
        $invited = is_array($invited_raw) ? $invited_raw : array_map('trim', explode(',', $invited_raw));

        foreach ($invited as $invitee_input) {
            $invitee_id = null;

            if (is_numeric($invitee_input)) {
                $invitee_id = (int)$invitee_input;
            } else {
                $lookup_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $lookup_stmt->bind_param("s", $invitee_input);
                $lookup_stmt->execute();
                $lookup_stmt->bind_result($lookup_id);
                if ($lookup_stmt->fetch()) {
                    $invitee_id = $lookup_id;
                }
                $lookup_stmt->close();
            }

            if ($invitee_id !== null) {
                $insert_stmt = $conn->prepare("INSERT INTO eventparticipant (event_id, user_id) VALUES (?, ?)");
                $insert_stmt->bind_param("ii", $event_id, $invitee_id);
                $insert_stmt->execute();
                $insert_stmt->close();

                // Optional: Notify the invitee
                $invite_msg = "You've been invited to an event: $title";
                $invite_notif = $conn->prepare("INSERT INTO notification (event_id, user_id, message, sent_at) VALUES (?, ?, ?, NOW())");
                $invite_notif->bind_param("iis", $event_id, $invitee_id, $invite_msg);
                $invite_notif->execute();
                $invite_notif->close();
            }
        }
    }

    $conn->close();
}

function sendReminderEmail($event_id, $creator_id, $event_title, $event_start_time) {
    // Fetch the creator's email
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $creator_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    // Email setup
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sasmita.test20@gmail.com'; // your Gmail
        $mail->Password = 'ludg pjgk hufe xxgn';      // Gmail app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Send email to the creator
        $mail->setFrom('sasmita.test20@gmail.com', 'Kronos Reminder');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "⏰ Reminder: Upcoming Event - $event_title";
        $mail->Body = "
            <h2 style='color:#3c4858;'>⏰ Event Reminder</h2>
            <p><strong>Event:</strong> $event_title</p>
            <p><strong>Starts at:</strong> $event_start_time</p>
            <p style='color:green;'>Don't forget to attend your event!</p>
            <hr>
            <p style='font-size:12px;'>– Team Kronos</p>
        ";

        $mail->send();
        echo "✅ Reminder email sent to $email <br>";

        // Mark reminder as sent
        $update_sql = "UPDATE event SET reminder_sent = 1 WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $event_id);
        $update_stmt->execute();
        $update_stmt->close();
    } catch (Exception $e) {
        echo "❌ Failed to send email for $email: {$mail->ErrorInfo} <br>";
    }
}
?>
