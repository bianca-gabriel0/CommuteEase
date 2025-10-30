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
      <img src="assets/CE-logo.png" a href="Home" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home">HOME</a>
      <a href="schedule-main" class="active">SCHEDULE</a>
      <a href="Home">ABOUT</a>
      <a href="accountinfo">ACCOUNT</a>
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

    <!-- 🟩 Added Day Filter -->
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

    <select id="timeFilter">
      <option value="">Time</option>
      <option value="5 AM">5 AM</option>
      <option value="6 AM">6 AM</option>
      <option value="7 AM">7 AM</option>
      <option value="8 AM">8 AM</option>
      <option value="9 AM">9 AM</option>
      <option value="10 AM">10 AM</option>
      <option value="11 AM">11 AM</option>
      <option value="12 PM">12 PM</option>
    </select>

    <select id="typeFilter">
      <option value="">Types</option>
      <option value="Bus">Bus</option>
      <option value="Mini Bus">Mini Bus</option>
    </select>

    <button id="searchBtn">Search</button>
  </div>

  <!-- Schedule table inside bordered card -->
  <div class="schedule-card">
    <div class="table-wrapper">
      <table id="scheduleTable">
        <thead>
          <tr>
            <th>Day</th> <!-- 🟩 Added new column -->
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
  // --- This back-to-top code is fine ---
  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // 🟩 --- START OF PAGINATION CODE --- 🟩

  const tableBody = document.querySelector("#scheduleTable tbody");
  const prevPageBtn = document.getElementById("prevPageBtn"); // 🟩 New
  const nextPageBtn = document.getElementById("nextPageBtn"); // 🟩 New

  let allSchedules = [];    // This holds ALL schedules from the DB
  let currentView = [];     // 🟩 This holds what we're *currently* looking at (all or filtered)
  let currentPage = 1;      // 🟩 The page we're on
  const rowsPerPage = 10;   // 🟩 Max rows per page

  // 4. renderTable function (This is your old function, it's perfect)
  //    It just renders whatever 10-item "slice" we give it.
  function renderTable(data) {
    tableBody.innerHTML = ""; // Clear the table first

    data.forEach(schedule => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${schedule.day}</td>
        <td>${schedule.location}</td>
        <td>${schedule.type}</td>
        <td>${schedule.route}</td>
        <td>${schedule.departure}</td>
        <td>${schedule.arrival}</td>
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
        alert(`Saved schedule: ${schedule.route}`);
      });
      saveCell.appendChild(saveBtn);
      row.appendChild(saveCell);
      // --- end save button code ---
      
      tableBody.appendChild(row);
    });
  }

  // 🟩 5. NEW "Master" Display Function
  //    This is the new boss. It does all the work.
  function updateDisplay() {
    // Calculate the "slice" of data we need for the current page
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const pageItems = currentView.slice(startIndex, endIndex);

    // Render just that slice
    renderTable(pageItems);

    // Update the next/prev button states
    // 'true' means disabled
    prevPageBtn.disabled = (currentPage === 1);
    nextPageBtn.disabled = (endIndex >= currentView.length);
  }

  // 6. Fetching data (Modified)
  document.addEventListener("DOMContentLoaded", () => {
    fetch('php/fetch_schedules.php')
      .then(response => response.json())
      .then(data => {
        allSchedules = data;    // Fill our "master list"
        currentView = data;     // Set the "current view" to be the master list
        updateDisplay();        // 🟩 Call our new function (instead of renderTable)
      })
      .catch(error => {
        console.error('Error fetching schedules:', error);
        tableBody.innerHTML = "<tr><td colspan='8'>Error loading schedules.</td></tr>";
      });
  });

  // 7. Your search filter (Modified)
  document.getElementById("searchBtn").addEventListener("click", () => {
    const searchValue = document.getElementById("searchInput").value.toLowerCase();
    const typeValue = document.getElementById("typeFilter").value;
    const dayValue = document.getElementById("dayFilter").value;

    const filtered = allSchedules.filter(s =>
      (s.route.toLowerCase().includes(searchValue) || s.location.toLowerCase().includes(searchValue)) &&
      (typeValue === "" || s.type === typeValue) &&
      (dayValue === "" || s.day === dayValue)
    );

    currentView = filtered;   // 🟩 Set the "current view" to the filtered list
    currentPage = 1;        // 🟩 Reset to page 1
    updateDisplay();          // 🟩 Call our new function (instead of renderTable)
  });

  // 8. Your 'auto-update on day filter' code (this was correct)
  document.getElementById("dayFilter").addEventListener("change", () => {
    document.getElementById("searchBtn").click();
  });

  // 9. 🟩 NEW Click Listeners for Pagination
  prevPageBtn.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      updateDisplay(); // Re-render the new page
    }
  });

  nextPageBtn.addEventListener("click", () => {
    // Calculate if there's a next page
    const maxPage = Math.ceil(currentView.length / rowsPerPage);
    if (currentPage < maxPage) {
      currentPage++;
      updateDisplay(); // Re-render the new page
    }
  });

  // 🟩 --- END OF PAGINATION CODE --- 🟩


// --- Notification Bell Code ---
  const bell = document.querySelector('.notification-icon');
  const dropdown = document.getElementById('notificationDropdown'); // 🟩 This was the missing line!
  
  bell.addEventListener('click', (event) => {
    // Stop the click from closing the dropdown immediately
    event.stopPropagation(); 
    // This adds/removes the 'show' class to your dropdown
    dropdown.classList.toggle('show');
    bell.classList.add('read'); // This is for your red dot
  });

  // This closes the dropdown if you click anywhere else on the page
  document.addEventListener('click', (event) => {
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });

</script> 
</body>
</html>
