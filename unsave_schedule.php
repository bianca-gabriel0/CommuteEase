<?php

session_start();

include 'php/db.php'; 

if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $saved_id = $_POST['saved_id']; 

    if (!empty($saved_id)) {
        
        $sql = "DELETE FROM saved_schedules WHERE saved_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $saved_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
    header("Location: accountinfo.php?status=unsaved");
    exit;

} else {
    header("Location: accountinfo.php");
    exit; 
}
?>

