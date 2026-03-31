<?php
// Database configuration
$host = 'localhost';
$username = 'root'; // default XAMPP username
$password = '';     // default XAMPP password
$database = 'hostel_db'; // Your database name

// Create database connection using procedural mysqli
$conn = mysqli_connect($host, $username, $password, $database);

// 2. Base URL (detect subdirectory automatically)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
// Standardize to always have trailing slash, then remove it for the constant
$base_url = $protocol . "://" . $host . rtrim($script_name, '/');
// If we are in actions/ or admin/, we need to go up levels, but here we want the root
$base_url = preg_replace('/\/(actions|admin|student|includes|public|assets)$/', '', $base_url);
define('BASE_URL', $base_url);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
