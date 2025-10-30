<?php
// ALWAYS start the session at the very top, even if redirecting later
session_start();

// include the database connection
require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- 1. Get and Sanitize Input ---
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $rawPassword = $_POST['password']; // Keep raw password for validation

    // --- 2. SERVER-SIDE PASSWORD VALIDATION (The Firewall) ---
    // Minimum 8 characters (.{8,}) AND at least one digit (\d)
    if (strlen($rawPassword) < 8 || !preg_match('/\d/', $rawPassword)) {
        // FAIL: Weak password
        echo "<script>
                // Using window.history.back() to keep the user on the signup form
                alert('⚠️ Password must be at least 8 characters and contain at least one number.');
                window.history.back();
              </script>";
        exit();
    }
    
    // --- 3. Hash Password & Prepare for DB ---
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    // Prepare SQL to INSERT a new user
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    
    // --- NEW: Using try-catch to prevent fatal crash on Duplicate Key ---
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
        
        // Attempt to execute the query
        if ($stmt->execute()) {
            // SUCCESS: Account created!
            
            // --- 4. Store Full Data in Session ---
            $_SESSION['user_id'] = $stmt->insert_id; // Get the ID of the new user
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            // Redirect to Home.php (user is now logged in)
            header("Location: Home.php");
            exit();
            
        } 

        $stmt->close();
        
    } catch (mysqli_sql_exception $e) {
        // --- CATCH BLOCK HANDLES FATAL CRASH ---
        
        // We check the error code (1062) from the exception object ($e) instead of the connection ($conn)
        if ($e->getCode() == 1062) {
            // Error 1062 is MySQL/MariaDB code for 'Duplicate entry' on a UNIQUE key
            echo "<script>
                    alert('⚠️ This email address is already registered. Please sign in or use a different email.');
                    window.history.back();
                  </script>";
        } else {
            // Handle other fatal SQL exceptions (e.g., table not found, bad syntax)
             echo "<script>
                    alert('⚠️ Fatal database error: ' + '{$e->getMessage()}');
                    window.history.back();
                  </script>";
        }
        
    } finally {
        // We close the connection regardless of success or failure
        if (isset($conn) && $conn) {
             $conn->close();
        }
    }
}
?>
