<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); 
}

// NOTE: Uses your confirmed 'php/db.php' file which returns the $conn object.
include 'php/db.php'; 

$current_user_id = $_SESSION['user_id'];
$firstName = 'Guest';
$lastName = ''; 
$userEmail = 'email.not.found@example.com'; 
$fullName = '';

// --- 1. FETCH USER DETAILS FOR FORM PRE-FILL ---
// We fetch the current details directly from the database for accuracy.
// UPDATED: Fetches first_name, last_name, and email based on your table structure.
$sql_user = "SELECT first_name, last_name, email FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user) {
    $stmt_user->bind_param("i", $current_user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();

    if ($user_result->num_rows === 1) {
        $user_row = $user_result->fetch_assoc();
        $firstName = htmlspecialchars($user_row['first_name']);
        $lastName = htmlspecialchars($user_row['last_name']);
        $userEmail = htmlspecialchars($user_row['email']);
        
        // Update session variables if necessary (optional, but keeps session fresh)
        $_SESSION['first_name'] = $user_row['first_name'];
        $_SESSION['last_name'] = $user_row['last_name'];
        $_SESSION['email'] = $user_row['email'];
        
    } else {
        // Logged-in user ID not found in database
        error_log("CRITICAL: User ID $current_user_id not found in DB.");
    }
    $stmt_user->close();
} else {
    error_log("Error preparing user fetch statement: " . $conn->error);
}

$fullName = trim($firstName . ' ' . $lastName);

// --- 2. EXISTING SCHEDULE FETCHING LOGIC (Unchanged from your original code) ---
$items_per_page = 7; 
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1; 
}
$offset = ($current_page - 1) * $items_per_page;

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
  <div id="cancelEditModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
      <h4>Discard Changes?</h4>
      <p>Any unsaved profile changes will be lost if you cancel.</p>
      <div class="custom-modal-buttons">
        <button id="cancelCancelBtn" class="modal-btn cancel">Continue Editing</button>
        <button id="confirmCancelBtn" class="modal-btn confirm-cancel">Discard & Exit</button>
      </div>
    </div>
  </div>


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
          <!-- Added required 'currentPassword' field for security -->
          <form id="editProfileForm">
            <div class="form-group">
              <label for="firstName">First Name:</label>
              <input type="text" id="firstName" name="firstName" placeholder="Enter first name" value="<?php echo $firstName; ?>">
            </div>
            <div class="form-group">
              <label for="lastName">Last Name:</label>
              <input type="text" id="lastName" name="lastName" placeholder="Enter last name" value="<?php echo $lastName; ?>">
            </div>
            <div class="form-group">
              <label for="email">Email:</label>
              <!-- MODIFIED: Added 'disabled' attribute to make email non-editable -->
              <input type="email" id="email" name="email" disabled placeholder="Enter email address" value="<?php echo $userEmail; ?>">
            </div>
            
            <!-- NEW PASSWORD FIELD with EYE ICON -->
            <div class="form-group">
              <label for="password">New Password (Optional):</label>
              <div class="password-input-container">
                <input type="password" id="password" name="newPassword" placeholder="Enter new password">
                <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePasswordVisibility('password')"></i>
              </div>
            </div>
            
            <!-- CONFIRM PASSWORD FIELD with EYE ICON -->
            <div class="form-group">
              <label for="confirmPassword">Confirm New Password:</label>
              <div class="password-input-container">
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePasswordVisibility('confirmPassword')"></i>
              </div>
            </div>
            
            <!-- NEW: Local Error Message Display Container -->
            <div id="password-error" class="error-message"></div>

            <hr>
            
            <!-- CURRENT PASSWORD FIELD with EYE ICON (Required for save) -->
            <div class="form-group required-field">
              <label for="currentPassword">Current Password (Required to Save):</label>
              <div class="password-input-container">
                <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password">
                <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePasswordVisibility('currentPassword')"></i>
              </div>
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
      <div class="footer-bottom">
        <p>Hi, <?php echo $firstName; ?>!</p>
        <p>&copy; 2025 CommuteEase. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Delete Schedule Confirmation Modal -->
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

    // --- NEW PASSWORD VISIBILITY TOGGLE ---
    function togglePasswordVisibility(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling; // Assuming the icon is the next sibling
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }


    // --- NEW: Notification Toast Logic ---
    function showNotification(message, type = 'success') {
      const toast = document.getElementById("notification-toast");
      toast.innerHTML = message;
      toast.className = "show " + type; 
      setTimeout(() => { 
        toast.className = toast.className.replace("show", ""); 
        
        // Clean up the URL (maintains pagination)
        if (window.history.replaceState) {
          const urlParams = new URLSearchParams(window.location.search);
          const pageParam = urlParams.has('page') ? '?page=' + urlParams.get('page') : '';
          const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname + pageParam;
          window.history.replaceState({path: cleanURL}, '', cleanURL);
        }
      }, 3000); 
    }

    // NEW: Function to clear local password errors
    function clearPasswordErrors() {
        document.getElementById("password-error").textContent = "";
    }


    // --- UPDATED saveProfile function using AJAX/Fetch ---
    async function saveProfile() {
      const form = document.getElementById("editProfileForm");
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      
      clearPasswordErrors(); // Clear any previous local errors on new submit

      // Client-side Validation (Basic)
      if (!data.firstName || !data.lastName || !data.currentPassword) {
        showNotification("Please fill out all required fields (First Name, Last Name, Current Password).", "error");
        return;
      }
      
      const newPass = data.newPassword;
      const confirmPass = data.confirmPassword;
      
      // Client-side Password Checks (if user started entering a new password)
      if (newPass || confirmPass) {
          if (!newPass || !confirmPass) {
              document.getElementById("password-error").textContent = "Please confirm your new password in both fields.";
              return;
          }
          if (newPass !== confirmPass) {
              document.getElementById("password-error").textContent = "New Passwords do not match.";
              return;
          }
          // Optional: Add client-side check for 8 characters and 1 number for immediate feedback
          if (newPass.length < 8) {
             document.getElementById("password-error").textContent = "Password must be at least 8 characters long.";
             return;
          }
          if (!/\d/.test(newPass)) {
             document.getElementById("password-error").textContent = "Password must contain at least one number.";
             return;
          }
      }
      
      // Show temporary message while loading
      showNotification("Saving changes...", "info");

      try {
        const response = await fetch('update_profile.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          showNotification(result.message, "success");
          // UPDATED: Redirect to accountinfo.php after success
          setTimeout(() => {
              window.location.href = "accountinfo.php";
          }, 1500); 

        } else {
          // Determine if the error is password-related or a generic failure
          const passwordErrors = [
              'Password must be at least 8 characters long.',
              'Password must contain at least one number.',
              'New and Confirmation passwords do not match.', // Server-side check is redundant but kept for robustness
              'The new password must be different from your current password.'
          ];

          if (passwordErrors.includes(result.message)) {
              // Show the specific password error below the fields
              document.getElementById("password-error").textContent = result.message;
          } else {
              // Show all other errors (like "Incorrect current password") in the toast
              showNotification(result.message, "error");
          }

          // Clear sensitive fields on error
          document.getElementById("currentPassword").value = "";
          document.getElementById("password").value = "";
          document.getElementById("confirmPassword").value = "";
        }
      } catch (error) {
        console.error('Fetch error:', error);
        showNotification("An unexpected error occurred. Please try again.", "error");
      }
    }

    // --- UPDATED cancelEdit function using a Modal ---
    const cancelModal = document.getElementById('cancelEditModal');
    const cancelCancelBtn = document.getElementById('cancelCancelBtn');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    
    function closeCancelModal() {
        cancelModal.classList.remove('show');
    }

    function cancelEdit() {
        cancelModal.classList.add('show');
    }

    cancelCancelBtn.addEventListener('click', closeCancelModal);
    confirmCancelBtn.addEventListener('click', () => {
        closeCancelModal();
        window.location.href = "accountinfo.php"; // Redirect as planned
    });
    cancelModal.addEventListener('click', (event) => {
      if (event.target == cancelModal) {
        closeCancelModal();
      }
    });

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
