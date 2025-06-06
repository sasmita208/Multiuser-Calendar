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

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT e.*
    FROM event e
    LEFT JOIN eventparticipant ep ON e.id = ep.event_id
    WHERE e.creator_id = ? OR ep.user_id = ?
    GROUP BY e.id
    ORDER BY e.start_time DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Events - Kronos</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #6a1b9a, #ffffff);
      margin: 0;
      padding: 40px;
      color: #333;
    }

    @keyframes fadeSlideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px 40px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      animation: fadeSlideIn 1s ease;
    }

    h1 {
      color: #6a1b9a;
      text-align: center;
      margin-bottom: 30px;
      animation: fadeSlideIn 1.2s ease;
    }

    .event {
      border-left: 5px solid #6a1b9a;
      background-color: #f8f6ff;
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      animation: fadeSlideIn 0.8s ease;
    }

    .event h3 {
      color: #4a0072;
      margin-bottom: 6px;
    }

    .event p {
      margin: 4px 0;
      font-size: 0.95rem;
    }

    .back-link {
      display: inline-block;
      margin-top: 20px;
      color: #6a1b9a;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: #4a0072;
    }
  </style>
</head>
<body>
  <div class="container">
<div class="header" style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 30px;">
  <img src="images/logo.png" alt="Kronos Logo" style="width: 90px;" />
  <h1 style="margin: 0; font-size: 2rem; color: #6a1b9a;">My Events</h1>
</div>
    <?php if ($result->num_rows > 0): ?>
      <?php while ($event = $result->fetch_assoc()): ?>
        <div class="event">
          <h3><?= htmlspecialchars($event['title']) ?></h3>
          <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
          <p><strong>Start:</strong> <?= date("d M Y, h:i A", strtotime($event['start_time'])) ?></p>
          <p><strong>End:</strong> <?= date("d M Y, h:i A", strtotime($event['end_time'])) ?></p>
          <?php if ($event['is_recurring']): ?>
            <p><strong>Recurring:</strong> <?= htmlspecialchars($event['recurrence_rule']) ?></p>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No events found.</p>
    <?php endif; ?>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
