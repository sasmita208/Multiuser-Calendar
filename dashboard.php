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
$name = "User";

$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fetched_name);
if ($stmt->fetch()) {
    $name = $fetched_name;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kronos | Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2d9d5c56d.js" crossorigin="anonymous"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    .fade-in {
      animation: fadeIn 1s ease forwards;
    }

    .slide-in {
      animation: slideInLeft 0.8s ease forwards;
    }

    .pop-in {
      animation: popIn 0.6s ease;
    }

    body {
      display: flex;
      background: linear-gradient(to bottom right, #822ea8, #ffffff);
      color: #333;
    }

    .sidebar {
      width: 220px;
      background-color: rgba(255, 255, 255, 0.85);
      height: 100vh;
      padding: 25px 15px;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
    }

    .sidebar h2 {
      font-size: 20px;
      margin-bottom: 25px;
      color: #6a1b9a;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      display: flex;
      align-items: center;
      margin: 18px 0;
      cursor: pointer;
      padding: 10px 12px;
      border-radius: 8px;
      transition: background-color 0.3s, transform 0.2s;
    }

    .sidebar ul li:hover {
      background-color: #f0e5ff;
      transform: translateX(5px);
    }

    .sidebar ul li i {
      margin-right: 10px;
      color: #822ea8;
    }

    .main {
      flex: 1;
      padding: 30px 50px;
    }

    .main-header h1 {
      font-size: 26px;
      color: #3c1361;
    }

    .greeting {
      font-size: 20px;
      margin: 15px 0;
      color: #4a0072;
    }

    .event-section {
      margin-top: 10px;
    }

    .add-btn {
      margin-top: 15px;
      padding: 12px 24px;
      background-color: #6a1b9a;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .preview {
      margin-top: 30px;
      background-color: #822ea8;
      color: white;
      padding: 20px;
      border-radius: 10px;
    }

    .preview h3 {
      margin-bottom: 10px;
      font-size: 20px;
    }

    .powered {
      text-align: right;
      font-size: 12px;
      color: #eee;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar slide-in">
   <div class="logo-container">
  <img src="images/logo.png" alt="Kronos Logo" style="width: 160px; margin-bottom: 20px;" />
</div>
    <ul>
      <li onclick="location.href='my_events.php'"><i class="fas fa-calendar-alt"></i> My Events</li>
      <li onclick="location.href='design.html'"><i class="fas fa-paint-brush"></i> Design</li>
      <li onclick="location.href='settings.html'"><i class="fas fa-cog"></i> Settings</li>
      <li onclick="location.href='c.html'"><i class="fas fa-cog"></i> Calendar</li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main fade-in">
    <div class="main-header">
      <h1>Dashboard</h1>
      <p class="greeting">👋 Hello, <strong><?php echo htmlspecialchars($name); ?></strong>!</p>
    </div>

    <div class="event-section">
      <button class="add-btn" onclick="location.href='create_event.html'">+ Add Your Event</button>

      <div class="preview">
        <h3>Upcoming Events</h3>
        <ul id="event-list" style="list-style: none; padding-left: 0;"></ul>
      </div>

      <div class="preview" style="margin-top: 20px; background-color: #6a1b9a;">
        <h3>Notifications</h3>
        <ul id="notif-list" style="list-style: none; padding-left: 0;"></ul>
      </div>

      <p class="powered">Powered by EventsCalendar.co</p>
    </div>
  </div>

  <script>
    fetch('get_data.php')
      .then(response => response.json())
      .then(data => {
        const eventList = document.getElementById('event-list');
        const notifList = document.getElementById('notif-list');

        eventList.innerHTML = '';
        notifList.innerHTML = '';

        if (!data.events || data.events.length === 0) {
          eventList.innerHTML = '<li>No upcoming events</li>';
        } else {
          data.events.forEach(event => {
            const li = document.createElement('li');
            const date = new Date(event.start_time);
            li.textContent = `${event.title} - ${date.toLocaleString()}`;
            li.style.padding = '6px 0';
            li.classList.add('pop-in');
            eventList.appendChild(li);
          });
        }

        if (!data.notifications || data.notifications.length === 0) {
          notifList.innerHTML = '<li>No notifications</li>';
        } else {
          data.notifications.forEach(note => {
            const li = document.createElement('li');
            li.textContent = note.message;
            li.style.padding = '6px 0';
            li.classList.add('pop-in');
            notifList.appendChild(li);
          });
        }
      })
      .catch(error => {
        console.error('Error fetching data:', error);
        document.getElementById('event-list').innerHTML = '<li>Error loading events</li>';
        document.getElementById('notif-list').innerHTML = '<li>Error loading notifications</li>';
      });
  </script>

</body>
</html>
