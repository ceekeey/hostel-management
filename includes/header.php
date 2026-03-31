<?php
// Global session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Function to get base URL gracefully
// Use global base URL from config if available, otherwise fallback
if(!defined('BASE_URL')) {
    $base_url = '/hostel';
} else {
    $base_url = BASE_URL;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16a34a',
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
                    <a href="<?php echo $base_url; ?>/index.php" class="flex items-center gap-2 group">
                        <img src="<?php echo $base_url; ?>/logo.png" alt="Logo" class="h-10 w-auto">
                        <span class="text-2xl font-bold text-primary tracking-tight hidden sm:block">
                            Hostel<span class="text-dark">Sys</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop Nav Links & Auth -->
                <div class="hidden md:flex items-center space-x-5">
                    <a href="<?php echo $base_url; ?>/index.php"
                        class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                        <i data-lucide="home" class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i>
                        Home
                    </a>
                    <a href="https://gsu.edu.ng/home/"
                        class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                        <i data-lucide="globe" class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i>
                        Main Website
                    </a>
                    <a href="https://student.portal.gsu.edu.ng/login"
                        class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                        <i data-lucide="graduation-cap"
                            class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i> Student Portal
                    </a>

                    <div class="h-6 w-px bg-gray-300 mx-2"></div> <!-- divider -->

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?php echo $base_url; ?>/admin/dashboard.php"
                                class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                                <i data-lucide="layout-dashboard"
                                    class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i> Dashboard
                            </a>
                        <?php elseif ($_SESSION['role'] === 'staff'): ?>
                            <a href="<?php echo $base_url; ?>/staff/dashboard.php"
                                class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                                <i data-lucide="layout-dashboard"
                                    class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i> Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>/student/dashboard.php"
                                class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                                <i data-lucide="layout-dashboard"
                                    class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i> Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo $base_url; ?>/actions/logout.php"
                            class="group flex items-center gap-1.5 bg-primary text-white px-5 py-2 rounded-md font-semibold hover:bg-green-700 transition duration-300 hover:-translate-y-0.5 shadow-md hover:shadow-lg ml-2">
                            <i data-lucide="log-out" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/public/login.php"
                            class="group flex items-center gap-1.5 text-dark hover:text-primary font-medium transition-all duration-300">
                            <i data-lucide="log-in" class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i>
                            Login
                        </a>
                        <a href="<?php echo $base_url; ?>/public/register.php"
                            class="group flex items-center gap-1.5 bg-primary text-white px-5 py-2 rounded-md font-semibold hover:bg-green-700 transition duration-300 hover:-translate-y-0.5 shadow-md hover:shadow-lg ml-2">
                            <i data-lucide="user-plus"
                                class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform"></i> Register
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" type="button"
                        class="text-dark hover:text-primary focus:outline-none p-2 rounded-md hover:bg-gray-100 transition"
                        aria-label="Toggle Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="hidden md:hidden bg-white border-t border-gray-100 shadow-lg absolute w-full left-0 transition-all duration-300 ease-in-out">
            <div class="px-4 pt-2 pb-4 space-y-1 flex flex-col bg-white">
                <a href="<?php echo $base_url; ?>/index.php"
                    class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                    <i data-lucide="home" class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i>
                    Home
                </a>
                <a href="#"
                    class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                    <i data-lucide="globe" class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i>
                    Main Website
                </a>
                <a href="<?php echo $base_url; ?>/student/dashboard.php"
                    class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                    <i data-lucide="graduation-cap"
                        class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i> Student Portal
                </a>

                <div class="border-t border-gray-100 my-2 pt-2"></div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo $base_url; ?>/admin/dashboard.php"
                            class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                            <i data-lucide="layout-dashboard"
                                class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i> Dashboard
                        </a>
                    <?php elseif ($_SESSION['role'] === 'staff'): ?>
                        <a href="<?php echo $base_url; ?>/staff/dashboard.php"
                            class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                            <i data-lucide="layout-dashboard"
                                class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/student/dashboard.php"
                            class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                            <i data-lucide="layout-dashboard"
                                class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base_url; ?>/actions/logout.php"
                        class="flex items-center justify-center gap-2 bg-primary text-white block px-3 py-2.5 rounded-md text-base font-semibold mt-3 text-center shadow-md active:scale-95 transition-transform">
                        <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/public/login.php"
                        class="group text-dark hover:text-primary hover:bg-green-50 block px-3 py-2 rounded-md text-base font-medium transition flex items-center gap-3">
                        <i data-lucide="log-in"
                            class="w-5 h-5 text-gray-500 group-hover:text-primary transition-colors"></i> Login
                    </a>
                    <a href="<?php echo $base_url; ?>/public/register.php"
                        class="flex items-center justify-center gap-2 bg-primary text-white block px-3 py-2.5 rounded-md text-base font-semibold mt-3 text-center shadow-md active:scale-95 transition-transform">
                        <i data-lucide="user-plus" class="w-5 h-5"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="min-h-screen">