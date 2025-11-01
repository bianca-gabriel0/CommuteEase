<?php
session_start();


if (!isset($_SESSION['user_id_to_reset'])) {
    header("Location: admin_forgot_password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Reset Password</title>
  <!-- Add cache-buster to CSS link -->
  <link rel="stylesheet" href="admin_auth.css?v=<?php echo time(); ?>">
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  
  <!-- Add inline styles for the password icon, just in case -->
  <style>
    .password-container {
        position: relative;
    }
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        width: 22px;
        height: 22px;
        fill: #555;
        opacity: 1;
    }
    .auth-form-container .form-group input {
        /* Ensure input padding accommodates the icon */
        padding-right: 45px;
    }
  </style>
</head>
<body>
  <div class="auth-container">

    <!-- Left Sidebar -->
    <div class="auth-sidebar">
      <img src="assets/CE-logo.png" alt="Commute Ease Logo" />
      <h2>Commute Ease</h2>
      <p>Administrator Password Reset</p>
    </div>

    <!-- Right Form Area -->
    <div class="auth-form-container">
      <h2>Set New Password</h2>
      <p style="color: #555; margin-bottom: 20px;">You are verified. Please enter your new password.</p>

      <?php
        // Check for error messages
        if (isset($_GET['error'])) {
          $error = $_GET['error'];
          $message = 'An unknown error occurred.';
          if ($error == 'mismatch') {
            $message = '⚠️ Passwords do not match. Please try again.';
          } else if ($error == 'weak') {
            $message = '⚠️ Password must be 8+ characters and include a number.';
          }
          echo '<div class="error-notification">' . $message . '</div>';
        }
      ?>

      <!-- This form points to our new backend script -->
      <form id="resetPasswordForm" method="POST" action="process_admin_reset.php" novalidate>
        
        <div class="form-group">
          <label for="password">New Password</label>
          <div class="password-container">
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
            <svg id="eyeIconNew" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
              <circle cx="12" cy="12" r="2.5"/>
            </svg>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirmPassword">Confirm New Password</label>
          <div class="password-container">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-type your password" required>
            <svg id="eyeIconConfirm" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
              <circle cx="12" cy="12" r="2.5"/>
            </svg>
          </div>
        </div>

        <button type="submit" class="auth-btn">Save New Password</button>
      </form>
    </div>

  </div>

  <script>
    const form = document.getElementById("resetPasswordForm");
    const newPass = document.getElementById("password");
    const confirmPass = document.getElementById("confirmPassword");
    const eyeIconNew = document.getElementById("eyeIconNew");
    const eyeIconConfirm = document.getElementById("eyeIconConfirm");

    function togglePassword(input, icon) {
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        icon.style.opacity = isHidden ? "0.6" : "1";
    }
    eyeIconNew.addEventListener("click", () => {
        togglePassword(newPass, eyeIconNew);
    });

    eyeIconConfirm.addEventListener("click", () => {
        togglePassword(confirmPass, eyeIconConfirm);
    });

    form.addEventListener("submit", function(e) {
        if (newPass.value !== confirmPass.value) {
            e.preventDefault(); 
            alert("Passwords do not match. Please try again.");
        }
    });
  </script>
</body>
</html>

