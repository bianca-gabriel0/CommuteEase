<?php
// --- Admin Security Check ---
// We MUST start the session to check for a valid admin
session_start();

// If the admin is not logged in (session not set), kick them to the login page
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commute Ease Admin - Schedule</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&family=Birthstone&family=Ephesis&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="scheduleadmin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    

</head>

<body>
    <div class="sidebar">
        <img src="assets/CE-logo.png" alt="" class="signup-image">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        <!-- Set this link to active -->
        <a href="scheduleadmin.php" class="active"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
        <a href="view_users_admin.php"><i class="fa-solid fa-user-gear"></i> View Users</a>
        <button class="logout-button" onclick="logout()">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
        </button>
    </div>

    <div class="main-content">
        <div class="card2">
            <h2>Schedules</h2>

            <!-- ✅ Day Filter Buttons -->
            <div class="days-row">
                <button onclick="filterByDay('All')">All</button>
                <button onclick="filterByDay('Sunday')">Sunday</button>
                <button onclick="filterByDay('Monday')">Monday</button>
                <button onclick="filterByDay('Tuesday')">Tuesday</button>
                <button onclick="filterByDay('Wednesday')">Wednesday</button>
                <button onclick="filterByDay('Thursday')">Thursday</button>
                <button onclick="filterByDay('Friday')">Friday</button>
                <button onclick="filterByDay('Saturday')">Saturday</button>
                <button class="add-schedule" onclick="openAddModal()">Add Schedule</button>
                <a href="export.php" target="_blank" class="export-button"> Export</a>
            </div>

            <!-- ✅ Schedule Table -->
            <table id="scheduleTable" class="schedule-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Location</th>
                        <th>Type/s</th>
                        <th>Route / Destination</th>
                        <th>Departure Time</th>
                        <th>Estimated Arrival</th>
                        <th>Frequency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- UPDATED: Added pagination buttons AND page indicator -->
            <div class="table-arrows">
                <button id="prevPageBtn" class="arrow-btn">&lt;</button>
                <!-- NEW: Page indicator -->
                <span id="pageIndicator" class="page-indicator">1 of 1</span>
                <button id="nextPageBtn" class="arrow-btn">&gt;</button>
            </div>

        </div>
    </div>

    <!-- ✅ Add/Edit Schedule Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add a New Schedule</h2>

            <input type="hidden" id="schedule_id">

            <label for="day">Day:</label>
            <select id="day">
                <option>Monday</option>
                <option>Tuesday</option>
                <option>Wednesday</option>
                <option>Thursday</option>
                <option>Friday</option>
                <option>Saturday</option>
                <option>Sunday</option>
            </select>

            <label for="type">Type:</label>
            <select id="type">
                <option>Bus</option>
                <option>Mini-bus</option>
            </select>

            <label for="location">Location (Terminal):</label>
            <input type="text" id="location">

            <label for="route">Route / Destination:</label>
            <div>Dagupan → <input type="text" id="route" placeholder="Enter destination"></div>

            <label for="time">Departure Time:</label>
            <input type="time" id="time">

            <label for="arrival">Estimated Arrival:</label>
            <input type="time" id="arrival">

            <label for="frequency">Frequency:</label>
            <select id="frequency">
                <option>Every 10 minutes</option>
                <option>Every 15 minutes</option>
                <option>Every 20 minutes</option>
                <option>Every 25 minutes</option>
                <option>Every 30 minutes</option>
                <option>Every 35 minutes</option>
                <option>Every 40 minutes</option>
            </select>
            
            <!-- 🆕 NEW BUTTON CONTAINER -->
            <div class="modal-actions">
                <button id="modalButton" onclick="saveSchedule()">Add Schedule</button>
                <!-- 🆕 TRASH BUTTON (Only visible during edit) -->
                <button id="deleteButton" class="delete-button" onclick="deleteSchedule()" style="display: none;">
                    <i class="fa-solid fa-trash-alt"></i> Delete
                </button>
            </div>
            
        </div>
    </div>

    <script>
        // UPDATED: Renamed 'schedules' to 'allSchedules'
        let allSchedules = [];
        // UPDATED: Added new variables for pagination
        let currentView = []; 
        let currentPage = 1; 
        const rowsPerPage = 7; // As you requested
        
        // UPDATED: Get button elements
        const prevPageBtn = document.getElementById("prevPageBtn");
        const nextPageBtn = document.getElementById("nextPageBtn");
        // NEW: Get page indicator element
        const pageIndicator = document.getElementById("pageIndicator");

        // 🟢 Fetch from PHP (MySQL)
        async function loadSchedules() {
            // IMPORTANT: Your fetch_schedules.php MUST now include "WHERE is_deleted = FALSE" 
            // in its SQL query to only show active schedules.
            const res = await fetch("php/fetch_schedules.php");
            if (!res.ok) {
                console.error("Failed to fetch schedules:", res.status, res.statusText);
                return;
            }
            try {
                allSchedules = await res.json();
                currentView = allSchedules; // Set the view to all schedules
                currentPage = 1; // Reset to page 1
                updateDisplay(); // UPDATED: Call the new master display function
            } catch (e) {
                console.error("Could not parse JSON from fetch_schedules.php:", e);
            }
        }

        // UPDATED: Renamed from 'displaySchedules' to 'renderTable'
        // Its only job is to render the rows it's given
        function renderTable(list) {
            const tableBody = document.querySelector("#scheduleTable tbody");
            tableBody.innerHTML = "";

            // NEW: Show a message if the list is empty
            if (list.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 20px;">No schedules found for this day.</td></tr>`;
                return;
            }
            
            list.forEach(s => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${s.day}</td>
                    <td>${s.location}</td>
                    <td>${s.type}</td>
                    <td>${s.route_formatted}</td>
                    <td>${s.departure_formatted}</td>
                    <td>${s.arrival_formatted}</td>
                    <td>${s.frequency}</td>
                    <td class="actions-cell">
                        <button class="action-button edit" onclick="openEditModal(${s.schedule_id})">
                            <i class="fa-solid fa-pencil"></i> Edit
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // UPDATED: New master display function (copied from schedule-main.php)
        function updateDisplay() {
            // Calculate the "slice" of data we need for the current page
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const pageItems = currentView.slice(startIndex, endIndex);

            // Render just that slice
            renderTable(pageItems);

            // Update the next/prev button states
            prevPageBtn.disabled = (currentPage === 1);
            nextPageBtn.disabled = (endIndex >= currentView.length);

            // --- NEW: Update page indicator text ---
            const maxPage = Math.ceil(currentView.length / rowsPerPage) || 1; // || 1 to prevent "0 of 0" if empty
            pageIndicator.textContent = `${currentPage} of ${maxPage}`;
            // --- END NEW ---
        }

        // 🟢 Filter by day
        // UPDATED: This function now filters 'currentView' and calls 'updateDisplay'
        function filterByDay(day) {
            if (day === "All") {
                currentView = allSchedules;
            } else {
                currentView = allSchedules.filter(s => s.day === day);
            }
            currentPage = 1; // Reset to page 1
            updateDisplay(); // Re-render the table
        }

        // 🟢 Modal controls
        
        function openAddModal() {
            // Clear the form for a new entry
            document.getElementById("schedule_id").value = "";
            document.getElementById("day").value = "Monday";
            document.getElementById("type").value = "Bus";
            document.getElementById("location").value = "";
            document.getElementById("route").value = "";
            document.getElementById("time").value = "";
            document.getElementById("arrival").value = "";
            document.getElementById("frequency").value = "Every 10 minutes";
            
            document.getElementById("modalTitle").innerText = "Add a New Schedule";
            document.getElementById("modalButton").innerText = "Add Schedule";
            
            // 🆕 HIDE THE DELETE BUTTON WHEN ADDING
            document.getElementById("deleteButton").style.display = "none";
            
            document.getElementById("scheduleModal").style.display = "block";
        }

        function openEditModal(id) {
            // UPDATED: Find schedule in 'allSchedules' instead of 'schedules'
            const s = allSchedules.find(s => s.schedule_id == id);
            if (!s) {
                console.error("Could not find schedule with id:", id);
                return;
            }

            // Populate the form with data
            document.getElementById("schedule_id").value = s.schedule_id;
            document.getElementById("day").value = s.day;
            document.getElementById("type").value = s.type;
            document.getElementById("location").value = s.location;
            document.getElementById("route").value = s.destination; 
            document.getElementById("time").value = s.departure_time; 
            document.getElementById("arrival").value = s.estimated_arrival; 
            document.getElementById("frequency").value = s.frequency;

            document.getElementById("modalTitle").innerText = "Edit Schedule";
            document.getElementById("modalButton").innerText = "Save Changes";
            
            // 🆕 SHOW THE DELETE BUTTON WHEN EDITING
            document.getElementById("deleteButton").style.display = "inline-block";

            document.getElementById("scheduleModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("scheduleModal").style.display = "none";
        }

        async function saveSchedule() {
            const data = {
                day: document.getElementById("day").value,
                type: document.getElementById("type").value,
                location: document.getElementById("location").value,
                destination: document.getElementById("route").value,
                departure: document.getElementById("time").value,
                arrival: document.getElementById("arrival").value,
                frequency: document.getElementById("frequency").value
            };
            
            const scheduleId = document.getElementById("schedule_id").value;
            
            let endpoint = "";
            if (scheduleId) {
                endpoint = "php/update_schedule.php";
                data.schedule_id = scheduleId;
            } else {
                endpoint = "php/add_schedules.php";
            }

            // Using console.error instead of alert for better non-blocking feedback
            if (!data.location || !data.destination || !data.departure || !data.arrival) {
                console.error("Please fill in all required fields"); 
                // You might replace this with a custom modal message in a production app
                return;
            }

            const res = await fetch(endpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (result.status === "success") {
                closeModal();
                loadSchedules(); 
            } else {
                console.error("Error: " + result.message); 
            }
        }
        
        // 🆕 NEW FUNCTION TO HANDLE DELETION (SOFT DELETE)
        async function deleteSchedule() {
            const scheduleId = document.getElementById("schedule_id").value;
            
            // Replaced alert/confirm with console output/custom message logic for safety
            if (!scheduleId) {
                console.error("No schedule ID found for deletion.");
                return;
            }

            if (!confirm("Are you sure you want to delete this schedule? It will be moved to the trash.")) {
                return;
            }

            const res = await fetch("php/delete_schedule.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                // Send the ID of the schedule to be deleted
                body: JSON.stringify({ schedule_id: scheduleId })
            });

            const result = await res.json();
            
            if (result.status === "success") {
                closeModal();
                // Reload and re-render the list after deletion
                loadSchedules(); 
            } else {
                console.error("Error deleting schedule: " + result.message); 
            }
        }

        window.onload = function() {
            loadSchedules().catch(error => console.error('Error fetching schedules:', error));
        };

        // UPDATED: Add click listeners for pagination
        prevPageBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                updateDisplay(); // Re-render the new page
            }
        });

        nextPageBtn.addEventListener("click", () => {
            // Calculate if there's a next page
            const maxPage = Math.ceil(currentView.length / rowsPerPage);
            if (currentPage < maxPage) {
                currentPage++;
                updateDisplay(); // Re-render the new page
            }
        });

        // --- THIS IS THE FIX ---
        // Updated the logout function to point to the correct admin logout file
        function logout() {
            // I removed the 'confirm()' box and console.log
            window.location.href = "admin_logout.php";
        }

    </script>
</body>
</html>
