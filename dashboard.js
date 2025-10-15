const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const session = require('express-session');
const dotenv = require('dotenv');

dotenv.config();  // Load environment variables

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors({ origin: 'http://localhost:5500', credentials: true }));  // Adjust origin to match your frontend server (e.g., if served via live server)
app.use(express.json());  // Parse JSON bodies
app.use(session({
  secret: process.env.SESSION_SECRET,
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false }  // Set to true if using HTTPS
}));

// Connect to MongoDB
mongoose.connect(process.env.MONGODB_URI, { useNewUrlParser: true, useUnifiedTopology: true })
  .then(() => console.log('Connected to MongoDB'))
  .catch(err => console.error('MongoDB connection error:', err));

// Define User Schema and Model
const userSchema = new mongoose.Schema({
  username: String,
  email: String,
});

const User = mongoose.model('User', userSchema);

// Define Vehicle Schema and Model (for vehicle summary)
const vehicleSchema = new mongoose.Schema({
  status: String,  // e.g., 'active', 'ongoing'
});

const Vehicle = mongoose.model('Vehicle', vehicleSchema);

// Seed sample data (run once or on startup for testing)
async function seedData() {
  await User.deleteMany({});  // Clear existing users
  await Vehicle.deleteMany({});  // Clear existing vehicles

  // Insert sample users
  await User.insertMany([
    { username: 'John Doe', email: 'john@example.com' },
    { username: 'Jane Smith', email: 'jane@example.com' },
  ]);

  // Insert sample vehicles
  await Vehicle.insertMany([
    { status: 'active' },
    { status: 'ongoing' },
    { status: 'active' },  // Total: 3, Active: 2, Ongoing: 1
  ]);
}

seedData();  // Uncomment this if you want to seed data on server start

// API Routes

// 1. Get all users (for the users table)
app.get('/api/users', async (req, res) => {
  try {
    const users = await User.find();
    res.json(users);
  } catch (err) {
    res.status(500).json({ message: 'Error fetching users', error: err });
  }
});

// 2. Get statistics (for charts and stats on the dashboard)
app.get('/api/stats', async (req, res) => {
  try {
    const newUsersCount = await User.countDocuments({});  // In a real app, filter for new users
    const clientsCount = 64;  // Placeholder; replace with actual logic
    const totalVehicles = await Vehicle.countDocuments({});
    const activeVehicles = await Vehicle.countDocuments({ status: 'active' });
    const ongoingVehicles = await Vehicle.countDocuments({ status: 'ongoing' });

    res.json({
      newUsers: newUsersCount,
      clients: clientsCount,
      totalVehicles,
      activeVehicles,
      ongoingVehicles,
    });
  } catch (err) {
    res.status(500).json({ message: 'Error fetching stats', error: err });
  }
});

// 3. Logout route (destroys the session)
app.post('/api/logout', (req, res) => {
  req.session.destroy(err => {
    if (err) {
      return res.status(500).json({ message: 'Error logging out' });
    }
    res.json({ message: 'Logged out successfully' });
  });
});

// Start the server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});