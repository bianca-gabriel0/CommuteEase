<?php
// include the database connection using an explicit path so it works regardless of include_path
require_once __DIR__ . '/php/db.php'; // Connect to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare SQL insert
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);

    if ($stmt->execute()) {
        // ✅ Show alert, then redirect to Home.php
        echo "<script>
                alert('✅ Your account has been created successfully!');
                window.location.href = 'Home.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('⚠️ Error creating account: " . addslashes($stmt->error) . "');
                window.history.back();
              </script>";
    }

    $stmt->close();
}
$conn->close();
?>
