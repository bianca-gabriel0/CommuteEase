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
        
        <!-- Password Field -->
        <div class="form-group password-container">
          <input type="password" id="password" name="password" placeholder="Password" required>
          <svg id="eyeIcon" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 5C4.4 5 1 12 1 12s3.4 7 11 7 11-7 11-7-3.4-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
            <circle cx="12" cy="12" r="2.5"/>
          </svg>
          <small class="error-message" id="passwordError"></small>
        </div>
        
        <!-- NEW: Confirm Password Field -->
        <div class="form-group password-container">
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
          <!-- ADDED: SVG icon for the confirm password field -->
          <svg id="eyeIconConfirm" class="toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 5C4.4 5 1 12 1 12s3.4 7 11 7 11-7 11-7-3.4-7-11-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
            <circle cx="12" cy="12" r="2.5"/>
          </svg>
          <small class="error-message" id="confirmPasswordError"></small>
        </div>
        
        <button type="submit" class="btn" id="submitBtn" disabled>Create Account</button>
      </form>

      <p class="signin-text">Already have an account? <a href="login.php" id="signInLink">Sign in</a></p>
    </div>
  </div>

  <script>
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    // NEW: Confirm Password elements
    const confirmPassword = document.getElementById("confirmPassword");
    const confirmPasswordError = document.getElementById("confirmPasswordError");
    
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const eyeIcon = document.getElementById("eyeIcon");
    // ADDED: Get the new eye icon element
    const eyeIconConfirm = document.getElementById("eyeIconConfirm");
    const submitBtn = document.getElementById("submitBtn");
    
    // Tracks whether the field has been focused/interacted with
    let emailTouched = false;
    let passwordTouched = false;
    let confirmPasswordTouched = false; // NEW: Touched status for confirm field

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
      
      // Rule 2: At least one digit
      else if (value.length >= 8 && !/\d/.test(value)) {
        message = "Password must contain at least one number.";
        isValid = false;
      } else if (value === "") {
          isValid = false; 
      }

      displayError(passwordError, message, isValid, isTouched);
      password.classList.toggle("invalid", !isValid && value !== "");
      
      // Return true only if password meets all rules
      return isValid && value.length >= 8 && /\d/.test(value);
    }
    
    // NEW: Validation function for Confirm Password
    function validateConfirmPassword(isTouched) {
      const value = confirmPassword.value.trim();
      let isValid = true;
      let message = "";

      // Rule 3: Must match the main password field
      if (value !== password.value.trim() && password.value.trim().length > 0) {
        message = "Passwords do not match.";
        isValid = false;
      } else if (value.length === 0 && password.value.trim().length > 0) {
          message = "Please confirm your password.";
          isValid = false;
      } else if (value.length === 0) {
          isValid = false; // Just mark as invalid if empty
      }

      displayError(confirmPasswordError, message, isValid, isTouched);
      confirmPassword.classList.toggle("invalid", !isValid && value !== ""); 
      
      // Return true only if passwords match AND the main password is valid
      return isValid && validatePassword(false); // Check main password validity (don't display errors)
    }


    function toggleSubmit() {
        // Run validation using the 'touched' status to control error display
        const isEmailValid = validateEmail(emailTouched);
        const isPasswordStrong = validatePassword(passwordTouched);
        // NEW: Check if confirm password matches and is valid
        const isPasswordMatch = validateConfirmPassword(confirmPasswordTouched); 
        
        // Only enable button if ALL three checks are valid
        submitBtn.disabled = !(isEmailValid && isPasswordStrong && isPasswordMatch);
    }

    // --- Event Listeners for Eye Icons ---
    eyeIcon.addEventListener("click", () => {
      const show = password.type === "password";
      password.type = show ? "text" : "password";
      eyeIcon.style.opacity = show ? "0.6" : "1";
    });

    // ADDED: Event listener for the confirm password eye icon
    eyeIconConfirm.addEventListener("click", () => {
      const show = confirmPassword.type === "password";
      confirmPassword.type = show ? "text" : "password";
      eyeIconConfirm.style.opacity = show ? "0.6" : "1";
    });

    // --- Event Listeners for Validation ---
    
    // 1. Initial touch on blur: Show errors for the first time
    email.addEventListener("blur", () => { emailTouched = true; toggleSubmit(); });
    password.addEventListener("blur", () => { passwordTouched = true; toggleSubmit(); });
    confirmPassword.addEventListener("blur", () => { confirmPasswordTouched = true; toggleSubmit(); }); // NEW

    // 2. Input (user typing): Recalculate button status and update error messages if already touched
    email.addEventListener("input", toggleSubmit);
    password.addEventListener("input", toggleSubmit);
    confirmPassword.addEventListener("input", toggleSubmit); // NEW

    // 3. Ensure the submit button state is calculated when form loads
    document.addEventListener("DOMContentLoaded", toggleSubmit); 
    
  </script>
</body>
</html>

