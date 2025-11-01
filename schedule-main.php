<?php

session_start();

$is_logged_in = isset($_SESSION['user_id']); 
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');

include 'php/db.php'; 

$unread_count = 0;
$notifications = [];

if ($is_logged_in) {
    $current_user_id = $_SESSION['user_id'];

    $unread_sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
    $unread_stmt = $conn->prepare($unread_sql);
    $unread_stmt->bind_param("i", $current_user_id);
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_row()[0];
    $unread_stmt->close();

    $notif_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $current_user_id);
    $notif_stmt->execute();
    $notif_result = $notif_stmt->get_result();

    if ($notif_result->num_rows > 0) {
        while ($row = $notif_result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    $notif_stmt->close();
}

$conn->close();
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

  <div id="notification-toast"></div>

  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href="Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php">HOME</a>
      <a href="schedule-main.php" class="active">SCHEDULE</a>
      <a href="Home.php#about">ABOUT</a>
      
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
      
      <?php if ($is_logged_in): ?>
          <div class="notification-icon <?php if ($unread_count == 0) echo 'read'; ?>">
            <i class="fa-solid fa-bell"></i>
          </div>
          
          <div class="notification-dropdown" id="notificationDropdown">
              <?php if (empty($notifications)): ?>
                  <p class="no-notifications">No new notifications</p>
              <?php else: ?>
                  <?php foreach ($notifications as $notif): ?>
                      <div class="notification-item <?php if ($notif['is_read'] == 0) echo 'unread'; ?>">
                          <p><?php echo htmlspecialchars($notif['message']); ?></p>
                          <small><?php echo date("M j, g:i a", strtotime($notif['created_at'])); ?></small>
                      </div>
                  <?php endforeach; ?>
              <?php endif; ?>
          </div> 
      <?php endif; ?>

    </nav>
  </header>

  <h3><span class="underlinesched">TRIP SCHEDULES</span></h3>

  <div class="table-controls right-align">
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search routes or destination...">
      <i class="fa fa-search"></i>
    </div>

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

<div class="table-arrows">
  <button id="prevPageBtn" class="arrow-btn">&lt;</button>
  <span id="pageIndicator" class="page-indicator">1 of 1</span>
  <button id="nextPageBtn" class="arrow-btn">&gt;</button>
</div>

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

<script>
  const isUserLoggedIn = <?php echo json_encode($is_logged_in); ?>;
  
  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  const tableBody = document.querySelector("#scheduleTable tbody");
  const prevPageBtn = document.getElementById("prevPageBtn");
  const nextPageBtn = document.getElementById("nextPageBtn");
  const searchInput = document.getElementById("searchInput"); 
  const pageIndicator = document.getElementById("pageIndicator");

  let allSchedules = []; 
  let currentView = []; 
  let currentPage = 1; 
  const rowsPerPage = 10; 

  function renderTable(list) {
    tableBody.innerHTML = "";
    
    if (list.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 20px;">No schedules match your filters.</td></tr>`;
        return;
    }
    
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

      const saveCell = document.createElement("td");
      
      if (isUserLoggedIn) {
        const saveForm = document.createElement("form");
        saveForm.action = "save_schedule.php"; 
        saveForm.method = "POST";
        saveForm.classList.add("save-form"); 

        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "schedule_id";
        hiddenInput.value = schedule.schedule_id; 
        
        const saveBtn = document.createElement("button");
        saveBtn.type = "submit"; 
        saveBtn.classList.add("save-btn"); 
        saveBtn.title = "Save this schedule"; 

        const saveIcon = document.createElement("img");
        saveIcon.src = "assets/bookmark-icon.png";
        saveIcon.alt = "Save";
        saveIcon.classList.add("save-icon");
        saveBtn.appendChild(saveIcon);

        saveForm.appendChild(hiddenInput);
        saveForm.appendChild(saveBtn);
        
        saveCell.appendChild(saveForm);

      } else {
        const saveBtn = document.createElement("button");
        saveBtn.classList.add("save-btn");
        saveBtn.title = "Log in to save schedules";

        const saveIcon = document.createElement("img");
        saveIcon.src = "assets/bookmark-icon.png";
        saveIcon.alt = "Save";
        saveIcon.classList.add("save-icon");
        saveBtn.appendChild(saveIcon);
        
        saveBtn.addEventListener("click", () => {
          showNotification("You must be logged in to save a schedule.", "info"); 
        });
        
        saveCell.appendChild(saveBtn);
      }
      
      row.appendChild(saveCell);
      tableBody.appendChild(row);
    });
  }

  function updateDisplay() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const pageItems = currentView.slice(startIndex, endIndex);

    renderTable(pageItems);

    prevPageBtn.disabled = (currentPage === 1);
    nextPageBtn.disabled = (endIndex >= currentView.length);
    
    const maxPage = Math.ceil(currentView.length / rowsPerPage) || 1; 
    pageIndicator.textContent = `${currentPage} of ${maxPage}`;
  }

  function showNotification(message, type = 'success') {
    const toast = document.getElementById("notification-toast");
    toast.innerHTML = message;
    
    toast.className = "show " + type; 

    setTimeout(() => { 
      toast.className = toast.className.replace("show", ""); 
      
      if (window.history.replaceState) {
        const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanURL}, '', cleanURL);
      }
    }, 3000); 
  }


  document.addEventListener("DOMContentLoaded", () => {
    
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'saved') {
      showNotification("Schedule saved successfully!", "success");
    } else if (status === 'exists') {
      showNotification("This schedule is already in your list.", "info");
    }
    
    
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

  const filters = [
    document.getElementById("dayFilter"),
    document.getElementById("typeFilter"),
    document.getElementById("timeFilter")
  ];
  
  filters.forEach(filter => {
    filter.addEventListener("change", () => {
      document.getElementById("searchBtn").click();
    });
  });
  
  searchInput.addEventListener("keyup", (event) => {
    if (event.key === 'Enter') {
        event.preventDefault(); 
        document.getElementById("searchBtn").click();
    } else {
        document.getElementById("searchBtn").click();
    }
  });

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

if (isUserLoggedIn) {
    const bell = document.querySelector('.notification-icon');
    const dropdown = document.getElementById('notificationDropdown'); 
    
    bell.addEventListener("click", (event) => {
        event.stopPropagation(); 
        dropdown.classList.toggle("show");
        
        if (!bell.classList.contains('read')) {
            
            bell.classList.add('read');
            
            fetch('php/mark_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    dropdown.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                } else {
                    console.error('Failed to mark notifications as read');
                    bell.classList.remove('read');
                }
            })
            .catch(error => {
                console.error('Error with fetch:', error);
                bell.classList.remove('read');
            });
        }
    });

    document.addEventListener("click", (event) => {
      if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove("show");
      }
    });
}

</script> 
</body>
</html>
