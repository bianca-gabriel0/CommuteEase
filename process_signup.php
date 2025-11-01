<?php
session_start();

require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $rawPassword = $_POST['password']; 

    if (strlen($rawPassword) < 8 || !preg_match('/\d/', $rawPassword)) {
        echo "<script>
                // Using window.history.back() to keep the user on the signup form
                alert('⚠️ Password must be at least 8 characters and contain at least one number.');
                window.history.back();
              </script>";
        exit();
    }
    
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
        
        if ($stmt->execute()) {

            $_SESSION['user_id'] = $stmt->insert_id; 
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            header("Location: Home.php");
            exit();
            
        } 

        $stmt->close();
        
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            echo "<script>
                    alert('⚠️ This email address is already registered. Please sign in or use a different email.');
                    window.history.back();
                  </script>";
        } else {
             echo "<script>
                    alert('⚠️ Fatal database error: ' + '{$e->getMessage()}');
                    window.history.back();
                  </script>";
        }
        
    } finally {
        if (isset($conn) && $conn) {
             $conn->close();
        }
    }
}
?>
