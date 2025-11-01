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
  <title>Forgot Password</title>
  <link rel="stylesheet" href="forgotpassword.css">

</head>
<body>
  <div class="container">
    <div class="left">
      <img src="assets/CE-logo.png" alt="Bus Image">
    </div>

    <div class="right">
      <h2>Forgot your password?</h2>
      <p class="subtitle">Enter your email and solve the problem to reset it.</p>

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

      <form id="forgotPasswordForm" method="POST" action="verify_reset.php" novalidate>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Email" required>
          <small class="error-message" id="emailError"></small>
        </div>
        
        <div class="captcha-box">
          <label for="captcha_input">CAPTCHA: What is <?php echo $num1; ?> + <?php echo $num2; ?> ?</label>
          <input type="text" id="captcha_input" name="captcha_input" required />
        </div>

        <button type="submit" class="btn">Confirm</button>
      </form>

      <a href="login.php" class="back-link" id="backToSignIn">&lt; Back to Sign in</a>
    </div>
  </div>

  <script>
    const form = document.getElementById("forgotPasswordForm");
    const emailInput = document.getElementById("email");
    const emailError = document.getElementById("emailError");
    const backToSignIn = document.getElementById("backToSignIn");

    form.addEventListener("submit", (e) => {
      const emailValue = emailInput.value.trim();
      const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;

      if (!emailPattern.test(emailValue)) {
        e.preventDefault(); 
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

