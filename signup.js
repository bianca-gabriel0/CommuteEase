// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function () {
  const firstName = document.getElementById("firstName");
  const lastName = document.getElementById("lastName");
  const email = document.getElementById("email");
  const password = document.getElementById("password");
  const firstNameError = document.getElementById("firstNameError");
  const lastNameError = document.getElementById("lastNameError");
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");
  const eyeIcon = document.getElementById("eyeIcon");
  const submitBtn = document.getElementById("submitBtn");
  const backHome = document.getElementById("backHome");
  const signInLink = document.getElementById("signInLink");
  const message = document.getElementById("message");
  const form = document.getElementById("signupForm");

  // Name validation (for both first and last)
  function validateNames() {
    const firstNameValid = firstName.value.trim().length > 0;
    const lastNameValid = lastName.value.trim().length > 0;

    if (!firstNameValid) {
      firstNameError.textContent = "First name is required.";
      firstName.classList.add("invalid");
    } else {
      firstNameError.textContent = "";
      firstName.classList.remove("invalid");
    }

    if (!lastNameValid) {
      lastNameError.textContent = "Last name is required.";
      lastName.classList.add("invalid");
    } else {
      lastNameError.textContent = "";
      lastName.classList.remove("invalid");
    }

    return firstNameValid && lastNameValid;
  }

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

  // Enable button when all inputs are valid
  function toggleSubmit() {
    const allValid = validateNames() && validateEmail() && validatePassword();
    submitBtn.disabled = !allValid;
  }

  // Show/hide password
  eyeIcon.addEventListener("click", () => {
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    eyeIcon.style.opacity = show ? "0.6" : "1";
  });

  // Live validation on input
  firstName.addEventListener("input", toggleSubmit);
  lastName.addEventListener("input", toggleSubmit);
  email.addEventListener("input", toggleSubmit);
  password.addEventListener("input", toggleSubmit);

  // Form submission with backend API call
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!validateNames() || !validateEmail() || !validatePassword()) {
      message.style.color = "red";
      message.textContent = "Please enter valid details.";
      return;
    }

    const name = `${firstName.value.trim()} ${lastName.value.trim()}`;
    const emailValue = email.value.trim();
    const passwordValue = password.value.trim();

    try {
      const res = await fetch('http://localhost:5000/api/auth/signup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email: emailValue, password: passwordValue })
      });

      const data = await res.json();
      if (res.ok) {
        message.style.color = 'green';
        message.textContent = data.message || 'Account created successfully!';
        setTimeout(() => window.location.href = 'login.php', 1500);
      } else {
        message.style.color = 'red';
        message.textContent = data.message || 'Signup failed';
      }
    } catch (err) {
      message.style.color = 'red';
      message.textContent = 'Server error. Please try again later.';
    }
  });

  // Back to Home (no alert, just redirect)
  backHome.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "Home.php";
  });

  // Go to Sign In (no alert, just redirect)
  signInLink.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "login.php";
  });
});
