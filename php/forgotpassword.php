<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Email not found!'); window.location.href='forgotpassword.html';</script>";
        exit;
    }

    // Generate token + expiration (15 mins)
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    // Save to database
    $conn->query("DELETE FROM password_resets WHERE email='$email'"); // clear old requests
    $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $token, $expires);
    $stmt->execute();

    // Send reset email (you can use PHPMailer for real emails)
    $resetLink = "http://localhost/forgot_password_system/resetpassword.php?token=" . $token;

    // For testing, we’ll just show the link
    echo "<script>
      alert('A reset link has been generated: $resetLink');
      window.location.href='success.html';
    </script>";
}
?>
