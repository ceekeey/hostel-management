<?php
session_start();
require_once '../includes/auth.php';
require_role('student');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $faculty = mysqli_real_escape_string($conn, $_POST['faculty']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Handle Profile Picture Upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $upload_dir = '../public/uploads/profiles/';
        $file_name = time() . '_' . basename($_FILES['profile_pic']['name']);
        $target_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png');
        if (in_array($file_type, $allowed_types) && $_FILES['profile_pic']['size'] < 2000000) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                $profile_pic = $file_name;
                
                // Track update for SQL
                $update_sql = "UPDATE users SET fullname = ?, phone = ?, faculty = ?, department = ?, profile_pic = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $phone, $faculty, $department, $profile_pic, $user_id);
            } else {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: ../student/settings.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid image format or size too large (Max 2MB).";
            header("Location: ../student/settings.php");
            exit();
        }
    } else {
        // Update without changing profile pic
        $update_sql = "UPDATE users SET fullname = ?, phone = ?, faculty = ?, department = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $fullname, $phone, $faculty, $department, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Profile updated successfully!";
        $_SESSION['fullname'] = $fullname; // Update session name
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: ../student/settings.php");
    exit();
}
?>
