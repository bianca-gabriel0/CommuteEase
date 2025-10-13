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

// Serve static files (HTML, CSS, JS)
app.use(express.static(path.join(__dirname, "public")));

// Example user data
const users = [
  { username: "John Doe", email: "john@example.com" },
  { username: "Jane Smith", email: "jane@example.com" },
  { username: "Alex Reyes", email: "alexr@gmail.com" },
  { username: "Karla Cruz", email: "karla_cruz@yahoo.com" },
  { username: "Miko Tan", email: "miko.tan@gmail.com" },
  { username: "Sara Lim", email: "sara.lim@yahoo.com" },
  { username: "Ken Ito", email: "ken.ito@gmail.com" }
];

// API route to fetch all users
app.get("/api/users", (req, res) => {
  res.json(users);
});

// Fallback to dashboard page
app.get("*", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "dashboard.html"));
});

// Start the server
app.listen(PORT, () => {
  console.log(`🚀 CommuteEase Admin backend running at http://localhost:${PORT}`);
});
