<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Only logged in students
require_role('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $allocation_id = $_POST['allocation_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(4)));

    if ($allocation_id <= 0 || $amount <= 0) {
        $_SESSION['error'] = "Invalid payment details.";
        header("Location: ../student/payment.php");
        exit();
    }

    // 1. Check if payment already exists for this allocation
    $check_sql = "SELECT id FROM payments WHERE allocation_id = ? AND status = 'paid'";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $allocation_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['error'] = "This allocation has already been paid for.";
        mysqli_stmt_close($check_stmt);
        header("Location: ../student/payment.php");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // 2. Insert payment record (Mock successful payment)
    $insert_sql = "INSERT INTO payments (student_id, allocation_id, amount, reference_code, status, paid_at) VALUES (?, ?, ?, ?, 'paid', NOW())";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "iids", $user_id, $allocation_id, $amount, $reference);

    if (mysqli_stmt_execute($insert_stmt)) {
        $_SESSION['success'] = "Payment successful! Your receipt has been generated.";
    } else {
        $_SESSION['error'] = "Payment processing failed. Please try again.";
    }
    mysqli_stmt_close($insert_stmt);
    mysqli_close($conn);

    header("Location: ../student/payment.php");
    exit();
} else {
    header("Location: ../student/payment.php");
    exit();
}
?>
