<?php
include 'db.php';

$result = $conn->query("SELECT user_id, first_name, last_name, email, created_at FROM users");

echo "<h2>Registered Users</h2>";
echo "<table border='1' cellpadding='8'>
<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Created At</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['user_id']}</td>
            <td>{$row['first_name']}</td>
            <td>{$row['last_name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['created_at']}</td>
          </tr>";
}
echo "</table>";

$conn->close();
?>
