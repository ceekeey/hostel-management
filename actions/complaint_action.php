<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Only logged in students
require_role('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../student/complaints.php");
        exit();
    }

    // Insert complaint record
    $insert_sql = "INSERT INTO complaints (student_id, subject, message, status) VALUES (?, ?, ?, 'open')";
    $insert_stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($insert_stmt, $insert_sql)) {
        mysqli_stmt_bind_param($insert_stmt, "iss", $user_id, $subject, $message);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success'] = "Complaint ticket #".str_pad(mysqli_stmt_insert_id($insert_stmt), 6, '0', STR_PAD_LEFT)." has been created successfully.";
        } else {
            $_SESSION['error'] = "Failed to submit complaint. Please try again.";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $_SESSION['error'] = "Database error. Please contact admin.";
    }

    mysqli_close($conn);
    header("Location: ../student/complaints.php");
    exit();
} else {
    header("Location: ../student/complaints.php");
    exit();
}
?>
