<?php
// --- Admin Security Check ---
// We MUST start the session to check for a valid admin
session_start();

// If the admin is not logged in (session not set), kick them to the login page
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin_login.php");
    exit;
}

// include the database connection using an explicit path so it works regardless of include_path
require_once __DIR__ . '/php/db.php';

// Fetch all users from the database
$result = $conn->query("SELECT user_id, first_name, last_name, email, created_at FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Commute Ease Admin - Users</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&family=Birthstone&family=Ephesis&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="scheduleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <div class="sidebar">
    <img src="assets/CE-logo.png" alt="" class="signup-image">
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="scheduleadmin.php"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
    <a href="view_users_admin.php" class="active"><i class="fa-solid fa-user-gear"></i> View Users</a>
    <button class="logout-button" onclick="logout()">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
    </button>
  </div>

  <div class="main-content">
    <div class="card2">
      <h2>Registered Users</h2>

      <table class="schedule-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['user_id']; ?></td>
              <td><?php echo htmlspecialchars($row['first_name']); ?></td>
              <td><?php echo htmlspecialchars($row['last_name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo $row['created_at']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function logout() {
      // --- THIS IS THE FIX ---
      // I removed the 'confirm()' box and pointed to the correct admin logout file.
      window.location.href = "admin_logout.php";
    }
  </script>
</body>
</html>

<?php
$conn->close();
?>
