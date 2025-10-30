<?php
// 
// --- FIX #1: THE "AUTH GUARD" ---
// We MUST start the session at the very top to check for the login "wristband"
session_start();

// Check if the user_id "wristband" is NOT set
if (!isset($_SESSION['user_id'])) {
    // If they are not logged in, kick them back to the login page
    header("Location: login.php");
    exit(); // Stop the rest of the page from loading
}

// If the script gets past this point, the user IS logged in.
// 
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

  <header class="navbar">
    <div class="logo">
      <img src="assets/CE-logo.png" a href = "Home.php" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
      <a href="Home.php" class="active">HOME</a>
      <a href="schedule-main.php">SCHEDULE</a>
      <a href="#about">ABOUT</a>
      <a href="accountinfo.php">ACCOUNT</a>
      
      <div class="welcome-message">
        Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
      </div>
      
      <div class="notification-icon">
        <i class="fa-solid fa-bell"></i>
      </div>
      <div class="notification-dropdown" id="notificationDropdown">
        <p>No new notifications</p>
      </div>
    </nav>
  </header>

  <section class="hero">
    <h2>Get the schedules you need,<br>when you need them!</h2>
    <a href="schedule-main.php" class="btn" id="goNowBtn">Go now!</a>
  </section>

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
      <p>&copy; 2025 CommuteEase.


<script>
  const backToTop = document.getElementById("backToTop");
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
</script>


  <script>
      const images = document.querySelectorAll('.image-card img');
  let current = 0;

  function showNextImage() {
    images[current].classList.remove('active');
    current = (current + 1) % images.length;
    images[current].classList.add('active');
  }

  setInterval(showNextImage, 5000); // switch every 10 seconds
  
  const bell = document.querySelector('.notification-icon');
  const dropdown = document.getElementById('notificationDropdown');
  const redDot = document.querySelector('.notification-icon::after'); // for visual note only

  bell.addEventListener('click', (event) => {
    event.stopPropagation();
    dropdown.classList.toggle('show');

    // Remove red badge after clicking (simulate "read" state)
    bell.classList.add('read');
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (event) => {
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });


    // Navigation Active State
    const navLinks = document.querySelectorAll(".nav-link");
    navLinks.forEach(link => {
      link.addEventListener("click", (e) => {
        // Handle smooth scroll for #about
        if (link.getAttribute("href").startsWith("#")) {
          e.preventDefault();
          const target = document.querySelector(link.getAttribute("href"));
          if (target) {
            window.scrollTo({
              top: target.offsetTop - 60,
              behavior: "smooth"
            });
          }
        }

        // Update active link
        navLinks.forEach(l => l.classList.remove("active"));
        link.classList.add("active");
      });
    });

    // Highlight current page based on URL
    navLinks.forEach(link => {
      if (link.href === window.location.href) {
        link.classList.add("active");
      }
    });

    // Hero Button Redirect
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

    // Fade-in for About section
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