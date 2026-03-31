<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Only admin
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];

    if ($room_id <= 0 || $item_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = "Invalid allocation details.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    // 1. Check current inventory availability
    $inv_sql = "SELECT i.*, 
                (SELECT SUM(quantity) FROM room_inventory WHERE item_id = i.id) as assigned_stock 
                FROM inventory i WHERE i.id = ?";
    $stmt = mysqli_prepare($conn, $inv_sql);
    mysqli_stmt_bind_param($stmt, "i", $item_id);
    mysqli_stmt_execute($stmt);
    $item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$item) {
        $_SESSION['error'] = "Asset not found in master inventory.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    $available = $item['total_stock'] - ($item['assigned_stock'] ?? 0);

    if ($quantity > $available) {
        $_SESSION['error'] = "Insufficient stock. Only $available " . $item['item_name'] . "(s) left.";
        header("Location: ../admin/manage_rooms.php");
        exit();
    }

    // 2. Check if this item is already in this room
    $check_sql = "SELECT id, quantity FROM room_inventory WHERE room_id = ? AND item_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $room_id, $item_id);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($existing) {
        // Update existing quantity
        $new_qty = $existing['quantity'] + $quantity;
        $update_sql = "UPDATE room_inventory SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ii", $new_qty, $existing['id']);
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO room_inventory (room_id, item_id, quantity, condition_status) VALUES (?, ?, ?, 'Good')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "iii", $room_id, $item_id, $quantity);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Allocated $quantity " . $item['item_name'] . "(s) to room successfully.";
    } else {
        $_SESSION['error'] = "Database error. Failed to allocate asset.";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../admin/manage_rooms.php");
    exit();
} else {
    header("Location: ../admin/manage_rooms.php");
    exit();
}
