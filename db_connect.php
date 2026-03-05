<?php
// db_connect.php - Database connection file
$servername = "localhost"; // Assuming localhost, adjust if needed
$username = "root"; // Assuming default root user, adjust if needed
$password = "123456";
$dbname = "rsoa_rsoa278_32";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
