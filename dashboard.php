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
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="scheduleadmin.php"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
    <a href="commuters.php"><i class="fa-solid fa-users"></i> Commuters</a>
    <a href="view_users_admin.php"><i class="fa-solid fa-user-gear"></i> View Users</a>
    <button class="logout-button" onclick="logout()"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</button>
  </div>

  <div class="main-content">
    <div class="dashboard-content">

      <!-- Top row: new user, client, overview -->
      <div class="top-row">
        <div class="card small">
          <p class="card-title">NEW USER :</p>
          <div class="stat"><span class="dot green"></span><span id="newUsers">0</span></div>
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
          <p class="card-title">Registered Users</p>
          <table id="usersTable" class="users-table">
            <thead>
              <tr><th>Username</th><th>Email</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="card small">
          <p class="card-title">VEHICLE SUMMARY</p>
          <div class="stat"><span class="dot blue"></span>Total Vehicle: <span id="totalVehicles">0</span></div>
          <div class="mini-graph"><canvas id="vehicleChart"></canvas></div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Populate user table
    const users = [
      { username: "John Doe", email: "john@example.com" },
      { username: "Jane Smith", email: "jane@example.com" }
    ];
    const tbody = document.querySelector("#usersTable tbody");
    users.forEach(u => {
      const tr = document.createElement("tr");
      tr.innerHTML = `<td>${u.username}</td><td>${u.email}</td>`;
      tbody.appendChild(tr);
    });

    // Mini Graphs
    new Chart(document.getElementById("newUserChart"), {
      type: "line",
      data: {
        labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
        datasets: [{ data: [2,3,5,4,6,7,8], borderColor: "#00c853", fill: false, tension: 0.3 }]
      },
      options: { plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}} }
    });

    new Chart(document.getElementById("clientChart"), {
      type: "line",
      data: {
        labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
        datasets: [{ data: [5,4,6,7,5,8,9], borderColor: "#ff1744", fill: false, tension: 0.3 }]
      },
      options: { plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}} }
    });

    // Overview Chart
    new Chart(document.getElementById("overviewChart"), {
      type: "bar",
      data: {
        labels: ["Users", "Clients", "Vehicles"],
        datasets: [{
          label: "Overview",
          data: [54, 64, 100],
          backgroundColor: ["#00c853", "#ff1744", "#2196f3"]
        }]
      },
      options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
    });

    // Vehicle mini graph
    new Chart(document.getElementById("vehicleChart"), {
      type: "line",
      data: {
        labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
        datasets: [{ data: [1,2,2,3,2,4,3], borderColor: "#2196f3", fill: false, tension: 0.3 }]
      },
      options: { plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}} }
    });
  </script>
</body>
</html>
