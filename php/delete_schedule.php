<?php
// Set the content type to JSON so the JavaScript can easily parse the response
header('Content-Type: application/json');

// NOTE: Ensure the path to your database connection file is correct.
// This is the file that defines your $conn variable (e.g., MySQLi object).
include 'db.php'; 

// 1. Get the JSON data sent from the JavaScript fetch request
// file_get_contents("php://input") reads the raw body of the POST request.
$data = json_decode(file_get_contents("php://input"), true);
$schedule_id = $data['schedule_id'] ?? null;

// 2. Validate the input
if (empty($schedule_id) || !is_numeric($schedule_id)) {
    // Use proper HTTP response codes if possible, but JSON status is sufficient for fetch()
    echo json_encode(['status' => 'error', 'message' => 'Invalid schedule ID provided for deletion.']);
    // Stop script execution
    exit(); 
}

// Check if the database connection object ($conn) exists
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

try {
    // 3. Prepare the SQL statement for a soft delete
    // Using a prepared statement is CRITICAL to prevent SQL injection.
    // Assuming $conn is a MySQLi connection object. If using PDO, syntax would differ slightly.
    $sql = "UPDATE schedule SET is_deleted = TRUE WHERE schedule_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind the integer parameter
    $stmt->bind_param("i", $schedule_id); // "i" stands for integer type

    // 4. Execute the update
    if ($stmt->execute()) {
        // 5. Return a success response
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule moved to trash successfully.']);
        } else {
            // If affected_rows is 0, the ID might have been invalid or already soft-deleted.
            echo json_encode(['status' => 'error', 'message' => 'Schedule ID not found or already in trash.']);
        }
    } else {
        // Handle execution error
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query: ' . $stmt->error]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    // 6. Handle other general errors
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}

// Close connection after execution
$conn->close();

?>
