<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "commute_ease"; // kung ano name neto is yun din ipangalan nyo sa database

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
