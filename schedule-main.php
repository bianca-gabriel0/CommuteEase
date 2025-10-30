<?php
// 
// --- AUTH GUARD ---
// We MUST start the session at the very top to check for the login "wristband"
session_start();

// Check if the user_id "wristband" is NOT set
if (!isset($_SESSION['user_id'])) {
    // If they are not logged in, kick them back to the login page
    header("Location: login.php");
    exit(); // Stop the rest of the page from loading
}

// If the script gets past this point, the user IS logged in.
// 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Schedule</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="schedule-style.css">
</head>

<body>
  <!-- nav bar -->
  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href="Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php">HOME</a>
      <a href="schedule-main.php" class="active">SCHEDULE</a>
      <a href="Home.php#about">ABOUT</a>
      <a href="accountinfo.php">ACCOUNT</a>
      <div class="welcome-message">
        Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
      </div>
      <div class="notification-icon">
  <i class="fa-solid fa-bell"></i>
</div>
<div class="notification-dropdown" id="notificationDropdown">
  <p>No new notifications</p>
</div> 

    </nav>
  </header>

  <!-- TABLE FOR SCHED -->
  <h3><span class="underlinesched">TRIP SCHEDULES</span></h3>

  <div class="table-controls right-align">
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search routes or destination...">
      <i class="fa fa-search"></i>
    </div>

    <!-- Day Filter -->
    <select id="dayFilter">
      <option value="">Day</option>
      <option value="Monday">Monday</option>
      <option value="Tuesday">Tuesday</option>
      <option value="Wednesday">Wednesday</option>
      <option value="Thursday">Thursday</option>
      <option value="Friday">Friday</option>
      <option value="Saturday">Saturday</option>
      <option value="Sunday">Sunday</option>
    </select>

    <!-- UPDATED: Time Filter with 24-hour options -->
    <select id="timeFilter">
      <option value="">Time</option>
      <option value="0">12 AM</option>
      <option value="1">1 AM</option>
      <option value="2">2 AM</option>
      <option value="3">3 AM</option>
      <option value="4">4 AM</option>
      <option value="5">5 AM</option>
      <option value="6">6 AM</option>
      <option value="7">7 AM</option>
      <option value="8">8 AM</option>
      <option value="9">9 AM</option>
      <option value="10">10 AM</option>
      <option value="11">11 AM</option>
      <option value="12">12 PM</option>
      <option value="13">1 PM</option>
      <option value="14">2 PM</option>
      <option value="15">3 PM</option>
      <option value="16">4 PM</option>
      <option value="17">5 PM</option>
      <option value="18">6 PM</option>
      <option value="19">7 PM</option>
      <option value="20">8 PM</option>
      <option value="21">9 PM</option>
      <option value="22">10 PM</option>
      <option value="23">11 PM</option>
    </select>

    <select id="typeFilter">
      <option value="">Types</option>
      <option value="Bus">Bus</option>
      <option value="Mini-bus">Mini-bus</option>
    </select>

    <button id="searchBtn">Search</button>
  </div>

  <!-- Schedule table inside bordered card -->
  <div class="schedule-card">
    <div class="table-wrapper">
      <table id="scheduleTable">
        <thead>
          <tr>
            <th>Day</th>
            <th>Location</th>
            <th>Type/s</th>
            <th>Route / Destination</th>
            <th>Departure Time</th>
            <th>Estimated Arrival</th>
            <th>Frequency</th>
            <th>Save Schedule</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- next + previous arrows -->
<div class="table-arrows">
  <button id="prevPageBtn" class="arrow-btn">&lt;</button>
  <button id="nextPageBtn" class="arrow-btn">&gt;</button>
</div>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-logo">
          <img src="assets/CE-logo-white.png" alt="CommuteEase Logo">
          <p class="footer-tagline">Making your daily commute easier.</p>
        </div>

        <div class="footer-links">
          <h3>Quick Links</h3>
          <a href="Home.php">Home</a>
          <a href="#about">About</a>
          <a href="schedule-main.php">Schedule</a>
          <a href="accountinfo.php">Account</a>
        </div>

        <div class="footer-contact">
          <h3>Contact Us</h3>
          <p>📧 Email:</p>
          <a href="mailto:support@commuteease.com">commuteease@gmail.com</a>
          <p>Phone:</p>
          <a>(+63) 9123 456 789</a>
          <button id="backToTop">Back to Top ↑</button>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2025 CommuteEase.</p>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
<script>
  // --- Back-to-top code ---
  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // --- START OF PAGINATION CODE ---
  const tableBody = document.querySelector("#scheduleTable tbody");
  const prevPageBtn = document.getElementById("prevPageBtn");
  const nextPageBtn = document.getElementById("nextPageBtn");

  let allSchedules = [];    // This holds ALL schedules from the DB
  let currentView = [];     // This holds what we're *currently* looking at (all or filtered)
  let currentPage = 1;      // The page we're on
  const rowsPerPage = 10;   // Max rows per page

  // Renders the table rows for the given data (a "slice")
  function renderTable(data) {
    tableBody.innerHTML = ""; // Clear the table first

    data.forEach(schedule => {
      const row = document.createElement("tr");
      // Uses the _formatted fields from fetch_schedules.php
      row.innerHTML = `
        <td>${schedule.day}</td>
        <td>${schedule.location}</td>
        <td>${schedule.type}</td>
        <td>${schedule.route_formatted}</td>
        <td>${schedule.departure_formatted}</td>
        <td>${schedule.arrival_formatted}</td>
        <td>${schedule.frequency}</td>
      `;

      // --- save button code ---
      const saveCell = document.createElement("td");
      const saveBtn = document.createElement("button");
      saveBtn.classList.add("save-btn");
      const saveIcon = document.createElement("img");
      saveIcon.src = "assets/bookmark-icon.png";
      saveIcon.alt = "Save";
      saveIcon.classList.add("save-icon");
      saveBtn.appendChild(saveIcon);
      saveBtn.addEventListener("click", () => {
        alert(`Saved schedule: ${schedule.route_formatted}`);
      });
      saveCell.appendChild(saveBtn);
      row.appendChild(saveCell);
      // --- end save button code ---
      
      tableBody.appendChild(row);
    });
  }

  // Master" Display Function: Calculates the slice and updates buttons
  function updateDisplay() {
    // Calculate the "slice" of data we need for the current page
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const pageItems = currentView.slice(startIndex, endIndex);

    // Render just that slice
    renderTable(pageItems);

    // Update the next/prev button states
    prevPageBtn.disabled = (currentPage === 1);
    nextPageBtn.disabled = (endIndex >= currentView.length);
  }

  // Fetches data when the page loads
  document.addEventListener("DOMContentLoaded", () => {
    fetch('php/fetch_schedules.php')
      .then(response => response.json())
      .then(data => {
        allSchedules = data;    
        currentView = data;     
        updateDisplay();        
      })
      .catch(error => {
        console.error('Error fetching schedules:', error);
        tableBody.innerHTML = "<tr><td colspan='8'>Error loading schedules.</td></tr>";
      });
  });

  // Search filter event listener
  document.getElementById("searchBtn").addEventListener("click", () => {
    const searchValue = document.getElementById("searchInput").value.toLowerCase();
    const typeValue = document.getElementById("typeFilter").value;
    const dayValue = document.getElementById("dayFilter").value;
    const timeValue = document.getElementById("timeFilter").value; // This is the selected hour (0-23)

    const filtered = allSchedules.filter(s => {
      
      // Time filter logic (The fix is to be absolutely sure the comparison is between numbers)
      let scheduleHour = -1; 
      if (s.departure_time) {
        // Extract the hour (e.g., gets "08" from "08:45:00", then turns it into the number 8)
        scheduleHour = parseInt(s.departure_time.split(':')[0], 10);
      }
      
      // Parse the filter value to a number, or use null/empty string if not set
      const timeFilterHour = timeValue === "" ? null : parseInt(timeValue, 10);

      // Check all filters
      const matchesSearch = s.route_formatted.toLowerCase().includes(searchValue) || s.location.toLowerCase().includes(searchValue);
      const matchesType = (typeValue === "" || s.type === typeValue);
      const matchesDay = (dayValue === "" || s.day === dayValue);
      // The key is here: Matches if filter is empty, OR schedule hour exactly equals the filtered hour.
      const matchesTime = (timeFilterHour === null || scheduleHour === timeFilterHour);

      return matchesSearch && matchesType && matchesDay && matchesTime;
    });

    currentView = filtered;   
    currentPage = 1;        
    updateDisplay();        
  });

  // Auto-update on filter changes
  document.getElementById("dayFilter").addEventListener("change", () => {
    document.getElementById("searchBtn").click();
  });
  
  document.getElementById("typeFilter").addEventListener("change", () => {
    document.getElementById("searchBtn").click();
  });

  document.getElementById("timeFilter").addEventListener("change", () => {
    document.getElementById("searchBtn").click();
  });

  // Click Listeners for Pagination
  prevPageBtn.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      updateDisplay(); 
    }
  });

  nextPageBtn.addEventListener("click", () => {
    const maxPage = Math.ceil(currentView.length / rowsPerPage);
    if (currentPage < maxPage) {
      currentPage++;
      updateDisplay(); 
    }
  });

// --- Notification Bell Code ---
  const bell = document.querySelector('.notification-icon');
  const dropdown = document.getElementById('notificationDropdown'); 
  
  bell.addEventListener('click', (event) => {
    event.stopPropagation(); 
    dropdown.classList.toggle('show');
    bell.classList.add('read'); 
  });

  document.addEventListener('click', (event) => {
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });

</script> 
</body>
</html>
