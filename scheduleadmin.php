<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Commute Ease Admin - Schedule</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&family=Birthstone&family=Ephesis&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="scheduleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <div class="sidebar">
    <img src="assets/CE-logo.png" alt="" class="signup-image">
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="scheduleadmin.php"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
    <a href="commuters.php"><i class="fa-solid fa-users"></i> Commuters</a>
    <button class="logout-button" onclick="logout()">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
    </button>
  </div>

  <div class="main-content">
    <div class="card2">
      <h2>Schedules</h2>

      <!-- ✅ Day Filter Buttons -->
      <div class="days-row">
        <button onclick="filterByDay('All')">All</button>
        <button onclick="filterByDay('Sunday')">Sunday</button>
        <button onclick="filterByDay('Monday')">Monday</button>
        <button onclick="filterByDay('Tuesday')">Tuesday</button>
        <button onclick="filterByDay('Wednesday')">Wednesday</button>
        <button onclick="filterByDay('Thursday')">Thursday</button>
        <button onclick="filterByDay('Friday')">Friday</button>
        <button onclick="filterByDay('Saturday')">Saturday</button>
        <button class="add-schedule" onclick="openModal()">Add Schedule</button>
      </div>

      <!-- ✅ Schedule Table -->
      <table id="scheduleTable" class="schedule-table">
        <thead>
          <tr>
            <th>Day</th>
            <th>Location</th>
            <th>Type/s</th>
            <th>Route / Destination</th>
            <th>Departure Time</th>
            <th>Estimated Arrival</th>
            <th>Frequency</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- ✅ Add Schedule Modal -->
  <div id="scheduleModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Add a New Schedule</h2>

      <label for="day">Day:</label>
      <select id="day">
        <option>Monday</option>
        <option>Tuesday</option>
        <option>Wednesday</option>
        <option>Thursday</option>
        <option>Friday</option>
        <option>Saturday</option>
        <option>Sunday</option>
      </select>

      <label for="type">Type:</label>
      <select id="type">
        <option>Bus</option>
        <option>Mini-bus</option>
      </select>

      <label for="location">Location (Terminal):</label>
      <input type="text" id="location">

      <label for="route">Route / Destination:</label>
      <div>Dagupan → <input type="text" id="route" placeholder="Enter destination"></div>

      <label for="time">Departure Time:</label>
      <input type="time" id="time">

      <label for="arrival">Estimated Arrival:</label>
      <input type="time" id="arrival">

      <label for="frequency">Frequency:</label>
      <select id="frequency">
        <option>Every 10 minutes</option>
        <option>Every 15 minutes</option>
        <option>Every 20 minutes</option>
        <option>Every 25 minutes</option>
        <option>Every 30 minutes</option>
        <option>Every 35 minutes</option>
        <option>Every 40 minutes</option>
      </select>

      <button onclick="addNewSchedule()">Add Schedule</button>
    </div>
  </div>

  <script>
    let schedules = [];

    // 🟢 Fetch from PHP (MySQL)
    async function loadSchedules() {
      const res = await fetch("php/fetch_schedules.php");
      schedules = await res.json();
      displaySchedules(schedules);
    }

    // 🟢 Display table rows
    function displaySchedules(list) {
      const tableBody = document.querySelector("#scheduleTable tbody");
      tableBody.innerHTML = "";
      list.forEach(s => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${s.day}</td>
          <td>${s.location}</td>
          <td>${s.type}</td>
          <td>${s.route}</td>
          <td>${s.departure}</td>
          <td>${s.arrival}</td>
          <td>${s.frequency}</td>
        `;
        tableBody.appendChild(row);
      });
    }

    // 🟢 Filter by day
    function filterByDay(day) {
      if (day === "All") displaySchedules(schedules);
      else displaySchedules(schedules.filter(s => s.day === day));
    }

    // 🟢 Modal controls
    function openModal() {
      document.getElementById("scheduleModal").style.display = "block";
    }

    function closeModal() {
      document.getElementById("scheduleModal").style.display = "none";
    }

    // 🟢 Add new schedule to DB
    async function addNewSchedule() {
      const data = {
        day: document.getElementById("day").value,
        type: document.getElementById("type").value,
        location: document.getElementById("location").value,
        destination: document.getElementById("route").value,
        departure: document.getElementById("time").value,
        arrival: document.getElementById("arrival").value,
        frequency: document.getElementById("frequency").value
      };

      if (!data.location || !data.destination || !data.departure || !data.arrival) {
        alert("Please fill in all fields");
        return;
      }

      const res = await fetch("php/add_schedules.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.json();
      if (result.status === "success") {
        closeModal();
        loadSchedules();
      } else {
        alert("Error: " + result.message);
      }
    }

  window.onload = function() {
    fetch('php/fetch_schedules.php')
      .then(response => response.json())
      .then(data => {
        schedules = data;
        displaySchedules(schedules);
      })
      .catch(error => console.error('Error fetching schedules:', error));
  };

  </script>
</body>
</html>
