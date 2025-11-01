<?php 
session_start(); 

$message = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Login</title>
  
  <link rel="stylesheet" href="admin_auth.css?v=<?php echo time(); ?>"/>
  
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-container">
    
    <!-- Left Sidebar -->
    <div class="auth-sidebar">
      <img src="assets/CE-logo.png" alt="Commute Ease Logo" />
      <h2>Commute Ease</h2>
      <p>Administrator Dashboard Login</p>
    </div>

    <!-- Right Form Area -->
    <div class="auth-form-container">
      <h2>Welcome Back, Admin</h2>

      <?php
        // Check for login errors
        if (isset($_GET['error'])) {
          $error_message = 'An unknown error occurred.';
          if ($_GET['error'] == 'invalid_credentials') {
            $error_message = '⚠️ Incorrect email or password.';
          } else if ($_GET['error'] == 'not_admin') {
            $error_message = '⚠️ Access Denied. You are not an administrator.';
          }
          echo '<div class="error-notification">' . $error_message . '</div>';
        }
        
        // Check for success (e.g., from logout or password reset)
        if (isset($_GET['message'])) {
            if ($_GET['message'] == 'logged_out') {
              $message = '✅ You have been logged out successfully.';
            } else if ($_GET['message'] == 'password_updated') {
              $message = '✅ Password updated. Please log in.';
            } else if ($_GET['message'] == 'signup_success' || $_GET['message'] == 'account_created') {
              $message = '✅ Admin account created. Please log in.';
            }
            
            if (!empty($message)) {
                echo '<div class="success-notification">' . $message . '</div>';
            }
        }
      ?>

      <form id="loginForm" novalidate action="process_admin_login.php" method="POST">
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required />
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Enter your password" required />
            <svg id="eyeIcon" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
              <circle cx="12" cy="12" r="2.5"/>
            </svg>
          </div>
        </div>

        <div class="forgot-password">
          <a href="admin_forgot_password.php">Forgot password?</a>
        </div>

        <button type="submit" class="auth-btn" id="submitBtn">Sign In</button>
      </form>

      <p class="bottom-link">
        Need an admin account? <a href="admin_signup.php">Sign up</a>
      </p>
    </div>

  </div>

  <script>
    // Simple toggle for password visibility
    const eyeIcon = document.getElementById("eyeIcon");
    const password = document.getElementById("password");
    eyeIcon.addEventListener("click", () => {
      const isHidden = password.type === "password";
      password.type = isHidden ? "text" : "password";
      eyeIcon.style.opacity = isHidden ? "0.6" : "1";
    });
  </script>
</body>
</html>

