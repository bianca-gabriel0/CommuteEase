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
      <p class="subtitle">Enter your email to reset it</p>

      <form id="forgotPasswordForm" novalidate>
        <div class="form-group">
          <input type="email" id="email" placeholder="Email" required>
          <small class="error-message" id="emailError"></small>
        </div>
        <button type="submit" class="btn">Confirm</button>
      </form>

      <a href="login" class="back-link" id="backToSignIn">&lt; Back to Sign in</a>
    </div>
  </div>

  <script>
    const form = document.getElementById("forgotPasswordForm");
    const emailInput = document.getElementById("email");
    const emailError = document.getElementById("emailError");
    const backToSignIn = document.getElementById("backToSignIn");

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const emailValue = emailInput.value.trim();
      const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;

      if (!emailPattern.test(emailValue)) {
        emailError.textContent = "Please enter a valid email address.";
        emailError.style.color = "red";
        emailInput.style.borderColor = "red";
      } else {
        emailError.textContent = "";
        emailInput.style.borderColor = "#58a193";
        alert("A password reset link has been sent to your email!");
        form.reset();
      }
    });

    emailInput.addEventListener("input", () => {
      emailError.textContent = "";
      emailInput.style.borderColor = "#ddd";
    });

    backToSignIn.addEventListener("click", (e) => {
      e.preventDefault();
      window.location.href = "login.php"; // change this to your actual sign-in page
    });
  </script>
</body>
</html>
