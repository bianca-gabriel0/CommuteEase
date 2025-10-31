<?php
// 
// --- AUTH GUARD ---
// We MUST start the session at the very top to check for the login "wristband"
session_start();

// FLEXIBLE ACCESS: We check the login status but DO NOT redirect if false.
$is_logged_in = isset($_SESSION['user_id']); 

// If the user is logged in, we grab their name for the greeting.
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');
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

  <!-- --- NEW: Notification Toast HTML --- -->
  <!-- This is the empty element that our JavaScript will grab -->
  <div id="notification-toast"></div>
  <!-- --- END NEW HTML --- -->


  <!-- nav bar -->
  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href="Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php">HOME</a>
      <a href="schedule-main.php" class="active">SCHEDULE</a>
      <a href="Home.php#about">ABOUT</a>
      
      <!-- DYNAMIC LINKS: Shows LOGIN/SIGN UP for guests, ACCOUNT for members -->
      <?php if ($is_logged_in): ?>
        <a href="accountinfo.php">ACCOUNT</a>
      <?php else: ?>
        <a href="login.php">LOGIN</a>
        <a href="signup.php">SIGN UP</a>
      <?php endif; ?>
      
      <div class="welcome-message">
        <?php if ($is_logged_in): ?>
          Hi, <?php echo $firstName; ?>!
        <?php endif; ?>
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
          <a href="Home.php#about">About</a>
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
  // --- Pass PHP login status to JavaScript ---
  const isUserLoggedIn = <?php echo json_encode($is_logged_in); ?>;
  
  // --- Back-to-top code ---
  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // --- START OF PAGINATION CODE ---
  const tableBody = document.querySelector("#scheduleTable tbody");
  const prevPageBtn = document.getElementById("prevPageBtn");
  const nextPageBtn = document.getElementById("nextPageBtn");
  const searchInput = document.getElementById("searchInput"); // Get search input element

  let allSchedules = []; 
  let currentView = []; 
  let currentPage = 1; 
  const rowsPerPage = 10; 

 // Renders the table rows for the given data (a "slice")
  function renderTable(list) {
    tableBody.innerHTML = "";
    list.forEach(schedule => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${schedule.day}</td>
        <td>${schedule.location}</td>
        <td>${schedule.type}</td>
        <td>${schedule.route_formatted}</td>
        <td>${schedule.departure_formatted}</td>
        <td>${schedule.arrival_formatted}</td>
        <td>${schedule.frequency}</td>
      `;

      // --- UPDATED Save Button Logic ---
      const saveCell = document.createElement("td");
      
      if (isUserLoggedIn) {
        // --- User is LOGGED IN: Create a real submission form ---
        const saveForm = document.createElement("form");
        saveForm.action = "save_schedule.php"; // Point to your backend script
        saveForm.method = "POST";
        saveForm.classList.add("save-form"); // Optional: for styling

        // Create the hidden input to send the schedule_id
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "schedule_id";
        
        // This code assumes 'schedule_id' exists in the JSON from fetch_schedules.php
        hiddenInput.value = schedule.schedule_id; 
        
        // Create the submit button
        const saveBtn = document.createElement("button");
        saveBtn.type = "submit"; // This makes it submit the form
        saveBtn.classList.add("save-btn"); // Use your existing class
        saveBtn.title = "Save this schedule"; 

        // Create and add the icon (using your original code)
        const saveIcon = document.createElement("img");
        saveIcon.src = "assets/bookmark-icon.png";
        saveIcon.alt = "Save";
        saveIcon.classList.add("save-icon");
        saveBtn.appendChild(saveIcon);

        // Assemble the form
        saveForm.appendChild(hiddenInput);
        saveForm.appendChild(saveBtn);
        
        // Add the form to the cell
        saveCell.appendChild(saveForm);

      } else {
        // --- User is a GUEST: Keep the original "alert" button ---
        const saveBtn = document.createElement("button");
        saveBtn.classList.add("save-btn");
        saveBtn.title = "Log in to save schedules";

        const saveIcon = document.createElement("img");
        saveIcon.src = "assets/bookmark-icon.png";
        saveIcon.alt = "Save";
        saveIcon.classList.add("save-icon");
        saveBtn.appendChild(saveIcon);
        
        saveBtn.addEventListener("click", () => {
          alert("You must be logged in to save a schedule!"); 
        });
        
        saveCell.appendChild(saveBtn);
      }
      
      row.appendChild(saveCell);
      // --- end save button code ---
      
      tableBody.appendChild(row);
    });
  }

  // Master" Display Function: Calculates the slice and updates buttons
  function updateDisplay() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const pageItems = currentView.slice(startIndex, endIndex);

    renderTable(pageItems);

    prevPageBtn.disabled = (currentPage === 1);
    nextPageBtn.disabled = (endIndex >= currentView.length);
  }

  // --- NEW: Function to show the notification toast ---
  function showNotification(message, type = 'success') {
    const toast = document.getElementById("notification-toast");
    toast.innerHTML = message;
    
    // Set class based on type
    toast.className = "show " + type; // e.g., "show success" or "show info"

    // After 3 seconds, remove the show class
    setTimeout(() => { 
      toast.className = toast.className.replace("show", ""); 
      
      // Clean up the URL so the message doesn't pop up again on refresh
      // We use history.replaceState to do this without reloading the page
      if (window.history.replaceState) {
        const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanURL}, '', cleanURL);
      }
    }, 3000); // 3000ms = 3 seconds
  }


  // Fetches data when the page loads
  document.addEventListener("DOMContentLoaded", () => {
    
    // --- NEW: Check for URL parameters to show notification ---
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'saved') {
      showNotification("Schedule saved successfully!", "success");
    } else if (status === 'exists') {
      showNotification("This schedule is already in your list.", "info");
    }
    // --- END NEW NOTIFICATION CHECK ---
    
    
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
    const timeValue = document.getElementById("timeFilter").value;

    const filtered = allSchedules.filter(s => {
      
      let scheduleHour = -1; 
      if (s.departure_time) {
        scheduleHour = parseInt(s.departure_time.split(':')[0], 10);
      }
      
      const timeFilterHour = timeValue === "" ? null : parseInt(timeValue, 10);

      const matchesSearch = s.route_formatted.toLowerCase().includes(searchValue) || s.location.toLowerCase().includes(searchValue);
      const matchesType = (typeValue === "" || s.type === typeValue);
      const matchesDay = (dayValue === "" || s.day === dayValue);
      const matchesTime = (timeFilterHour === null || scheduleHour === timeFilterHour);

      return matchesSearch && matchesType && matchesDay && matchesTime;
    });

    currentView = filtered;   
    currentPage = 1;        
    updateDisplay();        
  });

  // UPDATED: Combined all filter change listeners into one
  const filters = [
    document.getElementById("dayFilter"),
    document.getElementById("typeFilter"),
    document.getElementById("timeFilter")
  ];
  
  // Attach event listener to all dropdown filters (change)
  filters.forEach(filter => {
    filter.addEventListener("change", () => {
      document.getElementById("searchBtn").click();
    });
  });
  
  // Attach keyup listener for instant search filtering on input
  searchInput.addEventListener("keyup", (event) => {
    // UPDATED: Check for Enter key and prevent default browser action
    if (event.key === 'Enter') {
        event.preventDefault(); // Stop the Enter key from causing a page refresh (robustness)
        document.getElementById("searchBtn").click();
    } else {
        // Instant search on every other key release (the existing behavior)
        document.getElementById("searchBtn").click();
    }
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
  
  bell.addEventListener("click", (event) => {
    event.stopPropagation(); 
    dropdown.classList.toggle("show");
    bell.classList.add("read"); 
  });

  document.addEventListener("click", (event) => {
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove("show");
    }
  });

</script> 
</body>
</html>

