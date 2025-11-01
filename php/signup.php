<?php
$host = 'localhost';  
$user = 'your_db_username';  
$pass = 'your_db_password';  
$dbname = 'your_database_name';  
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) $errors[] = "Password must be at least 8 characters with a number.";
    if (empty($errors)) {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = "Email already registered.";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
                    $insert->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
                    if ($insert->execute()) {
                        echo "Signup successful!";
                    } else {
                        $errors[] = "Signup failed. Please try again.";
                    }
                    $insert->close();
                }
                $stmt->close();
            }
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<p style='color:red;'>$error</p>";
                }
            }
        }
        $conn->close();
        ?>
