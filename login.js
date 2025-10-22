// login.js
document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();
  const message = document.getElementById('message');

  try {
    const res = await fetch('http://localhost:5000/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });

    const data = await res.json();
    if (res.ok) {
      message.style.color = 'green';
      message.textContent = data.message;
      // Store user info (optional)
      localStorage.setItem('user', JSON.stringify(data.user));
      setTimeout(() => window.location.href = 'dashboard.php', 1000);
    } else {
      message.style.color = 'red';
      message.textContent = data.message || 'Login failed';
    }
  } catch (err) {
    message.style.color = 'red';
    message.textContent = 'Server error';
  }
});