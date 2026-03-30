<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $block_name = trim($_POST['block_name']);
    $room_number = trim($_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    $room_type = trim($_POST['room_type'] ?? 'Standard');

    if (empty($block_name) || empty($room_number) || $capacity < 1) {
        $_SESSION['error'] = "Invalid room details provided.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    // Check mapping collision
    $check_sql = "SELECT id FROM rooms WHERE block_name = ? AND room_number = ?";
    $stmt_check = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt_check, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "ss", $block_name, $room_number);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['error'] = "This room already exists in this block.";
        mysqli_stmt_close($stmt_check);
        header("Location: ../admin/manage_rooms.php");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // Insert
    $insert_sql = "INSERT INTO rooms (block_name, room_number, capacity, room_type) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_stmt_init($conn);
    
    if (mysqli_stmt_prepare($stmt, $insert_sql)) {
        mysqli_stmt_bind_param($stmt, "ssis", $block_name, $room_number, $capacity, $room_type);
        if (mysqli_stmt_execute($stmt)) {
            $room_id = mysqli_insert_id($conn);
            
            // Auto-assign beds based on capacity
            $bed_sql = "SELECT id FROM inventory WHERE item_name = 'Single Bed' LIMIT 1";
            $bed_res = mysqli_query($conn, $bed_sql);
            if ($bed_row = mysqli_fetch_assoc($bed_res)) {
                $bed_item_id = $bed_row['id'];
                $assign_sql = "INSERT INTO room_inventory (room_id, item_id, quantity, condition_status) VALUES ($room_id, $bed_item_id, $capacity, 'Good')";
                mysqli_query($conn, $assign_sql);
            }
            
            $_SESSION['success'] = "Room $room_number securely added to $block_name with $capacity beds auto-assigned!";
        } else {
            $_SESSION['error'] = "Database write collision.";
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
