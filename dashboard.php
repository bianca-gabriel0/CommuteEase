<?php
// --- ADMIN SECURITY CHECK ---
session_start();
// If 'admin_user_id' is not set in the session, redirect to the login page
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin_login.php");
    exit;
}

// include the database connection
require_once __DIR__ . '/php/db.php';

// --- 1.A. GET TOTAL USERS (for Overview Chart) ---
$sql_total_users = "SELECT COUNT(user_id) AS count FROM users";
$result_total_users = $conn->query($sql_total_users);
$total_users = $result_total_users->fetch_assoc()['count'];

// --- 1.B. GET NEW USERS LAST 7 DAYS (for "NEW USER" card) ---
$sql_new_users = "SELECT COUNT(user_id) AS count 
                  FROM users 
                  WHERE created_at >= CURDATE() - INTERVAL 6 DAY";
$result_new_users = $conn->query($sql_new_users);
$new_users_last_7_days = $result_new_users->fetch_assoc()['count'];


// --- 2. GET TOTAL SCHEDULES (for "VEHICLE SUMMARY" card & Overview Chart) ---
$sql_total_schedules = "SELECT COUNT(schedule_id) AS count FROM schedule WHERE is_deleted = 0";
$result_total_schedules = $conn->query($sql_total_schedules);
$total_schedules = $result_total_schedules->fetch_assoc()['count'];


// --- 3. GET RECENT USERS LIST (for "Registered Users" table) ---
$sql_recent_users = "SELECT first_name, last_name, email 
                     FROM users 
                     ORDER BY created_at DESC 
                     LIMIT 5";
$result_recent_users = $conn->query($sql_recent_users);

$recent_users_list = [];
while ($row = $result_recent_users->fetch_assoc()) {
    $recent_users_list[] = [
        'username' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'email' => htmlspecialchars($row['email'])
    ];
}


// --- 4. GET DATA FOR 7-DAY MINI CHARTS ---

// A. Create a placeholder for the last 7 days (FOR NEW USER CHART)
$user_chart_labels_array = [];
$user_chart_data_map = [];
for ($i = 6; $i >= 0; $i--) {
    $date = new DateTime("-$i days");
    $day_key = $date->format('Y-m-d'); // e.g., '2025-11-01'
    $day_label = $date->format('D');   // e.g., 'Sat'
    
    $user_chart_labels_array[] = $day_label;
    $user_chart_data_map[$day_key] = 0;
}

// B. Get User counts for the last 7 days
$sql_user_chart = "SELECT DATE(created_at) AS creation_date, COUNT(user_id) AS count
                   FROM users
                   WHERE created_at >= CURDATE() - INTERVAL 6 DAY
                   GROUP BY DATE(created_at)";
$result_user_chart = $conn->query($sql_user_chart);
while ($row = $result_user_chart->fetch_assoc()) {
    if (isset($user_chart_data_map[$row['creation_date']])) {
        $user_chart_data_map[$row['creation_date']] = (int)$row['count'];
    }
}

// C. Get Schedule (Vehicle) counts PER DAY OF THE WEEK (FOR VEHICLE CHART)
$schedule_chart_labels_array = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$schedule_chart_data_map = [
    'Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0,
    'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0
];

$sql_schedule_chart = "SELECT day, COUNT(schedule_id) AS count 
                       FROM schedule 
                       WHERE is_deleted = 0 
                       GROUP BY day";
                       
$result_schedule_chart = $conn->query($sql_schedule_chart);
if ($result_schedule_chart) {
    while ($row = $result_schedule_chart->fetch_assoc()) {
        if (isset($schedule_chart_data_map[$row['day']])) {
            $schedule_chart_data_map[$row['day']] = (int)$row['count'];
        }
    }
}

// D. Finalize data arrays for JavaScript
$user_chart_values = array_values($user_chart_data_map);
$schedule_chart_values = [
    $schedule_chart_data_map['Sunday'],
    $schedule_chart_data_map['Monday'],
    $schedule_chart_data_map['Tuesday'],
    $schedule_chart_data_map['Wednesday'],
    $schedule_chart_data_map['Thursday'],
    $schedule_chart_data_map['Friday'],
    $schedule_chart_data_map['Saturday']
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Commute Ease Admin - Dashboard</title>

  <link rel="stylesheet" href="dashboard-style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

  <div class="sidebar">
    <img src="assets/CE-logo.png" alt="" class="signup-image" style="width:100%; margin-bottom:20px;">
    <!-- Set "Dashboard" to active -->
    <a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="scheduleadmin.php"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
    <a href="view_users_admin.php"><i class="fa-solid fa-user-gear"></i> View Users</a>
    <button class="logout-button" onclick="logout()"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</button>
  </div>

  <div class="main-content">
    <div class="dashboard-content">

      <!-- Top row: new user, client, overview -->
      <div class="top-row">
        <div class="card small">
          <!-- UPDATED: Card title and stat -->
          <p class="card-title">NEW USERS (Last 7 Days)</p>
          <div class="stat"><span class="dot green"></span><span id="newUsers"></span></div> 
          <div class="mini-graph"><canvas id="newUserChart"></canvas></div>
        </div>


        <div class="chart-card card">
          <p class="card-title">Overview</p>
          <canvas id="overviewChart"></canvas>
        </div>
      </div>

      <!-- table + vehicle summary card -->
      <div class="bottom-row">
        <div class="card user-table-card">
          <p class="card-title">Recently Registered Users</p> <!-- Title updated -->
          <table id="usersTable" class="users-table">
            <thead>
              <tr><th>Username</th><th>Email</th></tr>
            </thead>
            <!-- The table body is empty, JS will fill it -->
            <tbody></tbody>
          </table>
        </div>

        <div class="card small">
          <p class="card-title">VEHICLE SUMMARY</p>
          <div class="stat"><span class="dot blue"></span>Total Schedules: <span id="totalVehicles"></span></div> <!-- Title updated -->
          <div class="mini-graph"><canvas id="vehicleChart"></canvas></div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // --- START: DYNAMIC DATA FROM PHP ---
    
    // 1. Set the numbers for the stat cards
    // UPDATED: Use the new 7-day user count
    document.getElementById("newUsers").textContent = <?php echo $new_users_last_7_days; ?>;
    document.getElementById("totalVehicles").textContent = <?php echo $total_schedules; ?>;

    // 2. Populate user table
    const users = <?php echo json_encode($recent_users_list); ?>;

    // 3. Get Chart Data
    const userChartLabels = <?php echo json_encode($user_chart_labels_array); ?>;
    const userChartData = <?php echo json_encode($user_chart_values); ?>;
    
    const vehicleChartLabels = <?php echo json_encode($schedule_chart_labels_array); ?>;
    const vehicleChartData = <?php echo json_encode($schedule_chart_values); ?>;
    
    // --- END: DYNAMIC DATA FROM PHP ---


    const tbody = document.querySelector("#usersTable tbody");
    if (users.length > 0) {
        users.forEach(u => {
            const tr = document.createElement("tr");
            tr.innerHTML = `<td>${u.username}</td><td>${u.email}</td>`;
            tbody.appendChild(tr);
        });
    } else {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan="2">No recent users found.</td>`;
        tbody.appendChild(tr);
    }

    // New User Mini Graph
    new Chart(document.getElementById("newUserChart"), {
      type: "line",
      data: {
        labels: userChartLabels,
        datasets: [{ data: userChartData, borderColor: "#00c853", fill: false, tension: 0.3 }]
      },
      // UPDATED: Removed scales to make labels visible on hover
      options: { plugins:{legend:{display:false}} }
    });
    
    // This chart "clientChart" doesn't exist in your HTML, so it might throw an error.
    if (document.getElementById("clientChart")) {
        new Chart(document.getElementById("clientChart"), {
          type: "line",
          data: {
            labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
            datasets: [{ data: [5,4,6,7,5,8,9], borderColor: "#ff1744", fill: false, tension: 0.3 }]
          },
          options: { plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}} }
        });
    }

    // Overview Chart
    new Chart(document.getElementById("overviewChart"), {
      type: "bar",
      data: {
        labels: ["Total Users", "Total Schedules"], // Updated labels
        datasets: [{
          label: "Overview",
          data: [<?php echo $total_users; ?>, <?php echo $total_schedules; ?>],
          backgroundColor: ["#00c853", "#2196f3"]
        }]
      },
      options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
    });

    // Vehicle mini graph
    new Chart(document.getElementById("vehicleChart"), {
      type: "line",
      data: {
        labels: vehicleChartLabels,
        datasets: [{ data: vehicleChartData, borderColor: "#2196f3", fill: false, tension: 0.3 }]
      },
      // UPDATED: Removed scales to make labels visible on hover
      options: { plugins:{legend:{display:false}} }
    });
    
    // Logout function pointing to the correct admin logout file
    function logout() {
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "admin_logout.php";
      }
    }
  </script>
</body>
</html>

