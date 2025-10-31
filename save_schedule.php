<?php
// --- This is save_schedule.php ---

// 1. We MUST start the session to get the logged-in user's ID
session_start();

// 2. Include your database connection
include 'php/db.php'; // Make sure this path is correct

// 3. Check if the user is logged in AND if the form was actually submitted
if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Get the data
    $user_id = $_SESSION['user_id'];
    $schedule_id = $_POST['schedule_id'];
    
    // --- NEW: We'll use this to redirect with a message ---
    $redirect_status = "exists"; // Default to "already exists"

    if (!empty($schedule_id)) {
        
        // 5. Run the SQL Query
        // We use "INSERT IGNORE" which won't throw an error on duplicates
        $sql = "INSERT IGNORE INTO saved_schedules (user_id, schedule_id) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $schedule_id);
        $stmt->execute();

        // --- NEW: Check if a row was ACTUALLY inserted ---
        // $stmt->affected_rows will be 1 on a NEW save
        // $stmt->affected_rows will be 0 on a DUPLICATE (ignored) save
        if ($stmt->affected_rows > 0) {
            $redirect_status = "saved"; // It was a new save!
        }
        
        $stmt->close();
        $conn->close();
    }
    
    // 7. Redirect back with the correct status
    header("Location: schedule-main.php?status=" . $redirect_status);
    exit;

} else {
    // If someone just types in the URL, send them back without a message
    header("Location: schedule-main.php");
    exit; 
}

?>

