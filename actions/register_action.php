<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Sanitize incoming inputs
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Validate empty fields
    if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        $_SESSION['old'] = $_POST;
        header("Location: ../public/register.php");
        exit();
    }

    // 3. Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        $_SESSION['old'] = $_POST;
        header("Location: ../public/register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        $_SESSION['old'] = $_POST;
        header("Location: ../public/register.php");
        exit();
    }

    // 4. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        $_SESSION['old'] = $_POST;
        header("Location: ../public/register.php");
        exit();
    }

    // 5. Check if email already exists in the database
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($check_stmt, $check_sql)) {
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['error'] = "An account with this email already exists.";
            $_SESSION['old'] = $_POST;
            mysqli_stmt_close($check_stmt);
            header("Location: ../public/register.php");
            exit();
        }
        mysqli_stmt_close($check_stmt);
    }

    // 6. Hash password securely using BCrypt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // By default, a new registration is a student, and profile is NOT completed (0)
    $role = 'student';
    $profile_completed = 0;

    // 7. Insert the user into the database
    $insert_sql = "INSERT INTO users (fullname, email, phone, password, role, profile_completed) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($insert_stmt, $insert_sql)) {
        // "sssssi" -> 5 strings, 1 integer
        mysqli_stmt_bind_param($insert_stmt, "sssssi", $fullname, $email, $phone, $hashed_password, $role, $profile_completed);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            // Success
            $_SESSION['success'] = "Registration successful! Please login to continue.";
            header("Location: ../public/login.php");
        } else {
            // Execution failure
            $_SESSION['error'] = "Failed to register. Please try again.";
            $_SESSION['old'] = $_POST;
            header("Location: ../public/register.php");
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        // Preparation failure
        $_SESSION['error'] = "Database error. Please contact admin.";
        $_SESSION['old'] = $_POST;
        header("Location: ../public/register.php");
    }

    // Close connection
    mysqli_close($conn);
    exit();

} else {
    // Prevent direct access
    header("Location: ../public/register.php");
    exit();
}
?>
