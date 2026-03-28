<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Only logged in students are allowed
require_role('student');

if (isset($_POST['update_profile_btn'])) {
    
    // Sanitize
    $faculty = trim($_POST['faculty']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $user_id = $_SESSION['user_id']; 

    if (empty($faculty) || empty($department) || empty($phone)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../student/update_profile.php");
        exit();
    }

    // Prepare update query
    $sql = "UPDATE users SET faculty = ?, department = ?, phone = ?, profile_completed = 1 WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        // "sssi" -> string, string, string, integer
        mysqli_stmt_bind_param($stmt, "sssi", $faculty, $department, $phone, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Success! Update session logic
            $_SESSION['profile_completed'] = 1;
            $_SESSION['success'] = "Profile completed successfully!";
            
            // Redirect to dashboard now that they are verified
            header("Location: ../student/dashboard.php");
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
            header("Location: ../student/update_profile.php");
        }
    } else {
        $_SESSION['error'] = "Database error. Please try again.";
        header("Location: ../student/update_profile.php");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();

} else {
    // Prevent direct access
    header("Location: ../student/update_profile.php");
    exit();
}
?>
