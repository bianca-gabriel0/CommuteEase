// Replace inside saveProfile()
fetch("/api/user/update", {
  method: "PUT",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ firstName: first, lastName: last, email: "example@gmail.com" })
})
.then(res => res.json())
.then(data => {
  alert("✅ " + data.message);
  window.location.href = "accountinfo.php";
})
.catch(err => console.error("Error updating profile:", err));
