<?php
// ALWAYS start the session at the very top
session_start();

// 1. Include your database connection
require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- This is LOGIN code ---
    $email = trim($_POST['email']);
    $password_from_form = $_POST['password']; 

    // 4. Prepare the SQL to FIND the user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // User found!
        $user = $result->fetch_assoc();

        // 6. Verify the password
        if (password_verify($password_from_form, $user['password'])) {
            
            // 7. SUCCESS! Password is correct.
            // Store user data in the session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            // FIXES confirmed: Save last name and email
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email']; 
            
            // Redirect to a members-only page
            header("Location: Home.php");
            exit();

        } else {
            // 8. FAIL! Invalid password
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
    } else {
        // 8. FAIL! No user found
        header("Location: login.php?error=invalid_credentials");
        exit();
    }
    
    $stmt->close();
}
$conn->close();
?>
