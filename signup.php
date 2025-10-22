<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account</title>
  <link rel="stylesheet" href="signup.css">
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="assets/CE-logo.png" alt="Bus Image">
    </div>

    <div class="right">
      <a href="Home" class="back-link" id="backHome">&lt; Return to Home</a>
      <h2>Create Account</h2>
      <form id="createAccountForm" novalidate>
        <div class="form-group">
          <input type="text" id="firstName" placeholder="First Name" required>
        </div>
        <div class="form-group">
          <input type="text" id="lastName" placeholder="Last Name" required>
        </div>
        <div class="form-group">
          <input type="email" id="email" placeholder="Email" required>
          <small class="error-message" id="emailError"></small>
        </div>
        <div class="form-group password-container">
          <input type="password" id="password" placeholder="Password" required minlength="6">
          <svg id="eyeIcon" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 5C4.4 5 1 12 1 12s3.4 7 11 7 11-7 11-7-3.4-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
            <circle cx="12" cy="12" r="2.5"/>
          </svg>
          <small class="error-message" id="passwordError"></small>
        </div>
        <button type="submit" class="btn" id="submitBtn" disabled>Create Account</button>
      </form>
      <p class="signin-text">Already have an account? <a href="login.php" id="signInLink">Sign in</a></p>
    </div>
  </div>

  <script>
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const eyeIcon = document.getElementById("eyeIcon");
    const submitBtn = document.getElementById("submitBtn");
    const backHome = document.getElementById("backHome");
    const signInLink = document.getElementById("signInLink");

    // Email validation
    function validateEmail() {
      const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;
      if (!pattern.test(email.value.trim())) {
        emailError.textContent = "Enter a valid email.";
        email.classList.add("invalid");
        return false;
      }
      emailError.textContent = "";
      email.classList.remove("invalid");
      return true;
    }

    // Password validation
    function validatePassword() {
      if (password.value.length < 6) {
        passwordError.textContent = "Password must be at least 6 characters.";
        password.classList.add("invalid");
        return false;
      }
      passwordError.textContent = "";
      password.classList.remove("invalid");
      return true;
    }

    // Enable button when inputs valid
    function toggleSubmit() {
      submitBtn.disabled = !(validateEmail() && validatePassword());
    }

    // Show/hide password
    eyeIcon.addEventListener("click", () => {
      const show = password.type === "password";
      password.type = show ? "text" : "password";
      eyeIcon.style.opacity = show ? "0.6" : "1";
    });

    // Live validation
    email.addEventListener("input", toggleSubmit);
    password.addEventListener("input", toggleSubmit);

    // Submit
    document.getElementById("createAccountForm").addEventListener("submit", (e) => {
      e.preventDefault();
      if (validateEmail() && validatePassword()) {
        alert("✅ Account Created Successfully!");
        window.location.href = "Home.php";
      } else {
        alert("⚠️ Please enter valid details.");
      }
    });

    // Back to Home
    backHome.addEventListener("click", (e) => {
      e.preventDefault();
      alert("🔙 Returning to Home Page...");
      window.location.href = "Home.php";
    });

    // Go to Sign In
    signInLink.addEventListener("click", (e) => {
      e.preventDefault();
      alert("🔐 Redirecting to Sign In...");
      window.location.href = "login.php";
    });
  </script>
</body>
</html>
