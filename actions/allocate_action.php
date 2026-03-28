<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $application_id = (int)$_POST['application_id'];
    $student_id = (int)$_POST['student_id'];
    $admin_name = $_SESSION['fullname'];

    // Handle outright rejection immediately without needing room checks
    if (isset($_POST['action_reject'])) {
        $rej_sql = "UPDATE applications SET status = 'rejected' WHERE id = ?";
        $stmt_rej = mysqli_stmt_init($conn);
        if(mysqli_stmt_prepare($stmt_rej, $rej_sql)){
            mysqli_stmt_bind_param($stmt_rej, "i", $application_id);
            mysqli_stmt_execute($stmt_rej);
            $_SESSION['success'] = "Application was officially rejected.";
        }
        mysqli_close($conn);
        header("Location: ../admin/allocations.php");
        exit();
    }

    // Handle Approve & Allocate logic
    if (isset($_POST['action_approve'])) {
        $room_id = (int)$_POST['room_id'];
        
        // 1. Double check room has capacity (Concurrency Protection)
        $cap_sql = "SELECT capacity, current_occupancy FROM rooms WHERE id = ? AND status = 'available' AND current_occupancy < capacity";
        $stmt_cap = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt_cap, $cap_sql);
        mysqli_stmt_bind_param($stmt_cap, "i", $room_id);
        mysqli_stmt_execute($stmt_cap);
        $cap_res = mysqli_stmt_get_result($stmt_cap);
        $room = mysqli_fetch_assoc($cap_res);
        mysqli_stmt_close($stmt_cap);

        if (!$room) {
            $_SESSION['error'] = "The selected room has just reached maximum capacity or is unavailable. Please select another.";
            header("Location: ../admin/allocations.php");
            exit();
        }

        // Extremely critical transactional logic begins (Since we are using procedural MyISAM or basic InnoDB without PDO wrappers, we must execute them sequentially carefully)

        // 2. Change Application Status to Approved
        $upd_app = "UPDATE applications SET status = 'approved' WHERE id = ?";
        $stmt_app = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt_app, $upd_app);
        mysqli_stmt_bind_param($stmt_app, "i", $application_id);
        mysqli_stmt_execute($stmt_app);
        
        // 3. Create active allocation mapping
        // date('Y-m-d') records it
        $alloc_sql = "INSERT INTO allocations (student_id, room_id, allocated_by, allocation_date, status) VALUES (?, ?, ?, CURRENT_DATE, 'active')";
        $stmt_alloc = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt_alloc, $alloc_sql);
        mysqli_stmt_bind_param($stmt_alloc, "iis", $student_id, $room_id, $admin_name);
        mysqli_stmt_execute($stmt_alloc);

        // 4. Update Room parameters physically
        $new_occupancy = $room['current_occupancy'] + 1;
        $new_status = ($new_occupancy >= $room['capacity']) ? 'full' : 'available';

        $upd_room = "UPDATE rooms SET current_occupancy = ?, status = ? WHERE id = ?";
        $stmt_room = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt_room, $upd_room);
        mysqli_stmt_bind_param($stmt_room, "isi", $new_occupancy, $new_status, $room_id);
        mysqli_stmt_execute($stmt_room);

        $_SESSION['success'] = "Student was aggressively bound to the new Room Allocation!";
    }

    mysqli_close($conn);
    header("Location: ../admin/allocations.php");
    exit();

} else {
    header("Location: ../admin/allocations.php");
    exit();
}
?>
