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
      <img src="assets/CE-logo.png" a href = "Home" alt="Commute Ease Logo">
    </div>
    <nav class="nav-links">
  <a href="Home">HOME</a>
  <a href="schedule-main">SCHEDULE</a>
  <a href="Home">ABOUT</a>
  <a href="accountinfo" class="active">ACCOUNT</a>
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
          <h3>Juan Dela Cruz</h3>
          <a href="#" class="edit-link" onclick="goToEdit()"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
          <hr>
          <p><b>Email:</b> user@gmail.com</p>
          <p><b>Phone:</b> (+63) 9123 456 789</p>
        </div>

        <button class="logout-btn" onclick="logout()">↳ Log out</button>
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

  <!-- JS Functions -->
  <script>
    const backToTop = document.getElementById("backToTop");
    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    function deleteRow(button) {
      const row = button.closest("tr");
      row.remove();
    }

    function logout() {
      alert("You have logged out!");
    }

    function goToEdit() {
      window.location.href = "editprofile.php";
    }
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
    
  </script>

</body>
</html>
