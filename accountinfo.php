<?php
// Start the session
session_start();

// 1. STRICT AUTH GUARD
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); 
}

// 2. --- MODIFIED: DATABASE LOGIC ---
include 'php/db.php'; 

// --- PAGINATION SETUP ---
$items_per_page = 7; 
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $items_per_page;
// --- END: PAGINATION SETUP ---


$current_user_id = $_SESSION['user_id'];
$saved_schedules = []; 
$total_items = 0;
$total_pages = 0;

// --- TOTAL COUNT QUERY ---
$count_sql = "SELECT COUNT(ss.saved_id)
              FROM schedule s
              JOIN saved_schedules ss ON s.schedule_id = ss.schedule_id
              WHERE ss.user_id = ? AND s.is_deleted = FALSE";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $current_user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_row()[0]; 
$count_stmt->close();

if ($total_items > 0) {
    $total_pages = ceil($total_items / $items_per_page);

    // --- MAIN QUERY ---
    $sql = "SELECT 
                s.location, s.type, s.destination, s.departure_time, 
                s.estimated_arrival, s.frequency, ss.saved_id 
            FROM 
                schedule s
            JOIN 
                saved_schedules ss ON s.schedule_id = ss.schedule_id
            WHERE 
                ss.user_id = ? AND s.is_deleted = FALSE
            ORDER BY 
                ss.saved_id DESC 
            LIMIT ? OFFSET ?"; 

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $current_user_id, $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $saved_schedules[] = $row; 
        }
    }
    $stmt->close();
}
// --- END MODIFIED DATABASE LOGIC ---


// 3. Prepare dynamic data
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');
$lastName = htmlspecialchars($_SESSION['last_name'] ?? ''); 
$userEmail = htmlspecialchars($_SESSION['email'] ?? 'email.not.found@example.com'); 
$fullName = trim($firstName . ' ' . $lastName);

// We must close the connection *after* ALL database work is done
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Account</title>
  <link rel="stylesheet" href="accountinfo.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <!-- --- NEW: CSS for Notifications & Modal Button --- -->
  <style>
    /* Notification Toast CSS */
    #notification-toast {
      visibility: hidden; min-width: 250px; margin-left: -125px;
      background-color: #333; color: #fff; text-align: center; 
      border-radius: 8px; padding: 16px; position: fixed; 
      z-index: 1000; left: 50%; bottom: 30px; font-size: 17px; 
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    #notification-toast.success { background-color: #059669; }
    #notification-toast.info { background-color: #0284c7; } /* Blue for info */
    #notification-toast.show {
      visibility: visible; 
      -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    @-webkit-keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
    @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
    @-webkit-keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

    /* Red delete button for modal */
    /* (You can add this to accountinfo.css if you want) */
    .custom-modal-buttons .confirm-delete {
      background-color: #e11d48; /* A sharp red */
    }
    .custom-modal-buttons .confirm-delete:hover {
      background-color: #be123c; /* A darker red */
    }
  </style>
  
</head>
<body>

  <!-- --- NEW: Notification Toast HTML --- -->
  <div id="notification-toast"></div>

  <!-- nav bar (unchanged) -->
  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href="Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php">HOME</a>
      <a href="schedule-main.php">SCHEDULE</a>
      <a href="Home.php#about">ABOUT</a>
      <a href="accountinfo.php" class="active">ACCOUNT</a>
      <div class="welcome-message">Hi, <?php echo $firstName; ?>!</div>
      <div class="notification-icon"><i class="fa-solid fa-bell"></i></div>
      <div class="notification-dropdown" id="notificationDropdown"><p>No new notifications</p></div>
    </nav>
  </header>

  <!-- Account Section (unchanged) -->
  <section class="account-section">
    <h2 class="account-heading">Account Information</h2>
    <div class="account-container">
      <!-- Profile Card (unchanged) -->
      <div class="profile-card">
        <div class="profile-header"><div class="profile-pic"><img src="assets/profile.png" alt="User Profile"></div></div>
        <div class="profile-details">
          <h3></h3>
          <h3><?php echo $fullName; ?></h3> 
          <a href="#" class="edit-link" onclick="goToEdit()"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
          <hr>
          <p><b>Email:</b> <?php echo $userEmail; ?></p>
        </div>
        <button class="logout-btn" onclick="openLogoutModal()">↳ Log out</button>
      </div>

      <!-- Saved Schedules -->
      <div class="schedule-card">
        <h3>My Saved Schedules:</h3>
        <table>
          <thead>
            <tr>
              <th>Location</th>
              <th>Type/s</th>
              <th>Route / Destination</th>
              <th>Departure Time</th>
              <th>Estimated Arrival</th>
              <th>Frequency</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="scheduleTable">
            
            <!-- --- This PHP Loop is the same --- -->
            <?php if (empty($saved_schedules)): ?>
              <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">You have no saved schedules yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($saved_schedules as $schedule): ?>
                <tr>
                  <td><?php echo htmlspecialchars($schedule['location']); ?></td>
                  <td><?php echo htmlspecialchars($schedule['type']); ?></td>
                  <td>Dagupan → <?php echo htmlspecialchars($schedule['destination']); ?></td>
                  <td><?php echo date("g:iA", strtotime($schedule['departure_time'])); ?></td>
                  <td><?php echo date("g:iA", strtotime($schedule['estimated_arrival'])); ?></td>
                  <td><?php echo htmlspecialchars($schedule['frequency']); ?></td>
                  <td>
                    <!-- 
                      --- CHANGE ---
                      1. Added class "delete-schedule-form"
                    -->
                    <form action="unsave_schedule.php" method="POST" class="delete-schedule-form" style="display: inline;">
                      <input type="hidden" name="saved_id" value="<?php echo $schedule['saved_id']; ?>">
                      <!-- 
                        --- CHANGE ---
                        2. Changed type="submit" to type="button"
                           This stops the instant delete.
                      -->
                      <button type="button" class="delete-btn" title="Unsave this schedule">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            <!-- --- END: PHP Loop --- -->
            
          </tbody>
        </table>
            
            <!-- Pagination links (unchanged) -->
            <?php if ($total_pages > 1): ?>
              <div class="pagination-controls">
                  <?php if ($current_page > 1): ?>
                      <a href="?page=<?php echo $current_page - 1; ?>" class="page-link">&lt;</a>
                  <?php else: ?>
                      <span class="page-link-disabled">&lt;</span>
                  <?php endif; ?>
                  
                  <?php if ($current_page < $total_pages): ?>
                      <a href="?page=<?php echo $current_page + 1; ?>" class="page-link">&gt;</a>
                  <?php else: ?>
                      <span class="page-link-disabled">&gt;</span>
                  <?php endif; ?>
              </div>
            <?php endif; ?>
            <!-- --- END: PAGINATION LINKS --- -->
            
      </div>
    </div>
    
  </section>

  <!-- Footer (unchanged) -->
  <footer>
    <!-- (Your footer code is unchanged) -->
    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-logo"><img src="assets/CE-logo-white.png" alt="CommuteEase Logo"><p class="footer-tagline">Making your daily commute easier.</p></div>
        <div class="footer-links">
          <h3>Quick Links</h3>
          <a href="Home.php">Home</a><a href="#about">About</a>
          <a href="schedule-main.php">Schedule</a><a href="accountinfo.php">Account</a>
        </div>
        <div class="footer-contact">
          <h3>Contact Us</h3>
          <p>📧 Email:</p><a href="mailto:commuteease@gmail.com">commuteease@gmail.com</a>
          <p>Phone:</p><a>(+63) 9123 456 789</a>
          <button id="backToTop">Back to Top ↑</button>
        </div>
      </div>
      <div class="footer-bottom"><p>&copy; 2025 CommuteEase. All rights reserved.</p></div>
    </div>
  </footer>

  <!-- Logout Modal (unchanged) -->
  <div id="logoutModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
      <h4>Confirm Log Out</h4><p>Are you sure you want to log out?</p>
      <div class="custom-modal-buttons">
        <button id="cancelLogoutBtn" class="modal-btn cancel">Cancel</button>
        <button id="confirmLogoutBtn" class="modal-btn confirm">Log Out</button>
      </div>
    </div>
  </div>

  <!-- --- NEW: Delete Schedule Confirmation Modal --- -->
  <div id="deleteScheduleModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
      <h4>Confirm Delete</h4>
      <p>Are you sure you want to unsave this schedule?</p>
      <div class="custom-modal-buttons">
        <button id="cancelDeleteBtn" class="modal-btn cancel">Cancel</button>
        <button id="confirmDeleteBtn" class="modal-btn confirm-delete">Delete</button>
      </div>
    </div>
  </div>


  <!-- JS Functions -->
  <script>
    // Back-to-top (unchanged)
    const backToTop = document.getElementById("backToTop");
    if(backToTop) {
        backToTop.addEventListener("click", () => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // Logout Modal Functions (unchanged)
    function openLogoutModal() { document.getElementById('logoutModal').classList.add('show'); }
    function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('show'); }
    function confirmLogout() { window.location.href = 'logout.php'; }
    function goToEdit() { window.location.href = "editprofile.php"; }
    
    // Notification Bell (unchanged)
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

    // Logout Modal Listeners (unchanged)
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    cancelLogoutBtn.addEventListener('click', closeLogoutModal);
    confirmLogoutBtn.addEventListener('click', confirmLogout);
    logoutModal.addEventListener('click', (event) => {
      if (event.target == logoutModal) { closeLogoutModal(); }
    });
    
    
    // --- NEW: Delete Schedule Modal Logic ---
    let formToSubmit = null; // This will hold the form we want to submit
    const deleteModal = document.getElementById('deleteScheduleModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // 1. Find ALL delete buttons and add a click listener
    document.querySelectorAll('.delete-btn[type="button"]').forEach(button => {
      button.addEventListener('click', (event) => {
        // Find the form that this button lives inside
        formToSubmit = event.target.closest('form.delete-schedule-form');
        // Open the modal
        deleteModal.classList.add('show');
      });
    });

    // 2. Function to close the delete modal
    function closeDeleteModal() {
      deleteModal.classList.remove('show');
      formToSubmit = null; // Clear the stored form
    }

    // 3. Add listeners to the modal buttons
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    
    confirmDeleteBtn.addEventListener('click', () => {
      // If we have a form stored, submit it
      if (formToSubmit) {
        formToSubmit.submit();
      }
    });

    // 4. Close modal if user clicks on the gray backdrop
    deleteModal.addEventListener('click', (event) => {
      if (event.target == deleteModal) {
        closeDeleteModal();
      }
    });

    
    // --- NEW: Notification Toast Logic ---
    
    // 1. The function to show the toast
    function showNotification(message, type = 'success') {
      const toast = document.getElementById("notification-toast");
      toast.innerHTML = message;
      toast.className = "show " + type; 
      setTimeout(() => { 
        toast.className = toast.className.replace("show", ""); 
        // Also, clean up the URL (remove the ?status=unsaved)
        if (window.history.replaceState) {
          const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
          // We need to preserve the ?page= param if it exists
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('page')) {
            window.history.replaceState({path: cleanURL}, '', cleanURL + '?page=' + urlParams.get('page'));
          } else {
            window.history.replaceState({path: cleanURL}, '', cleanURL);
          }
        }
      }, 3000); 
    }

    // 2. Check for the status on page load
    document.addEventListener("DOMContentLoaded", () => {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');

      if (status === 'unsaved') {
        // Use the "info" style (blue) for unsaving
        showNotification("Schedule unsaved.", "info");
      }
    });
    
  </script>

</body>
</html>

