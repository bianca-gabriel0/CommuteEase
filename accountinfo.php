<?php
// Start the session
session_start();

// 1. STRICT AUTH GUARD: Do not allow public access to this page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // Stop the page from loading
}

// Prepare dynamic data using session variables
// We assume the login/signup scripts are correctly saving these three keys:
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');
$lastName = htmlspecialchars($_SESSION['last_name'] ?? ''); 
$userEmail = htmlspecialchars($_SESSION['email'] ?? 'email.not.found@example.com'); 

// NEW: Combine first and last name for single display
$fullName = trim($firstName . ' ' . $lastName);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Account</title>
  <link rel="stylesheet" href="accountinfo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  
</head>
<body>

  <!-- nav bar -->
  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href="Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php">HOME</a>
      <a href="schedule-main.php">SCHEDULE</a>
      <a href="Home.php#about">ABOUT</a>
      <a href="accountinfo.php" class="active">ACCOUNT</a>
           
      <div class="welcome-message">
        Hi, <?php echo $firstName; ?>!
      </div>

      <div class="notification-icon">
        <i class="fa-solid fa-bell"></i>
      </div>
      <div class="notification-dropdown" id="notificationDropdown">
        <p>No new notifications</p>
      </div>

    </nav>
  </header>

  <!-- Account Section -->
  <section class="account-section">
    <h2 class="account-heading">Account Information</h2>

    <div class="account-container">
      <!-- Profile Card -->
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-pic">
            <img src="assets/profile.png" alt="User Profile">
          </div>
        </div>

        <div class="profile-details">
          <h3></h3>
          <h3><?php echo $fullName; ?></h3> 
          <a href="#" class="edit-link" onclick="goToEdit()"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
          <hr>
          <!-- UPDATED: Display actual email -->
          <p><b>Email:</b> <?php echo $userEmail; ?></p>
        </div>

        <!-- UPDATED: This button now opens the modal -->
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
            <tr>
              <td>SM Mall</td>
              <td>Bus</td>
              <td>Dagupan – Manaoag</td>
              <td>5:30 AM</td>
              <td>6:30 AM</td>
              <td>Every 30 minutes</td>
              <td><span class="delete-btn" onclick="deleteRow(this)">🗑️</span></td>
            </tr>
            <tr>
              <td>SM Mall</td>
              <td>Jeepney</td>
              <td>Dagupan – Lingayen</td>
              <td>6:00 AM</td>
              <td>7:20 AM</td>
              <td>Every 15 minutes</td>
              <td><span class="delete-btn" onclick="deleteRow(this)">🗑️</span></td>
            </tr>
          </tbody>
        </table>
                  <div class="table-arrows">
  <button>&lt;</button>
  <button>&gt;</button>
</div>
      </div>
    </div>
    
  </section>

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
          <a href="mailto:commuteease@gmail.com">commuteease@gmail.com</a>
          <p>Phone:</p>
          <a>(+63) 9123 456 789</a>
          <button id="backToTop">Back to Top ↑</button>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2025 CommuteEase. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- NEW: Logout Confirmation Modal HTML -->
  <div id="logoutModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
      <h4>Confirm Log Out</h4>
      <p>Are you sure you want to log out?</p>
      <div class="custom-modal-buttons">
        <button id="cancelLogoutBtn" class="modal-btn cancel">Cancel</button>
        <button id="confirmLogoutBtn" class="modal-btn confirm">Log Out</button>
      </div>
    </div>
  </div>


  <!-- JS Functions -->
  <script>
    // This querySelector might fail if #backToTop is not on this page.
    // Added a check to prevent errors.
    const backToTop = document.getElementById("backToTop");
    if(backToTop) {
        backToTop.addEventListener("click", () => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    function deleteRow(button) {
      const row = button.closest("tr");
      row.remove();
    }

    // UPDATED: This function now OPENS the modal instead of logging out
    function openLogoutModal() {
      document.getElementById('logoutModal').classList.add('show');
    }

    // NEW: Function to CLOSE the modal
    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.remove('show');
    }

    // NEW: Function to CONFIRM the logout
    function confirmLogout() {
      // This is the original action
      window.location.href = 'logout.php';
    }

    function goToEdit() {
      window.location.href = "editprofile.php";
    }
    
    // Notification JS is fine
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

    // --- NEW: Event Listeners for Logout Modal ---
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

    // Add event listeners
    cancelLogoutBtn.addEventListener('click', closeLogoutModal);
    confirmLogoutBtn.addEventListener('click', confirmLogout);
    
    // Close if user clicks on the gray backdrop
    logoutModal.addEventListener('click', (event) => {
      if (event.target == logoutModal) {
        closeLogoutModal();
      }
    });
    
  </script>

</body>
</html>

