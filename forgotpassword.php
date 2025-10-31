<?php
// We MUST start the session to store the captcha answer
session_start();

// Generate two random numbers for the math problem
$num1 = rand(1, 9);
$num2 = rand(1, 9);

// Store the correct answer in the session
$_SESSION['captcha_answer'] = $num1 + $num2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="forgotpassword.css">
  <style>
    /* Added styles for the new elements */
    .captcha-box {
      font-size: 1.1em;
      font-weight: bold;
      color: #333;
      margin-top: 20px;
      margin-bottom: 20px;
      text-align: left;
    }
    .captcha-box label {
        display: block;
        margin-bottom: 10px;
    }
    .captcha-box input {
      width: 80px;
      padding: 10px;
      font-size: 1.1em;
      text-align: center;
    }
    .error-notification {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 12px 15px;
      border-radius: 5px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="assets/CE-logo.png" alt="Bus Image">
    </div>

    <div class="right">
      <h2>Forgot your password?</h2>
      <p class="subtitle">Enter your email and solve the problem to reset it.</p>

      <!-- Check for error messages from the verification script -->
      <?php
        if (isset($_GET['error'])) {
          $error = $_GET['error'];
          $message = 'An unknown error occurred.';
          if ($error == 'wrong_captcha') {
            $message = '⚠️ The math problem answer was incorrect. Please try again.';
          } else if ($error == 'no_email') {
            $message = '⚠️ No account was found with that email address.';
          }
          echo '<div class="error-notification">' . $message . '</div>';
        }
      ?>

      <!-- This form now points to our new backend script -->
      <form id="forgotPasswordForm" method="POST" action="verify_reset.php" novalidate>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Email" required>
          <small class="error-message" id="emailError"></small>
        </div>
        
        <!-- NEW: Math problem "captcha" -->
        <div class="captcha-box">
          <label for="captcha_input">Human Check: What is <?php echo $num1; ?> + <?php echo $num2; ?> ?</label>
          <input type="text" id="captcha_input" name="captcha_input" required />
        </div>

        <button type="submit" class="btn">Confirm</button>
      </form>

      <a href="login.php" class="back-link" id="backToSignIn">&lt; Back to Sign in</a>
    </div>
  </div>

  <script>
    // We only need simple client-side validation now
    const form = document.getElementById("forgotPasswordForm");
    const emailInput = document.getElementById("email");
    const emailError = document.getElementById("emailError");
    const backToSignIn = document.getElementById("backToSignIn");

    form.addEventListener("submit", (e) => {
      const emailValue = emailInput.value.trim();
      const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;

      if (!emailPattern.test(emailValue)) {
        e.preventDefault(); // Stop the form submission
        emailError.textContent = "Please enter a valid email address.";
        emailError.style.color = "red";
      } else {
        emailError.textContent = "";
      }
    });

    emailInput.addEventListener("input", () => {
      emailError.textContent = "";
    });

    backToSignIn.addEventListener("click", (e) => {
      e.preventDefault();
      window.location.href = "login.php";
    });
  </script>
</body>
</html>

