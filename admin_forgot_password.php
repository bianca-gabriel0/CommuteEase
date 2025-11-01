<?php
session_start();

$num1 = rand(1, 9);
$num2 = rand(1, 9);

$_SESSION['captcha_answer'] = $num1 + $num2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Forgot Password</title>
  <link rel="stylesheet" href="admin_auth.css">
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .captcha-box {
      margin-top: 20px;
      margin-bottom: 25px;
    }
    .captcha-box label {
      font-size: 1.2rem;
      font-weight: 600;
      color: #333;
    }
    .captcha-box input {
      width: 100px !important; 
      padding: 12px 16px !important;
      font-size: 1.1rem !important;
      text-align: center;
      margin-top: 10px;
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
      <h2>Forgot Password?</h2>
      <p style="color: #555; margin-bottom: 20px;">Enter your admin email and solve the problem to reset your password.</p>

      <?php
        // Check for error messages from the verification script
        if (isset($_GET['error'])) {
          $error = $_GET['error'];
          $message = 'An unknown error occurred.';
          if ($error == 'wrong_captcha') {
            $message = '⚠️ The math problem answer was incorrect. Please try again.';
          } else if ($error == 'no_email') {
            $message = '⚠️ No admin account was found with that email address.';
          }
          echo '<div class="error-notification">' . $message . '</div>';
        }
      ?>

      <!-- This form now points to our new backend script -->
      <form id="forgotPasswordForm" method="POST" action="process_admin_forgot.php" novalidate>
        
        <div class="form-group">
          <label for="email">Admin Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        
        <!-- Math problem "captcha" -->
        <div class="captcha-box">
          <label for="captcha_input">What is <?php echo $num1; ?> + <?php echo $num2; ?> ?</label>
          <input type="text" id="captcha_input" name="captcha_input" required />
        </div>

        <button type="submit" class="auth-btn">Verify Account</button>
      </form>

      <p class="bottom-link">
        Remembered your password? <a href="admin_login.php">Back to Sign in</a>
      </p>
    </div>

  </div>
</body>
</html>

