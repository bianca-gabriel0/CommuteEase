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

// Serve static files
app.use(express.static(path.join(__dirname, "public")));

// Dummy user and schedule data
let user = {
  name: "Juan Dela Cruz",
  email: "example@gmail.com",
  profilePic: "profile.jpg"
};

let schedules = [
  {
    id: 1,
    location: "SM Mall",
    type: "Bus",
    route: "Dagupan – Manaoag",
    departure: "5:30 AM",
    arrival: "6:30 AM",
    frequency: "Every 30 minutes"
  },
  {
    id: 2,
    location: "SM Mall",
    type: "Jeepney",
    route: "Dagupan – Lingayen",
    departure: "6:00 AM",
    arrival: "7:20 AM",
    frequency: "Every 15 minutes"
  }
];

// Routes
app.get("/api/user", (req, res) => {
  res.json(user);
});

app.get("/api/schedules", (req, res) => {
  res.json(schedules);
});

app.delete("/api/schedules/:id", (req, res) => {
  const id = parseInt(req.params.id);
  schedules = schedules.filter(s => s.id !== id);
  res.json({ message: "Schedule deleted", id });
});

// Default route
app.get("*", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "accountinfo.php"));
});

// Start server
app.listen(PORT, () => {
  console.log(`🚍 CommuteEase backend running at http://localhost:${PORT}`);
});

