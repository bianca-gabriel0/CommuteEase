// server.js
const express = require("express");
const cors = require("cors");
const path = require("path");

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));

// Dummy commuter data
let commuters = [
  { id: 1, name: "Juan Dela Cruz", email: "juan@email.com", password: "password123" },
  { id: 2, name: "Maria Santos", email: "maria@email.com", password: "mypass" }
];

// --- Routes ---

// Get all commuters
app.get("/api/commuters", (req, res) => {
  res.json(commuters);
});

// Delete a commuter by ID
app.delete("/api/commuters/:id", (req, res) => {
  const id = parseInt(req.params.id);
  commuters = commuters.filter(u => u.id !== id);
  res.json({ message: "Commuter deleted", id });
});

// Serve the admin page
app.get("*", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "commuters.html"));
});

app.listen(PORT, () => {
  console.log(`🚌 CommuteEase Admin backend running at http://localhost:${PORT}`);
});
