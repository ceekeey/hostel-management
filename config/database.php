<?php
// Database configuration
$host = 'localhost';
$username = 'root'; // default XAMPP username
$password = '';     // default XAMPP password
$database = 'hostel_db'; // Your database name

// Create database connection using procedural mysqli
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
