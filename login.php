<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link rel="stylesheet" href="login.css"/>
  

</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <?php session_start(); ?> 
      <a href="Home.php" class="back-link">&lt; Return to Home</a>

      <div class="login-content">
        <div class="bus-image">
          <img src="assets/CE-logo.png" alt="Bus" />
        </div>

        <div class="login-form">
          <h2>Welcome!</h2>

          <?php
            if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
              echo '<div class="error-notification">
                      ⚠️ Incorrect email or password. Please try again.
                    </div>';
            }
            
            if (isset($_GET['message']) && $_GET['message'] == 'logged_out') {
              echo '<div class="success-notification">
                      ✅ You have been logged out successfully.
                    </div>';
            }
          ?>
          <form id="loginForm" novalidate action="process_login.php" method="POST">
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required />
            <small class="error-message" id="emailError"></small>
            
            <label for="password">Password</label>
            <div class="password-container">
              <input type="password" id="password" name="password" placeholder="Enter your password" required minlength="6" />
              <svg id="eyeIcon" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
                <circle cx="12" cy="12" r="2.5"/>
              </svg>
            </div>
            <small class="error-message" id="passwordError"></small>

            <div class="forgot-password">
              <a href="forgotpassword" id="forgotLink">Forgot password?</a>
            </div>

            <div class="loader" id="loader"></div>
            <button type="submit" id="submitBtn" disabled>Sign In</button>
          </form>

          <p class="signup-text">
            Don't have an account? <a href="signup" id="signupLink">Sign up</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <script>
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const submitBtn = document.getElementById("submitBtn");
    const eyeIcon = document.getElementById("eyeIcon");
    const loader = document.getElementById("loader");
    const signupLink = document.getElementById("signupLink");
    const forgotLink = document.getElementById("forgotLink");

    function validateEmail() {
      const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;
      if (email.value.trim() === "") {
        emailError.textContent = "Email is required.";
        emailError.style.color = "#dc3545";
        email.classList.add("invalid");
        email.classList.remove("valid");
        return false;
      } else if (!pattern.test(email.value.trim())) {
        emailError.textContent = "Please enter a valid email address.";
        emailError.style.color = "#dc3545";
        email.classList.add("invalid");
        email.classList.remove("valid");
        return false;
      } else {
        emailError.textContent = "✅ Email looks good!";
        emailError.style.color = "#28a745";
        email.classList.add("valid");
        email.classList.remove("invalid");
        return true;
      }
    }

    function validatePassword() {
      if (password.value.trim() === "") {
        passwordError.textContent = "Password is required.";
        passwordError.style.color = "#dc3545";
        password.classList.add("invalid");
        password.classList.remove("valid");
        return false;
      } else if (password.value.length < 6) {
        passwordError.textContent = "Password must be at least 6 characters long.";
        passwordError.style.color = "#dc3545";
        password.classList.add("invalid");
        password.classList.remove("valid");
        return false;
      } else {
        passwordError.textContent = "✅ Strong password.";
        passwordError.style.color = "#28a745";
        password.classList.add("valid");
        password.classList.remove("invalid");
        return true;
      }
    }

    function toggleSubmit() {
      const emailValid = validateEmail();
      const passwordValid = validatePassword();
      submitBtn.disabled = !(emailValid && passwordValid);
    }

    eyeIcon.addEventListener("click", () => {
      const isHidden = password.type === "password";
      password.type = isHidden ? "text" : "password";
      eyeIcon.style.opacity = isHidden ? "0.6" : "1";
    });

    email.addEventListener("input", toggleSubmit);
    password.addEventListener("input", toggleSubmit);

    forgotLink.addEventListener("click", (e) => {
      e.preventDefault();
      alert("Redirecting to Forgot Password page...");
      window.location.href = "forgotpassword.php";
    });

    signupLink.addEventListener("click", (e) => {
      e.preventDefault();
      alert("Redirecting to Sign Up page...");
      window.location.href = "signup.php";
    });
    
  </script>
</body>
</html>