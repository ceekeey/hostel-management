<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Only logged in students
require_role('student');

// Verify submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $preferred_block = trim($_POST['preferred_block'] ?? '');
    $room_type = trim($_POST['room_type'] ?? 'Standard');

    // 1. Validation
    if (empty($preferred_block) || empty($room_type)) {
        $_SESSION['error'] = "Please select all required fields.";
        header("Location: ../student/apply.php");
        exit();
    }

    // Ensure valid room types
    if (!in_array($room_type, ['Standard', 'Premium'])) {
        $room_type = 'Standard';
    }

    // 2. Check if student already has a pending or approved application
    $check_sql = "SELECT id FROM applications WHERE student_id = ? AND (status = 'pending' OR status = 'approved')";
    $check_stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($check_stmt, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['error'] = "You have already submitted an application.";
        mysqli_stmt_close($check_stmt);
        header("Location: ../student/apply.php");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // 3. Insert specific application securely
    $insert_sql = "INSERT INTO applications (student_id, preferred_block, room_type, status) VALUES (?, ?, ?, 'pending')";
    $insert_stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($insert_stmt, $insert_sql)) {
        mysqli_stmt_bind_param($insert_stmt, "iss", $user_id, $preferred_block, $room_type);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success'] = "Application submitted successfully! Please wait for admin review.";
        } else {
            $_SESSION['error'] = "Failed to submit application. Please try again.";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $_SESSION['error'] = "Database error. Please contact admin.";
    }

    mysqli_close($conn);
    header("Location: ../student/apply.php");
    exit();

} else {
    header("Location: ../student/apply.php");
    exit();
}
?>
