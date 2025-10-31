<?php
// --- This is unsave_schedule.php ---

// 1. We MUST start the session
session_start();

// 2. Include your database connection
include 'php/db.php'; 

// 3. Check if user is logged in AND form was submitted
if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Get the data
    $user_id = $_SESSION['user_id'];
    $saved_id = $_POST['saved_id']; 

    if (!empty($saved_id)) {
        
        // 5. Run the SQL Query
        // Security: Make sure the saved_id also belongs to the user_id
        $sql = "DELETE FROM saved_schedules WHERE saved_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $saved_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
    // --- CHANGE: Redirect back with a status message ---
    header("Location: accountinfo.php?status=unsaved");
    exit;

} else {
    // If someone just types in the URL, send them back
    header("Location: accountinfo.php");
    exit; 
}
?>

