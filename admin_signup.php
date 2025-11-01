<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Create Account</title>
  <link rel="stylesheet" href="admin_auth.css">
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-container">

    <!-- Left Sidebar -->
    <div class="auth-sidebar">
      <img src="assets/CE-logo.png" alt="Commute Ease Logo" />
      <h2>Commute Ease</h2>
      <p>Create a New Administrator Account</p>
    </div>

    <!-- Right Form Area -->
    <div class="auth-form-container">
      <h2>Create Account</h2>

      <?php
        // Check for signup errors
        if (isset($_GET['error'])) {
          $error_message = 'An unknown error occurred.';
          if ($_GET['error'] == 'email_exists') {
            $error_message = '⚠️ This email address is already registered.';
          } else if ($_GET['error'] == 'password_mismatch') {
            $error_message = '⚠️ Passwords do not match.';
          }
          echo '<div class="error-notification">' . $error_message . '</div>';
        }
      ?>

      <form id="signupForm" method="POST" action="process_admin_signup.php" novalidate>
        
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" name="firstName" placeholder="Enter first name" required>
        </div>

        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" name="lastName" placeholder="Enter last name" required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
        </div>
        
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-type your password" required>
        </div>

        <button type="submit" class="auth-btn" id="submitBtn">Create Account</button>
      </form>

      <p class="bottom-link">
        Already have an account? <a href="admin_login.php">Sign in</a>
      </p>
    </div>

  </div>
  <!-- You can add JS validation back here if needed -->
</body>
</html>
