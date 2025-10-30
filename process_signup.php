<?php
// include the database connection
require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- This is SIGNUP code ---
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    // We HASH the password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare SQL to INSERT a new user
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);

    if ($stmt->execute()) {
        // SUCCESS: Account created!
        
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id; // Get the ID of the new user
        $_SESSION['first_name'] = $firstName;
        // FIXES confirmed: Save last name and email
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $email;
        
        // Redirect to Home.php (they are now logged in)
        header("Location: Home.php");
        exit();
        
    } else {
        // FAIL: Probably a duplicate email
        echo "<script>
                alert('⚠️ Error creating account: This email is already taken.');
                window.history.back();
              </script>";
    }

    $stmt->close();
}
$conn->close();
?>
