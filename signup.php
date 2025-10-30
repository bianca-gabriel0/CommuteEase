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

      <form id="createAccountForm" method="POST" action="process_signup.php" novalidate>
        <div class="form-group">
          <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
        </div>
        <div class="form-group">
          <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
        </div>
        <div class="form-group">
          <input type="email" id="email" name="email" placeholder="Email" required>
          <small class="error-message" id="emailError"></small>
        </div>
        <div class="form-group password-container">
          <input type="password" id="password" name="password" placeholder="Password" required>
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
    
    // Tracks whether the field has been focused/interacted with
    let emailTouched = false;
    let passwordTouched = false;

    // Helper function to display/hide the error element
    function displayError(errorElement, message, isValid, isTouched) {
        if (isTouched && !isValid) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        } else {
            errorElement.style.display = 'none';
        }
    }

    function validateEmail(isTouched) {
      const value = email.value.trim();
      const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;
      let isValid = true;
      let message = "";

      if (value === "") {
          message = "Email is required.";
          isValid = false;
      } else if (!pattern.test(value)) {
        message = "Enter a valid email.";
        isValid = false;
      }

      displayError(emailError, message, isValid, isTouched);
      // Toggle invalid class for styling, but don't mark empty fields as invalid unless we check them
      email.classList.toggle("invalid", !isValid && value !== ""); 
      return isValid;
    }

    function validatePassword(isTouched) {
      const value = password.value.trim();
      let isValid = true;
      let message = "";
      
      // Rule 1: Minimum 8 characters
      if (value.length > 0 && value.length < 8) {
        message = "Password must be at least 8 characters.";
        isValid = false;
      } 
      
      // Rule 2: At least one digit (only check if length passes or is max length)
      else if (value.length >= 8 && !/\d/.test(value)) {
        message = "Password must contain at least one number.";
        isValid = false;
      } else if (value === "") {
          // If the field is empty, it's technically invalid, but we only show the error if touched
          isValid = false; 
      }

      // If touched, display error message if invalid
      displayError(passwordError, message, isValid, isTouched);
      password.classList.toggle("invalid", !isValid && value !== "");
      
      // We only return true if the password meets all rules AND is not empty
      return isValid && value.length >= 8 && /\d/.test(value);
    }

    function toggleSubmit() {
        // Run validation using the 'touched' status to control error display
        const isEmailValid = validateEmail(emailTouched);
        const isPasswordValid = validatePassword(passwordTouched);
        
        // Only enable button if BOTH are valid
        submitBtn.disabled = !(isEmailValid && isPasswordValid);
    }

    eyeIcon.addEventListener("click", () => {
      const show = password.type === "password";
      password.type = show ? "text" : "password";
      eyeIcon.style.opacity = show ? "0.6" : "1";
    });

    // 1. Initial touch on blur (user clicks away): Show errors for the first time
    email.addEventListener("blur", () => {
        emailTouched = true;
        toggleSubmit(); 
    });
    password.addEventListener("blur", () => {
        passwordTouched = true;
        toggleSubmit(); 
    });

    // 2. Input (user typing): Recalculate button status and update error messages if already touched
    email.addEventListener("input", toggleSubmit);
    password.addEventListener("input", toggleSubmit);

    // 3. Ensure the submit button state is calculated when form loads
    document.addEventListener("DOMContentLoaded", () => {
        // Run toggleSubmit once to correctly disable the button on load (without showing errors)
        toggleSubmit(); 
    });
  </script>
</body>
</html>
