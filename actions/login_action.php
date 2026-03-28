<?php
// Start session to store user info 
session_start();

// Include the procedural database connection script
require_once '../config/database.php';

// Check if the form was actually submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Retrieve and sanitize inputs
    // trim() removes leading/trailing spaces
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Validate inputs (are they empty?)
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        $_SESSION['old_login'] = $_POST;
        header("Location: ../public/login.php");
        exit();
    }

    // 3. Prepare the SQL query
    // Notice we fetch profile_completed now
    $sql = "SELECT id, fullname, password, role, profile_completed FROM users WHERE email = ?";
    
    // Initialize procedural prepared statement
    $stmt = mysqli_stmt_init($conn);
    
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_SESSION['error'] = "Database error. Please try again later.";
        $_SESSION['old_login'] = $_POST;
        header("Location: ../public/login.php");
        exit();
    }

    // 4. Bind the parameters
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    // 5. Execute the query
    mysqli_stmt_execute($stmt);
    
    // 6. Get the result set from the compiled statement
    $result = mysqli_stmt_get_result($stmt);
    
    // 7. Check if exactly one user was found
    if ($row = mysqli_fetch_assoc($result)) {
        
        // 8. Verify the password
        if (password_verify($password, $row['password'])) {
            
            // Password is correct, create session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];
            // Store profile status logically
            $_SESSION['profile_completed'] = $row['profile_completed'];
            
            // Redirect based on the user's role and profile status
            if ($row['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else if ($row['role'] === 'staff') {
                header("Location: ../staff/dashboard.php");
            } else if ($row['role'] === 'student') {
                
                // CRITICAL: Check if student has completed their profile
                if ($row['profile_completed'] == 0) {
                    $_SESSION['success'] = "Welcome! Please complete your profile to access your dashboard.";
                    header("Location: ../student/update_profile.php");
                } else {
                    header("Location: ../student/dashboard.php");
                }
                
            } else {
                header("Location: ../public/login.php");
            }
            exit();
            
        } else {
            // Invalid password
            $_SESSION['error'] = "Incorrect password.";
            $_SESSION['old_login'] = $_POST;
            header("Location: ../public/login.php");
            exit();
        }
    } else {
        // No user found with that email
        $_SESSION['error'] = "No account found with that email.";
        $_SESSION['old_login'] = $_POST;
        header("Location: ../public/login.php");
        exit();
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    // If someone tries to access this script directly without submitting the form
    header("Location: ../public/login.php");
    exit();
}
?>
