// server.js
const express = require("express");
const cors = require("cors");
const path = require("path");

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static frontend files
app.use(express.static(path.join(__dirname, "public")));

// Example in-memory user data
const users = [
  { email: "ken@example.com", password: "123456" },
  { email: "user@gmail.com", password: "password" }
];

// Handle forgot password POST request
app.post("/api/forgot-password", (req, res) => {
  const { email } = req.body;
  const user = users.find(u => u.email === email);

  if (!user) {
    return res.status(404).json({ message: "Email not found!" });
  }

  console.log(`📨 Password reset link sent to: ${email}`);
  return res.json({ message: "Password reset link has been sent to your email!" });
});

// Fallback to serve the forgotpassword page
app.get("*", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "forgotpassword.html"));
});

// Start the server
app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});
