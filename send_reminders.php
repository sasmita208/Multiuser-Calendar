<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// ⏱️ Log helper function
function logMessage($message) {
    $logFile = __DIR__ . '/reminder_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// 📦 Database config
$host = "localhost";
$db = "dbs";
$user = "root";
$pass = "YourNewPassword123!";

// 🛠️ Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    logMessage("❌ DB Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// ⏰ Set timezone (IST)
$conn->query("SET time_zone = '+05:30'");

// 📅 Get events starting within reminder window and not already sent
$sql = "
    SELECT e.id, e.title, e.start_time, u.email 
    FROM event e
    JOIN users u ON e.creator_id = u.id
    WHERE e.reminder_minutes_before IS NOT NULL 
    AND e.reminder_sent = 0
    AND TIMESTAMPDIFF(MINUTE, NOW(), e.start_time) <= e.reminder_minutes_before
    AND TIMESTAMPDIFF(MINUTE, NOW(), e.start_time) > 0
";

$result = $conn->query($sql);
logMessage("🔍 Rows found: " . $result->num_rows);

if ($result->num_rows == 0) {
    logMessage("⚠️ No events found that need reminders.");
}

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);

    try {
        // 📧 SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sasmita.test20@gmail.com';
        $mail->Password   = 'ludg pjgk hufe xxgn'; // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // ✉️ Email setup
        $mail->setFrom('sasmita.test20@gmail.com', 'Kronos Reminder');
        $mail->addAddress($row['email']);
        $mail->isHTML(true);
        $mail->Subject = "⏰ Reminder: Upcoming Event - " . $row['title'];
        $mail->Body    = "
            <h2 style='color:#3c4858;'>⏰ Event Reminder</h2>
            <p><strong>Event:</strong> {$row['title']}</p>
            <p><strong>Starts at:</strong> {$row['start_time']}</p>
            <p style='color:green;'>Don't forget to attend your event!</p>
            <hr>
            <p style='font-size:12px;'>– Team Kronos</p>
        ";

        $mail->send();
        logMessage("✅ Reminder sent to " . $row['email']);

        // ✅ Mark as sent
        $stmt = $conn->prepare("UPDATE event SET reminder_sent = 1 WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        logMessage("❌ Email failed for " . $row['email'] . ": {$mail->ErrorInfo}");
    }
}

$conn->close();
?>
