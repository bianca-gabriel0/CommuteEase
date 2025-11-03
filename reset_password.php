<?php
session_start();

$token = $_GET['token'] ?? null;

$is_valid_token = $token &&
                  isset($_SESSION['reset_token']) &&
                  $_SESSION['reset_token'] == $token &&
                  isset($_SESSION['reset_token_expiry']) &&
                  time() < $_SESSION['reset_token_expiry'];

if (!$is_valid_token) {
    die("This password reset link is invalid or has expired. Please <a href='forgotpassword.php'>try again</a>.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Your Password</title>
  <link rel="stylesheet" href="forgotpassword.css"/>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    .password-wrapper {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        top: 50%;
        right: 15px; 
        transform: translateY(-50%);
        cursor: pointer;
        color: #888; 
    }

    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] {
        padding-right: 40px; 
        width: 100%; 
        box-sizing: border-box; 
    }
  </style>

</head>
<body>
  <div class="container">
    <div class="left">
      <img src="assets/CE-logo.png" alt="Bus Image">
    </div>
    <div class="right">
      <h2>Set a New Password</h2>
      <p class="subtitle">Enter and confirm your new password.</p>
      
      <form action="update_password.php" method="POST" id="resetForm" novalidate>
        
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        
        <div class="form-group">
          <label for="new_password">New Password</label>
          <div class="password-wrapper">
            <input type="password" id="new_password" name="new_password" required>
            <i class="fas fa-eye toggle-password"></i>
          </div>
          <small class="error-message" id="passwordError" style="display: none;"></small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <div class="password-wrapper">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <i class="fas fa-eye toggle-password"></i>
          </div>
          <small class="error-message" id="confirmError" style="display: none;"></small>
        </div>

        <button type="submit" class="btn" id="submitBtn" disabled>Update Password</button>
      </form>
    </div>
  </div>

<script>
  const newPassword = document.getElementById("new_password");
  const confirmPassword = document.getElementById("confirm_password");
  const passwordError = document.getElementById("passwordError");
  const confirmError = document.getElementById("confirmError");
  const submitBtn = document.getElementById("submitBtn");

  function validatePasswords() {
    let isPasswordValid = false;
    let isConfirmValid = false;

    const passVal = newPassword.value;
    const confirmVal = confirmPassword.value;

    if (passVal.length > 0 && passVal.length < 8) {
      passwordError.textContent = "Password must be at least 8 characters.";
      passwordError.style.display = "block";
    } else if (passVal.length >= 8 && !/\d/.test(passVal)) {
      passwordError.textContent = "Password must contain at least one number.";
      passwordError.style.display = "block";
    } else if (passVal.length >= 8) {
      passwordError.style.display = "none";
      isPasswordValid = true;
    } else {
        passwordError.style.display = "none";
    }

    if (confirmVal.length > 0 && passVal !== confirmVal) {
      confirmError.textContent = "Passwords do not match.";
      confirmError.style.display = "block";
    } else if (passVal === confirmVal && isPasswordValid) {
      confirmError.style.display = "none";
      isConfirmValid = true;
    } else {
        confirmError.style.display = "none";
    }

    submitBtn.disabled = !(isPasswordValid && isConfirmValid);
  }

  newPassword.addEventListener("input", validatePasswords);
  confirmPassword.addEventListener("input", validatePasswords);

  // --- NEW: Password Toggle Logic ---

  const passwordTogglers = document.querySelectorAll(".toggle-password");

  passwordTogglers.forEach(toggler => {
      toggler.addEventListener("click", function() {
          // 'this' is the icon that was clicked
          // '.previousElementSibling' gets the <input> right before it
          const passwordInput = this.previousElementSibling; 

          // Check the current type of the input
          if (passwordInput.type === "password") {
              // Change it to text
              passwordInput.type = "text";
              // Change the icon to the "slashed" eye
              this.classList.remove("fa-eye");
              this.classList.add("fa-eye-slash");
          } else {
              // Change it back to password
              passwordInput.type = "password";
              // Change the icon back to the regular eye
              this.classList.remove("fa-eye-slash");
              this.classList.add("fa-eye");
          }
      });
  });

</script>
</body>
</html>