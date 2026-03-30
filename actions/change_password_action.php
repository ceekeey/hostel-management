<?php
session_start();
require_once '../includes/auth.php';
require_role('student');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required.";
        header("Location: ../student/settings.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header("Location: ../student/settings.php");
        exit();
    }

    // Verify Old Password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($old_password, $user['password'])) {
        // Hashing and Updating
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['success'] = "Password changed successfully! Keep it secure.";
        } else {
            $_SESSION['error'] = "Error updating password.";
        }
    } else {
        $_SESSION['error'] = "The current password you entered is incorrect.";
    }

    mysqli_close($conn);
    header("Location: ../student/settings.php");
    exit();
}
?>
