<?php
session_start();
require_once '../includes/auth.php';
require_role('admin');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $room_id = (int)($_POST['room_id'] ?? 0);

    if ($action === 'assign') {
        $item_id = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        $condition_status = mysqli_real_escape_string($conn, $_POST['condition_status']);

        if (!$room_id || !$item_id || $quantity <= 0) {
            $_SESSION['error'] = "Invalid assignment details.";
        } else {
            // Check if item already exists in room, then update instead of insert (optional design choice)
            $check_sql = "SELECT id, quantity FROM room_inventory WHERE room_id = $room_id AND item_id = $item_id";
            $check_res = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_res) > 0) {
                $row = mysqli_fetch_assoc($check_res);
                $new_qty = $row['quantity'] + $quantity;
                $update_sql = "UPDATE room_inventory SET quantity = $new_qty, condition_status = '$condition_status' WHERE id = " . $row['id'];
                mysqli_query($conn, $update_sql);
            } else {
                $sql = "INSERT INTO room_inventory (room_id, item_id, quantity, condition_status) VALUES ($room_id, $item_id, $quantity, '$condition_status')";
                mysqli_query($conn, $sql);
            }
            $_SESSION['success'] = "Assets added to room successfully.";
        }
    } elseif ($action === 'remove') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM room_inventory WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Asset removed from room.";
        } else {
            $_SESSION['error'] = "Error removing asset: " . mysqli_error($conn);
        }
    }

    header("Location: ../admin/room_inventory.php?room_id=$room_id");
    exit();
}
?>
