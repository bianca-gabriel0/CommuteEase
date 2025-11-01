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
          <input type="password" id="new_password" name="new_password" required>
          <small class="error-message" id="passwordError" style="display: none;"></small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
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
</script>
</body>
</html>

