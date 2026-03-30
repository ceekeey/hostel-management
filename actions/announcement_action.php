<?php
session_start();
require_once '../includes/auth.php';
require_role('admin');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $priority = mysqli_real_escape_string($conn, $_POST['priority']);

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = "Both title and content are required.";
        } else {
            $sql = "INSERT INTO announcements (title, content, priority) VALUES ('$title', '$content', '$priority')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "Announcement broadcasted successfully!";
            } else {
                $_SESSION['error'] = "An error occurred while posting: " . mysqli_error($conn);
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM announcements WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Announcement removed from history.";
        } else {
            $_SESSION['error'] = "Error removing notice: " . mysqli_error($conn);
        }
    }

    header("Location: ../admin/announcements.php");
    exit();
}
?>
