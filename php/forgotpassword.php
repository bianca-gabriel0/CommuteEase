<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Email not found!'); window.location.href='forgotpassword.html';</script>";
        exit;
    }

    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $conn->query("DELETE FROM password_resets WHERE email='$email'"); 
    $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $token, $expires);
    $stmt->execute();
    $resetLink = "http://localhost/forgot_password_system/resetpassword.php?token=" . $token;
    echo "<script>
      alert('A reset link has been generated: $resetLink');
      window.location.href='success.html';
    </script>";
}
?>
