<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commute Ease Admin - Commuters</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&family=Birthstone&family=Ephesis&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="commuters.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
  
    </style>
</head>
<body>

<div class="sidebar">
    <img src="assets/CE-logo.png" alt="" class="signup-image">
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard </a>
    <a href="scheduleadmin.php"><i class="fa-solid fa-calendar-alt"></i> Schedules</a>
    <a href="commuters.php"><i class="fa-solid fa-users"></i> Commuters</a>
    <button class="logout-button" onclick="logout()"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</button>
</div>



<div class="main-content">
  <div class="card2">
    <h2>Commuters</h2>

    <!-- user search bar -->
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search by name..." onkeyup="searchUsers()">
      <i class="fa fa-search"></i>
    </div>
  </div>

    <!-- commuters table -->
    <table id="commutersTable" class="commuters-table">
      <thead>
        <tr>
          <th>Names</th>
          <th>Email</th>
          <th>Password</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Juan Dela Cruz</td>
          <td>juan@email.com</td>
          <td>
            <input type="password" value="password123" readonly>
            <button class="show-btn" onclick="togglePassword(this)">Show</button>
          </td>
          <td>
            <button class="delete-btn" onclick="deleteRow(this)">🗑</button>
          </td>
        </tr>
        <tr>
          <td>Maria Santos</td>
          <td>maria@email.com</td>
          <td>
            <input type="password" value="mypass" readonly>
            <button class="show-btn" onclick="togglePassword(this)">Show</button>
          </td>
          <td>
            <button class="delete-btn" onclick="deleteRow(this)">🗑</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Toggle Show/Hide password
  function togglePassword(button) {
    const input = button.previousElementSibling;
    if (input.type === "password") {
      input.type = "text";
      button.textContent = "Hide";
    } else {
      input.type = "password";
      button.textContent = "Show";
    }
  }

  // delete row with confirmation
  function deleteRow(button) {
    const row = button.closest("tr");
    const userName = row.querySelector("td").textContent; // first column = name
    if (confirm(`Are you sure you want to delete ${userName}?`)) {
      row.remove();
    }
  }

  // search bar function
  function searchUsers() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("commutersTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
      let td = tr[i].getElementsByTagName("td")[0]; // Names column
      if (td) {
        let textValue = td.textContent || td.innerText;
        tr[i].style.display = textValue.toLowerCase().includes(filter) ? "" : "none";
      }
    }
  }
  
</script>

</body>
</html>