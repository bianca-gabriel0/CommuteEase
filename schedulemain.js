const express = require('express');

const sqlite3 = require('sqlite3').verbose();

const bodyParser = require('body-parser');

const cors = require('cors');

const app = express();

const port = 3000; // Or whatever port

app.use(cors());

app.use(bodyParser.json());

// Connect to SQLite database

const db = new sqlite3.Database('./schedules.db', (err) => {

if (err) {

console.error(err.message);
}

console.log('Connected to the schedules database.');

});

// Create table if not exists

db.run(`CREATE TABLE IF NOT EXISTS schedules (

id INTEGER PRIMARY KEY AUTOINCREMENT,

day TEXT,

type TEXT,

location TEXT,

route TEXT,

departure TEXT, // Store as 24-hour string

arrival TEXT, // Store as 24-hour string

frequency TEXT

)`);

// GET /schedules?day=Monday

app.get('/schedules', (req, res) => {

const day = req.query.day; // e.g., 'Monday' or 'All'

let sql = 'SELECT * FROM schedules';

let params = [];

if (day && day !== 'All') {

sql += ' WHERE day = ?';
params.push(day);
}

db.all(sql, params, (err, rows) => {

if (err) {
  res.status(400).json({ error: err.message });
  return;
}
res.json(rows);
});

});

// POST /schedules

app.post('/schedules', (req, res) => {

const { day, type, location, route, departure, arrival, frequency } = req.body;

// Note: Frontend sends departure and arrival in 24-hour format

const sql = `INSERT INTO schedules (day, type, location, route, departure, arrival, frequency)

           VALUES (?, ?, ?, ?, ?, ?, ?)`;
db.run(sql, [day, type, location, route, departure, arrival, frequency], function(err) {

if (err) {
  res.status(400).json({ error: err.message });
  return;
}
res.json({ id: this.lastID });
});

});

app.listen(port, () => {

console.log(`Server running on port ${port}`);

});