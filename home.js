// server.js
const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static frontend files
app.use(express.static(path.join(__dirname, 'public')));

// Example API route — you can add more later
app.get('/api/schedules', (req, res) => {
  const sampleSchedules = [
    { route: "Dagupan - Calasiao", time: "7:00 AM" },
    { route: "Dagupan - Mangaldan", time: "7:15 AM" },
  ];
  res.json(sampleSchedules);
});

// Example POST route (for saving user account info, etc.)
app.post('/api/account', (req, res) => {
  const { username, email } = req.body;
  console.log(`Received: ${username}, ${email}`);
  res.json({ message: "Account info received!" });
});

// Fallback to serve Home.html for unknown routes
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'Home.html'));
});

// Start server
app.listen(PORT, () => {
  console.log(`🚍 CommuteEase server running at http://localhost:${PORT}`);
});
