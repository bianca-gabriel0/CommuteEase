<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); 
}

include 'php/db.php'; 

$items_per_page = 7; 
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1; 
}
$offset = ($current_page - 1) * $items_per_page;


$current_user_id = $_SESSION['user_id'];
$saved_schedules = []; 
$total_items = 0;
$total_pages = 0;


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

$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');
$lastName = htmlspecialchars($_SESSION['last_name'] ?? ''); 
$userEmail = htmlspecialchars($_SESSION['email'] ?? 'email.not.found@example.com'); 
$fullName = trim($firstName . ' ' . $lastName);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Edit Profile</title>
  <link rel="stylesheet" href="editprofile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
</head>
<body>

  <div id="notification-toast"></div>

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
      
      <div class="notification-icon"><i class="fa-solid fa-bell"></i></div>
      <div class="notification-dropdown" id="notificationDropdown"><p>No new notifications</p></div>
    </nav>
  </header>

  <section class="account-section">
    <h2 class="account-heading">Edit Profile</h2>

    <div class="account-container">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-pic">
            <img src="assets/profile.png" alt="User Profile">
          </div>
        </div>

        <div class="profile-details">
          <h3>Edit Personal Information</h3>
          <hr>
          <form id="editProfileForm">
            <div class="form-group">
              <label for="firstName">First Name:</label>
              <input type="text" id="firstName" placeholder="Enter first name" value="<?php echo $firstName; ?>">
            </div>
            <div class="form-group">
              <label for="lastName">Last Name:</label>
              <input type="text" id="lastName" placeholder="Enter last name" value="<?php echo $lastName; ?>">
            </div>
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" placeholder="Enter email address" value="<?php echo $userEmail; ?>">
            </div>
            <div class="form-group">
              <label for="password">New Password:</label>
              <input type="password" id="password" placeholder="Enter new password">
            </div>
            <div class="form-group">
              <label for="confirmPassword">Confirm Password:</label>
              <input type="password" id="confirmPassword" placeholder="Confirm new password">
            </div>
          </form>
        </div>

        <div class="edit-buttons">
          <button class="save-btn" onclick="saveProfile()">Save Changes</button>
          <button class="cancel-btn" onclick="cancelEdit()">Cancel</button>
        </div>
      </div>

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
                    <form action="unsave_schedule.php" method="POST" class="delete-schedule-form" style="display: inline;">
                      <input type="hidden" name="saved_id" value="<?php echo $schedule['saved_id']; ?>">
                      <button type="button" class="delete-btn" title="Unsave this schedule" style="border: none; background: none; cursor: pointer; font-size: 1.25rem;">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
          <div class="pagination-controls">
              <?php if ($current_page > 1): ?>
                  <a href="editprofile.php?page=<?php echo $current_page - 1; ?>" class="page-link">&lt;</a>
              <?php else: ?>
                  <span class="page-link-disabled">&lt;</span>
              <?php endif; ?>
              
              <?php if ($current_page < $total_pages): ?>
                  <a href="editprofile.php?page=<?php echo $current_page + 1; ?>" class="page-link">&gt;</a>
              <?php else: ?>
                  <span class="page-link-disabled">&gt;</span>
              <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <footer>
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
      <!-- --- NEW: Added greeting to footer --- -->
      <div class="footer-bottom">
        <p>Hi, <?php echo $firstName; ?>!</p>
        <p>&copy; 2025 CommuteEase. All rights reserved.</p>
      </div>
    </div>
  </footer>

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


  <!-- JS -->
  <script>
    // Back to top
    const backToTop = document.getElementById("backToTop");
    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    function saveProfile() {
      // (Your existing saveProfile logic is unchanged)
      const first = document.getElementById("firstName").value.trim();
      const last = document.getElementById("lastName").value.trim();
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();
      const confirmPassword = document.getElementById("confirmPassword").value.trim();

      if (!first || !last || !email) {
        alert("Please fill out all fields.");
        return;
      }

      if (password && password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      // NOTE: This is where you would have your backend AJAX call
      // to actually save the profile data.
      alert(`Profile updated for: ${first} ${last}`);
      window.location.href = "accountinfo.php";
    }

    // Cancel edit (Original)
    function cancelEdit() {
      if (confirm("Discard changes?")) {
        window.location.href = "accountinfo.php";
      }
    }

    // Notification Bell (Original)
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

    // --- NEW: Delete Schedule Modal Logic ---
    let formToSubmit = null; 
    const deleteModal = document.getElementById('deleteScheduleModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // 1. Find ALL delete buttons and add a click listener
    document.querySelectorAll('.delete-btn[type="button"]').forEach(button => {
      button.addEventListener('click', (event) => {
        formToSubmit = event.target.closest('form.delete-schedule-form');
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
      if (formToSubmit) {
        formToSubmit.submit();
      }
    });
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
        // Clean up the URL
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
        showNotification("Schedule unsaved.", "info");
      }
    });
    
  </script>

</body>
</html>

