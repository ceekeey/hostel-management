<?php
// We wrap this inside a check to prevent notices if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures the user is logged in
 * Call this function at the top of protected pages
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        // Not logged in -> redirect to login page
        $_SESSION['error'] = "Please log in to access this page.";
        header("Location: ../public/login.php");
        exit();
    }
}

/**
 * Restricts access to specific roles
 * $allowed_roles can be a string (e.g., 'admin') or an array (e.g., ['admin', 'staff'])
 */
function require_role($allowed_roles) {
    // Make sure they're logged in first
    require_login();
    
    // Convert to array if a single string is passed
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    // Check if the current user's role is in the allowed array
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Unauthorized -> redirect them to their respective dashboards
        $_SESSION['error'] = "Unauthorized access.";
        
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else if ($_SESSION['role'] === 'staff') {
            header("Location: ../staff/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit();
    }
}
?>
