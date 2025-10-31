<?php
// Database connection details (update these!)
$host = 'localhost';  // e.g., 'localhost' or your server
$user = 'your_db_username';  // e.g., 'root'
$pass = 'your_db_password';  // e.g., '' for local dev
$dbname = 'your_database_name';  // From the SQL above
// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    // Basic validation (mirrors your JS, but server-side is crucial)
    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) $errors[] = "Password must be at least 8 characters with a number.";
    if (empty($errors)) {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                // You should continue your logic here, for example:
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = "Email already registered.";
                } else {
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    // Insert new user
                    $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
                    $insert->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
                    if ($insert->execute()) {
                        echo "Signup successful!";
                    } else {
                        echo "Error: " . $insert->error;
                    }
                    $insert->close();
                }
                $stmt->close();
            } else {
                foreach ($errors as $error) {
                    echo "<p>$error</p>";
                }
            }
        }
        $conn->close();
        ?>
