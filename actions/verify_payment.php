<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../config/paystack.php';

require_role('student');

if (!isset($_GET['reference'])) {
    $_SESSION['error'] = "No reference supplied.";
    header("Location: ../student/payment.php");
    exit();
}

$reference = $_GET['reference'];
$allocation_id = $_GET['alloc_id'] ?? 0;

// Verify transaction with Paystack
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache",
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    $_SESSION['error'] = "Curl Error: " . $err;
    header("Location: ../student/payment.php");
    exit();
}

if (!$response) {
    $_SESSION['error'] = "Empty response from Paystack.";
    header("Location: ../student/payment.php");
    exit();
}

$result = json_decode($response);

if ($result->status && $result->data->status === 'success') {
    // Payment is successful
    $user_id = $_SESSION['user_id'];
    $amount = $result->data->amount / 100; // Paystack returns in kobo
    $paid_at = date("Y-m-d H:i:s");

    // Check if payment already exists
    $check_sql = "SELECT id FROM payments WHERE reference_code = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $reference);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        mysqli_stmt_close($stmt);

        // Insert payment record
        $insert_sql = "INSERT INTO payments (student_id, allocation_id, amount, reference_code, payment_method, status, paid_at) VALUES (?, ?, ?, ?, 'Paystack', 'paid', ?)";
        $i_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($i_stmt, "iidss", $user_id, $allocation_id, $amount, $reference, $paid_at);
        
        if (mysqli_stmt_execute($i_stmt)) {
            $_SESSION['success'] = "Payment successful! Your receipt has been cleared.";
        } else {
            $_SESSION['error'] = "Database error: Failed to record payment.";
        }
        mysqli_stmt_close($i_stmt);
    } else {
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Payment already recorded.";
    }

} else {
    $_SESSION['error'] = "Payment verification failed: " . ($result->message ?? "Unknown error");
}

mysqli_close($conn);
header("Location: ../student/payment.php");
exit();
?>
