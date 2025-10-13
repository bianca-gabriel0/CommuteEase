const express = require('express');
const cors = require('cors');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;
// Middleware
app.use(cors()); // Allows frontend to fetch from backend (needed for browser security)
app.use(express.json()); // Parses JSON bodies (if you add POST routes later)
app.use(express.static(path.join(__dirname))); // Serves static files like CSS, images, HTML
// Sample schedule data (in a real app, this could come from a database like MongoDB)
const schedules = [
  { Location: "Victory Liner", type: "Bus", route: "Dagupan → Manaoag", departure: "5:30 AM", arrival: "6:30 AM", frequency: "Every 30 minutes" },
  { Location: "Assada Center", type: "Bus", route: "Dagupan → Lingayen", departure: "6:00 AM", arrival: "7:20 AM", frequency: "Every 15 minutes" },
  { Location: "SM Dagupan", type: "Mini Bus", route: "Dagupan → San Fabian", departure: "8:00 AM", arrival: "9:00 AM", frequency: "Every 20 minutes" },
  { Location: "Solid North", type: "Bus", route: "Dagupan → Urdaneta", departure: "10:00 AM", arrival: "12:00 PM", frequency: "Every 30 minutes" },
  { Location: "SM Dagupan", type: "Mini Bus", route: "Dagupan → Malasiqui", departure: "9:00 AM", arrival: "10:00 PM", frequency: "Every 15 minutes" }
];
// API Route: Fetch all schedules
app.get('/api/schedules', (req, res) => {
  res.json(schedules);
});
// Serve the HTML file at root
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});