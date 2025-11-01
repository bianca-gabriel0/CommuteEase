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


// --- NOTIFICATION LOGIC ---
$unread_count = 0;
$notifications = [];

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
// --- END OF NOTIFICATION LOGIC ---


// --- User Info ---
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
  <title>CommuteEase - Account</title>
  <link rel="stylesheet" href="accountinfo.css"> 
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
      <div class="welcome-message">Hi, <?php echo $firstName; ?>!</div>
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
      
    </nav>
  </header>

  <section class="account-section">
    <h2 class="account-heading">Account Information</h2>
    <div class="account-container">
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

                      <button type="button" class="delete-btn" title="Unsave this schedule">🗑️</button>
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
      <div class="footer-bottom"><p>&copy; 2025 CommuteEase. All rights reserved.</p></div>
    </div>
  </footer>

  <div id="logoutModal" class="custom-modal-backdrop">
    <div class="custom-modal-content">
      <h4>Confirm Log Out</h4><p>Are you sure you want to log out?</p>
      <div class="custom-modal-buttons">
        <button id="cancelLogoutBtn" class="modal-btn cancel">Cancel</button>
        <button id="confirmLogoutBtn" class="modal-btn confirm">Log Out</button>
      </div>
    </div>
  </div>

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


  <script>
    const backToTop = document.getElementById("backToTop");
    if(backToTop) {
        backToTop.addEventListener("click", () => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    function openLogoutModal() { document.getElementById('logoutModal').classList.add('show'); }
    function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('show'); }
    function confirmLogout() { window.location.href = 'logout.php'; }
    function goToEdit() { window.location.href = "editprofile.php"; }
    
    // --- Notification Bell Logic ---
    const bell = document.querySelector('.notification-icon');
    const dropdown = document.getElementById('notificationDropdown');
    
    bell.addEventListener('click', (event) => {
      event.stopPropagation();
      dropdown.classList.toggle('show');
      
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
    
    document.addEventListener('click', (event) => {
      if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
      }
    });

    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    cancelLogoutBtn.addEventListener('click', closeLogoutModal);
    confirmLogoutBtn.addEventListener('click', confirmLogout);
    logoutModal.addEventListener('click', (event) => {
      if (event.target == logoutModal) { closeLogoutModal(); }
    });
    
    let formToSubmit = null; 
    const deleteModal = document.getElementById('deleteScheduleModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    document.querySelectorAll('.delete-btn[type="button"]').forEach(button => {
      button.addEventListener('click', (event) => {
        formToSubmit = event.target.closest('form.delete-schedule-form');
        deleteModal.classList.add('show');
      });
    });

    function closeDeleteModal() {
      deleteModal.classList.remove('show');
      formToSubmit = null; 
    }

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

    
    // --- Notification Toast Logic  ---
    
    function showNotification(message, type = 'success') {
      const toast = document.getElementById("notification-toast");
      toast.innerHTML = message;
      toast.className = "show " + type; 
      setTimeout(() => { 
        toast.className = toast.className.replace("show", ""); 
        if (window.history.replaceState) {
          const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('page')) {
            window.history.replaceState({path: cleanURL}, '', cleanURL + '?page=' + urlParams.get('page'));
          } else {
            window.history.replaceState({path: cleanURL}, '', cleanURL);
          }
        }
      }, 3000); 
    }

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
