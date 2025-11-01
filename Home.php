<?php
// Start the session to check for login status
session_start();

// FLEXIBLE ACCESS: We check the login status but DO NOT redirect if false.
$is_logged_in = isset($_SESSION['user_id']); 

// If the user is logged in, we grab their name for the greeting.
$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');

// --- NEW: NOTIFICATION LOGIC ---
// We must include the DB file to make queries
include 'php/db.php'; 

$unread_count = 0;
$notifications = [];

// Only fetch notifications if the user is logged in
if ($is_logged_in) {
    $current_user_id = $_SESSION['user_id'];

    // 1. Get the count of *unread* notifications
    $unread_sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
    $unread_stmt = $conn->prepare($unread_sql);
    $unread_stmt->bind_param("i", $current_user_id);
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_row()[0];
    $unread_stmt->close();

    // 2. Get the 5 most recent notifications (read or unread)
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

$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CommuteEase - Home</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="home-style.css">
  
  
</head>

<body>

  <!-- nav bar -->
  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href = "Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php" class="active">HOME</a>
      <a href="schedule-main.php">SCHEDULE</a>
      <a href="#about">ABOUT</a>
      
      <!-- DYNAMIC LINKS: Shows LOGIN/SIGN UP for guests, ACCOUNT for members -->
      <?php if ($is_logged_in): ?>
        <a href="accountinfo.php">ACCOUNT</a>
      <?php else: ?>
        <a href="login.php">LOGIN</a>
        <a href="signup.php">SIGN UP</a>
      <?php endif; ?>
      
      <div class="welcome-message">
        <!-- FIX: Display the user's name if they are logged in -->
        <?php if ($is_logged_in): ?>
          Hi, <?php echo $firstName; ?>!
        <?php endif; ?>
      </div>
      
      <!-- 
        UPDATED: Notification Bell and Dropdown 
        Now only shows if logged in, and uses the PHP variables
      -->
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
      <!-- END UPDATED NOTIFICATION -->

    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <h2>Get the schedules you need,<br>when you need them!</h2>
    <a href="schedule-main.php" class="btn" id="goNowBtn">Go now!</a>
  </section>

  <!-- Available terminal Section -->
  <section class="terminals">

  <div class="terminal-cards">
    <div class="terminal-card">
      <img src="assets/solid-north.png" alt="Solid North">
      <div class="terminal-label">Solid North Dagupan</div>
    </div>
        <div class="terminal-card">
      <img src="assets/dagupan-bus.png" alt="Dagupan Bus Terminal">
      <div class="terminal-label">Dagupan Bus</div>
    </div>
    <div class="terminal-card">
      <img src="assets/victory-liner.png" alt="Victory Liner">
      <div class="terminal-label">Victory Liner Dagupan</div>
    </div>
    <div class="terminal-card">
      <img src="assets/five-star.png" alt="Five Star">
      <div class="terminal-label">Five Star Dagupan</div>
    </div>
    <div class="terminal-card">
      <img src="assets/SM-mall.png" alt="SM Dagupan">
      <div class="terminal-label">SM Center Dagupan</div>
    </div>
  </div>
</section>

  <!-- About Section -->
  <section id="about" class="about">
  <div class="about-text">
    <h3><span class="underline">ABOUT</span></h3>
    <p>
      Public transportation in Dagupan City, Pangasinan, is widely used by workers, students, 
      and residents, but commuters often face uncertain bus and jeepney schedules. This leads 
      to wasted time, long queues, and overcrowded vehicles.
    </p>
    <p>
      CommuteEase provides Dagupan commuters with a clear and accessible timetable system. By 
      allowing users to see which buses or jeepneys are available at specific times—and which 
      departures are coming within the next few minutes—the system helps make daily commuting 
      more efficient and less stressful.
    </p>
  </div>

  <div class="about-image">
    <div class="image-card">
      <img src="assets/bus-imagevl.png" alt="Victory" class="active">
      <img src="assets/bus-sample.png" alt="Bus">
      <img src="assets/bus-1.png" alt="City Traffic">
    </div>
  </div>
</section>

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
      <p>&copy; 2025 CommuteEase.</p> <!-- Corrected the missing closing tag -->
    </div>
  </div>
</footer>


<script>
  // --- NEW: Pass PHP login status to JavaScript ---
  const isUserLoggedIn = <?php echo json_encode($is_logged_in); ?>;

  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // --- UPDATED: Notification Bell Code ---
  // We only run this if the user is logged in (meaning the bell exists)
  if (isUserLoggedIn) {
      const bell = document.querySelector('.notification-icon');
      const dropdown = document.getElementById('notificationDropdown'); 
      
      bell.addEventListener("click", (event) => {
          event.stopPropagation(); 
          dropdown.classList.toggle("show");
          
          // Check if it does NOT have the 'read' class (meaning it's unread)
          if (!bell.classList.contains('read')) {
              
              // 1. Add the 'read' class immediately to hide the dot
              bell.classList.add('read');
              
              // 2. Send a request to the server to mark all as read
              fetch('php/mark_notifications_read.php', {
                  method: 'POST'
              })
              .then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      // 3. Mark all items in dropdown as read (remove blue background)
                      dropdown.querySelectorAll('.notification-item.unread').forEach(item => {
                          item.classList.remove('unread');
                      });
                  } else {
                      console.error('Failed to mark notifications as read');
                      // If it failed, remove the 'read' class to show the dot again
                      bell.classList.remove('read');
                  }
              })
              .catch(error => {
                  console.error('Error with fetch:', error);
                  // Also remove 'read' class on error
                  bell.classList.remove('read');
              });
          }
      });

      document.addEventListener("click", (event) => {
        // Must check if bell and dropdown exist before checking contains
        if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
          dropdown.classList.remove("show");
        }
      });
  }
  // -----------------------------------------------------------------


  // --- Image Slider JS ---
  const images = document.querySelectorAll('.image-card img');
  let current = 0;

  function showNextImage() {
    if (images.length > 0) { // Only run if images exist
        images[current].classList.remove('active');
        current = (current + 1) % images.length;
        images[current].classList.add('active');
    }
  }

  setInterval(showNextImage, 5000); // switch every 5 seconds
  
  // --- Hero Button Redirect JS ---
  const goNowBtn = document.getElementById("goNowBtn");
  if (goNowBtn) {
    goNowBtn.addEventListener("click", (e) => {
      e.preventDefault();
      goNowBtn.textContent = "Loading...";
      goNowBtn.style.opacity = "0.8";
      setTimeout(() => {
        window.location.href = goNowBtn.getAttribute("href");
      }, 800);
    });
  }

  // --- Other JS (Fade-in, etc.) ---
  const aboutSection = document.querySelector(".about");
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        aboutSection.classList.add("visible");
      }
    });
  }, { threshold: 0.3 });
  if (aboutSection) observer.observe(aboutSection);
  
</script>
</body>
</html>
