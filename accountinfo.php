<?php
// Start the session
session_start();

// 1. STRICT AUTH GUARD: Do not allow public access to this page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // Stop the page from loading
}

// 2. --- MODIFIED: DATABASE LOGIC ---
include 'php/db.php'; // Include your database connection

// NEW: --- PAGINATION SETUP ---
$items_per_page = 7; // You wanted 7 per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1; // Safety check, can't be on page 0
}
// Calculate the starting point for the SQL query
$offset = ($current_page - 1) * $items_per_page;
// --- END: PAGINATION SETUP ---


// Get the current user's ID
$current_user_id = $_SESSION['user_id'];
$saved_schedules = []; // Initialize an empty array to hold the schedules

// NEW: --- TOTAL COUNT QUERY ---
// We need to know the total number of items *before* we get the paged items
$total_items = 0;
$total_pages = 0;

// This query MUST match the WHERE clause of the main query below
$count_sql = "SELECT COUNT(ss.saved_id)
              FROM schedule s
              JOIN saved_schedules ss ON s.schedule_id = ss.schedule_id
              WHERE ss.user_id = ? AND s.is_deleted = FALSE";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $current_user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_row()[0]; // Gets the first column of the first row (the count)
$count_stmt->close();

// Only run the main query if there are items to show
if ($total_items > 0) {
    // Calculate total pages
    $total_pages = ceil($total_items / $items_per_page);

    // This is your original SQL query that links the tables
    // NEW: Added 'ORDER BY' and 'LIMIT / OFFSET'
    $sql = "SELECT 
                s.location, 
                s.type, 
                s.destination, 
                s.departure_time, 
                s.estimated_arrival, 
                s.frequency, 
                ss.saved_id 
            FROM 
                schedule s
            JOIN 
                saved_schedules ss ON s.schedule_id = ss.schedule_id
            WHERE 
                ss.user_id = ? AND s.is_deleted = FALSE
            ORDER BY 
                ss.saved_id DESC -- It's good practice to have an ORDER BY for pagination
            LIMIT ? OFFSET ?"; // <-- NEW: Added placeholders

    $stmt = $conn->prepare($sql);
    // NEW: Bind 3 params: user_id (i), limit (i), offset (i)
    $stmt->bind_param("iii", $current_user_id, $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $saved_schedules[] = $row; // Add each saved schedule to the array
        }
    }
    $stmt->close();
}
// $conn->close(); // MOVED: This was line 37, moved down
// --- END MODIFIED DATABASE LOGIC ---


// 3. Prepare dynamic data using session variables (Original Code)
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');
$lastName = htmlspecialchars($_SESSION['last_name'] ?? ''); 
$userEmail = htmlspecialchars($_SESSION['email'] ?? 'email.not.found@example.com'); 

// Combine first and last name for single display
$fullName = trim($firstName . ' ' . $lastName);

// NEW: We must close the connection *after* ALL database work is done
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Account</title>
  <link rel="stylesheet" href="accountinfo.css"> <!-- This links to your correct CSS file -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <!-- 
    NOTE: I removed the <style> block from here because
    you confirmed the styles are in your accountinfo.css file.
    This is cleaner!
  -->
  
</head>
<body>

  <!-- nav bar (NO CHANGES) -->
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

  <!-- Account Section (NO CHANGES) -->
  <section class="account-section">
    <h2 class="account-heading">Account Information</h2>

    <div class="account-container">
      <!-- Profile Card (NO CHANGES) -->
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
                    <!-- This form is the same -->
                    <form action="unsave_schedule.php" method="POST" style="display: inline;">
                      <input type="hidden" name="saved_id" value="<?php echo $schedule['saved_id']; ?>">
                      <button type="submit" class="delete-btn" title="Unsave this schedule">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            <!-- --- END: PHP Loop --- -->
            
          </tbody>
        </table>
            
            <!-- 
              ==============================================================
              --- THIS IS THE IMPORTANT PART ---
              This HTML block uses the class names that your CSS file
              is looking for. This will fix the "all white" button issue.
              ==============================================================
            -->
            <?php if ($total_pages > 1): ?>
              <div class="pagination-controls">
                  
                  <!-- 'Previous' Link -->
                  <?php if ($current_page > 1): ?>
                      <a href="?page=<?php echo $current_page - 1; ?>" class="page-link">&lt;</a>
                  <?php else: ?>
                      <!-- This is the disabled state -->
                      <span class="page-link-disabled">&lt;</span>
                  <?php endif; ?>
                  
                  <!-- 'Next' Link -->
                  <?php if ($current_page < $total_pages): ?>
                      <a href="?page=<?php echo $current_page + 1; ?>" class="page-link">&gt;</a>
                  <?php else: ?>
                      <!-- This is the disabled state -->
                      <span class="page-link-disabled">&gt;</span>
                  <?php endif; ?>
                  
              </div>
            <?php endif; ?>
            <!-- --- END: PAGINATION LINKS --- -->
            
      </div>
    </div>
    
  </section>

  <!-- Footer (NO CHANGES) -->
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

  <!-- Logout Modal (NO CHANGES) -->
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


  <!-- JS Functions (NO CHANGES) -->
  <script>
    const backToTop = document.getElementById("backToTop");
    if(backToTop) {
        backToTop.addEventListener("click", () => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    function openLogoutModal() {
      document.getElementById('logoutModal').classList.add('show');
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.remove('show');
    }

    function confirmLogout() {
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

    // --- Event Listeners for Logout Modal ---
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

