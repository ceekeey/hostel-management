<?php
session_start();
require_once '../includes/auth.php';
require_role('admin');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $total_stock = (int)$_POST['total_stock'];

        if (empty($item_name) || $total_stock < 0) {
            $_SESSION['error'] = "Invalid input details.";
        } else {
            $sql = "INSERT INTO inventory (item_name, category, total_stock) VALUES ('$item_name', '$category', $total_stock)";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "Inventory item '$item_name' registered successfully.";
            } else {
                $_SESSION['error'] = "Error registering item: " . mysqli_error($conn);
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM inventory WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Inventory item deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting item: " . mysqli_error($conn);
        }
    }

    header("Location: ../admin/inventory.php");
    exit();
}
?>
