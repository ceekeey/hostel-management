<?php
// Include auth logic securely
require_once '../includes/auth.php';
require_role('student'); // ensures only logged-in students access this page

// Force profile completion if they somehow bypassed it
if (!isset($_SESSION['profile_completed']) || $_SESSION['profile_completed'] == 0) {
    header("Location: update_profile.php");
    exit();
}

require_once '../config/database.php';
$user_id = $_SESSION['user_id'];

/* 
 * 1. Fetch Student Profile Data (Real dynamic data)
 * Using procedural mysqli and prepared statements for security
 */
$student_sql = "SELECT fullname, email, faculty, department FROM users WHERE id = ?";
$stmt = mysqli_stmt_init($conn);

if (mysqli_stmt_prepare($stmt, $student_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    $student_res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($student_res);
    mysqli_stmt_close($stmt);
} else {
    die("Database access error.");
}

/* 
 * 2. Database Query Examples for Dashboard Cards
 */

// --- Application Status Query ---
$app_sql = "SELECT status FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1";
$app_stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($app_stmt, $app_sql);
mysqli_stmt_bind_param($app_stmt, "i", $user_id);
mysqli_stmt_execute($app_stmt);
$app_res = mysqli_stmt_get_result($app_stmt);
$application = mysqli_fetch_assoc($app_res);
$app_status = $application ? ucfirst($application['status']) : "Not Started";

// --- Room Allocation Query ---
$room_sql = "SELECT r.block_name, r.room_number FROM allocations a JOIN rooms r ON a.room_id = r.id WHERE a.student_id = ? AND a.status = 'active'";
$room_stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($room_stmt, $room_sql);
mysqli_stmt_bind_param($room_stmt, "i", $user_id);
mysqli_stmt_execute($room_stmt);
$room_res = mysqli_stmt_get_result($room_stmt);
$allocation = mysqli_fetch_assoc($room_res);
$room_allocated = $allocation ? $allocation['block_name'] . " - " . $allocation['room_number'] : "Not Assigned";

// --- Payment Status Query ---
$pay_sql = "SELECT status FROM payments WHERE student_id = ? ORDER BY id DESC LIMIT 1";
$pay_stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($pay_stmt, $pay_sql);
mysqli_stmt_bind_param($pay_stmt, "i", $user_id);
mysqli_stmt_execute($pay_stmt);
$pay_res = mysqli_stmt_get_result($pay_stmt);
$payment = mysqli_fetch_assoc($pay_res);
$payment_text = $payment ? ucfirst($payment['status']) : "Unpaid";
$payment_class = ($payment_text == 'Paid') ? 'text-success' : 'text-danger';

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - HostelSys</title>
    
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css for entrance animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: { primary: '#0d6efd', dark: '#212529', light: '#f8f9fa', success: '#198754', warning: '#ffc107', danger: '#dc3545' }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f3f4f6; overflow-x: hidden; } 
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Loader Animation */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff; display: flex; justify-content: center; align-items: center;
            z-index: 9999; transition: opacity 0.5s ease;
        }
        .spinner {
            width: 40px; height: 40px; border: 4px solid #f3f3f3;
            border-top: 4px solid #0d6efd; border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Mobile Sidebar Transitions */
        #mobile-sidebar { transition: transform 0.3s ease-in-out; }
        #sidebar-overlay { transition: opacity 0.3s ease-in-out; }
    </style>
</head>
<body class="font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Global Loader -->
    <div id="page-loader">
        <div class="flex flex-col items-center gap-4">
            <div class="spinner"></div>
            <p class="font-bold text-primary animate-pulse tracking-widest text-xs uppercase">Initializing Portal</p>
        </div>
    </div>

    <?php include '../includes/student_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-10">
        
        <!-- 2. Top Navbar -->
        <header class="h-20 bg-white/80 backdrop-blur-md shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-100 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <!-- Mobile Sidebar Toggle -->
                <button id="open-sidebar" class="lg:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Student Portal</h1>
            </div>
            
            <div class="flex items-center gap-4 lg:gap-6">
                <!-- Notifications Bell -->
                <button class="text-gray-400 hover:text-primary transition relative p-2 rounded-full hover:bg-gray-50 border border-transparent hover:border-gray-200">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-danger border-2 border-white rounded-full"></span>
                </button>
                
                <!-- Profile Area -->
                <div class="flex items-center gap-3 pl-4 lg:pl-6 border-l border-gray-200 cursor-pointer group">
                    <div class="hidden md:block text-right">
                        <p class="font-bold text-dark text-sm leading-tight group-hover:text-primary transition">
                            <?php echo htmlspecialchars($student['fullname']); ?>
                        </p>
                        <p class="text-gray-400 text-xs mt-0.5 font-medium tracking-wide text-uppercase">Student</p>
                    </div>
                    <!-- User Avatar Generator -->
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg border border-primary/20 group-hover:bg-primary group-hover:text-white transition duration-300">
                        <?php echo strtoupper(substr($student['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- 3. Dynamic Page Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            
            <!-- Contextual Welcome -->
            <div class="mb-10 animate__animated animate__fadeInDown">
                <h2 class="text-3xl font-bold text-dark mb-2 tracking-tight">
                    Welcome back, <?php echo htmlspecialchars(explode(' ', trim($student['fullname']))[0]); ?>! 👋
                </h2>
                <p class="text-gray-500 font-medium">Here's a quick overview of your hostel accommodation status.</p>
            </div>

            <!-- Dashboard Cards (Flex/Grid Layout) -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-10">
                
                <!-- Card 1: Application Status -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-xl hover:scale-[1.02] transition-all duration-300 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Application Status</p>
                            <h3 class="text-2xl font-bold text-dark"><?php echo $app_status; ?></h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-primary flex items-center justify-center shadow-inner">
                            <i data-lucide="file-signature" class="w-7 h-7"></i>
                        </div>
                    </div>
                    <div class="mt-auto pt-5 border-t border-gray-50">
                        <a href="apply.php" class="group text-sm font-semibold text-primary hover:text-blue-700 flex items-center gap-1.5 transition">
                            Continue application <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Room Allocation -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-xl hover:scale-[1.02] transition-all duration-300 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Room Allocation</p>
                            <h3 class="text-2xl font-bold text-dark"><?php echo $room_allocated; ?></h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="home" class="w-7 h-7"></i>
                        </div>
                    </div>
                    <div class="mt-auto pt-5 border-t border-gray-50">
                        <span class="text-sm font-semibold text-gray-400 italic">Official Assignment</span>
                    </div>
                </div>

                <!-- Card 3: Payment Status -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-xl hover:scale-[1.02] transition-all duration-300 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Fees & Payments</p>
                            <h3 class="text-2xl font-bold text-dark <?php echo $payment_class; ?>"><?php echo $payment_text; ?></h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-red-50 text-danger flex items-center justify-center shadow-inner">
                            <i data-lucide="credit-card" class="w-7 h-7"></i>
                        </div>
                    </div>
                    <div class="mt-auto pt-5 border-t border-gray-50">
                        <a href="payment.php" class="group text-sm font-semibold text-danger hover:text-red-700 flex items-center gap-1.5 transition">
                            Pay outstanding fees <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Profile Overview (Database Connected UI) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8 animate__animated animate__fadeInUp animate__delay-4s">
                <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-lg text-dark flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-gray-400"></i> Profile Information
                    </h3>
                    <a href="update_profile.php" class="text-sm font-semibold text-primary hover:underline">Edit Details</a>
                </div>
                <div class="p-8">
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="hover:translate-x-2 transition p-2 hover:bg-gray-50 rounded-lg">
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Full Name</dt>
                            <dd class="text-base text-dark font-medium"><?php echo htmlspecialchars($student['fullname']); ?></dd>
                        </div>
                        <div class="hover:translate-x-2 transition p-2 hover:bg-gray-50 rounded-lg">
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Email Address</dt>
                            <dd class="text-base text-dark font-medium"><?php echo htmlspecialchars($student['email']); ?></dd>
                        </div>
                        <div class="hover:translate-x-2 transition p-2 hover:bg-gray-50 rounded-lg">
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Faculty</dt>
                            <dd class="text-base text-dark font-medium"><?php echo htmlspecialchars($student['faculty']); ?></dd>
                        </div>
                        <div class="hover:translate-x-2 transition p-2 hover:bg-gray-50 rounded-lg">
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Department</dt>
                            <dd class="text-base text-dark font-medium"><?php echo htmlspecialchars($student['department']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>
    </main>

    <!-- UI Scripts -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Page Loader Logic
        window.addEventListener('load', () => {
            const loader = document.getElementById('page-loader');
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 500);
        });


    </script>
</body>
</html>
