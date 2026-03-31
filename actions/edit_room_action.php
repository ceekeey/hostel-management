<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $block_name = trim($_POST['block_name']);
    $room_number = trim($_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    $room_type = trim($_POST['room_type'] ?? 'Standard');

    if ($room_id <= 0 || empty($block_name) || empty($room_number) || $capacity < 1) {
        $_SESSION['error'] = "Invalid room details provided.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    // Check collision (same block/number but different ID)
    $check_sql = "SELECT id FROM rooms WHERE block_name = ? AND room_number = ? AND id != ?";
    $stmt_check = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt_check, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "ssi", $block_name, $room_number, $room_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['error'] = "Another room already exists with this block and number.";
        mysqli_stmt_close($stmt_check);
        header("Location: ../admin/manage_rooms.php");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // Update
    $update_sql = "UPDATE rooms SET block_name = ?, room_number = ?, capacity = ?, room_type = ? WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($stmt, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "ssisi", $block_name, $room_number, $capacity, $room_type, $room_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Room information updated successfully.";
        } else {
            $_SESSION['error'] = "Database update failure.";
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
    header("Location: ../admin/manage_rooms.php");
    exit();
} else {
    header("Location: ../admin/manage_rooms.php");
    exit();
}
?>
