<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];

    if ($room_id <= 0) {
        $_SESSION['error'] = "Invalid room identifier.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    // Since we have ON DELETE CASCADE in the database, we just need to delete the room record
    $delete_sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($stmt, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $room_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Room and all associated records deleted securely.";
        } else {
            $_SESSION['error'] = "Database deletion failure.";
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
