<?php
// Global session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Function to get base URL gracefully
$base_url = '/hotel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
    
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0d6efd',
                        dark: '#212529',
                        light: '#f8f9fa',
                        success: '#198754'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-light font-sans text-dark antialiased">
    <!-- 1. Sticky Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo $base_url; ?>/index.php" class="text-2xl font-bold text-primary tracking-tight">
                        Hostel<span class="text-dark">Sys</span>
                    </a>
                </div>
                
                <!-- Nav Links & Auth -->
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $base_url; ?>/index.php" class="text-dark hover:text-primary font-medium transition duration-150">Home</a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="text-dark hover:text-primary font-medium">Dashboard</a>
                        <?php elseif($_SESSION['role'] === 'staff'): ?>
                            <a href="<?php echo $base_url; ?>/staff/dashboard.php" class="text-dark hover:text-primary font-medium">Dashboard</a>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>/student/dashboard.php" class="text-dark hover:text-primary font-medium">Dashboard</a>
                        <?php endif; ?>
                        <a href="<?php echo $base_url; ?>/actions/logout.php" class="bg-primary text-white px-5 py-2 rounded-md font-semibold hover:bg-blue-700 transition shadow-md">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/public/login.php" class="text-dark hover:text-primary font-medium transition">Login</a>
                        <a href="<?php echo $base_url; ?>/public/register.php" class="bg-primary text-white px-5 py-2 rounded-md font-semibold hover:bg-blue-700 transition shadow-md">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="min-h-screen">
