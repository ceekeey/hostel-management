<?php
require_once '../includes/auth.php';
require_role('student');

// Ensure profile is completed
if (!isset($_SESSION['profile_completed']) || $_SESSION['profile_completed'] == 0) {
    header("Location: update_profile.php");
    exit();
}

require_once '../config/database.php';
$user_id = $_SESSION['user_id'];

// Check for active allocation
$alloc_sql = "
    SELECT a.*, r.block_name, r.room_number, r.room_type, r.capacity, r.current_occupancy 
    FROM allocations a 
    JOIN rooms r ON a.room_id = r.id 
    WHERE a.student_id = ? AND a.status = 'active' 
    LIMIT 1
";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $alloc_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$allocation = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Check if they have an active application to show contextual helpers
if (!$allocation) {
    $app_sql = "SELECT status FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1";
    $app_stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($app_stmt, $app_sql);
    mysqli_stmt_bind_param($app_stmt, "i", $user_id);
    mysqli_stmt_execute($app_stmt);
    $app_res = mysqli_stmt_get_result($app_stmt);
    $application = mysqli_fetch_assoc($app_res);
    mysqli_stmt_close($app_stmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Room - Hostelio</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: { primary: '#16a34a', dark: '#212529', light: '#f8f9fa', success: '#198754', warning: '#ffc107', danger: '#dc3545' }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Reusable Sidebar -->
    <?php include '../includes/student_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10">
        
        <!-- Top Navbar -->
        <header class="h-20 bg-white/80 backdrop-blur-md shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-100 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Accommodation Details</h1>
            </div>
            <div class="flex items-center gap-4 lg:gap-6">
                <!-- Profile Area -->
                <div class="flex items-center gap-3 pl-4 lg:pl-6 border-l border-gray-200 cursor-pointer group">
                    <div class="hidden md:block text-right">
                        <p class="font-bold text-dark text-sm leading-tight group-hover:text-primary transition">
                            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                        </p>
                        <p class="text-gray-400 text-xs mt-0.5 font-medium tracking-wide text-uppercase">Student</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            
            <?php if ($allocation): ?>
                
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-dark mb-2 tracking-tight">Your Hostel Room</h2>
                    <p class="text-gray-500 font-medium">Please verify your active bed space allocation below.</p>
                </div>

                <!-- Digital Room Key Card -->
                <div class="max-w-4xl bg-gradient-to-br from-green-600 to-green-700 rounded-3xl shadow-xl shadow-green-500/20 overflow-hidden relative">
                    <!-- Aesthetic SVG Overlays -->
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-40 h-40 bg-black/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative p-8 md:p-12 md:flex justify-between items-center h-full text-white">
                        <div>
                            <p class="text-green-100 font-bold tracking-widest uppercase text-sm mb-2">Block Details</p>
                            <h3 class="text-5xl font-black mb-6"><?php echo htmlspecialchars($allocation['block_name']); ?></h3>
                            
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6">
                                <div>
                                    <p class="text-green-200 text-xs uppercase tracking-widest font-semibold mb-1">Room Number</p>
                                    <p class="text-2xl font-bold"><?php echo htmlspecialchars($allocation['room_number']); ?></p>
                                </div>
                                <div>
                                    <p class="text-green-200 text-xs uppercase tracking-widest font-semibold mb-1">Room Type</p>
                                    <p class="text-2xl font-bold"><?php echo htmlspecialchars($allocation['room_type']); ?></p>
                                </div>
                                <div>
                                    <p class="text-green-200 text-xs uppercase tracking-widest font-semibold mb-1">Allocation ID</p>
                                    <p class="text-lg font-mono">#<?php echo str_pad($allocation['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <div>
                                    <p class="text-green-200 text-xs uppercase tracking-widest font-semibold mb-1">Current Occupancy</p>
                                    <p class="text-lg font-bold"><?php echo $allocation['current_occupancy'] . " / " . $allocation['capacity']; ?> Students</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 md:mt-0 flex flex-col items-center">
                            <div class="w-32 h-32 bg-white rounded-2xl p-4 shadow-lg flex items-center justify-center transform hover:scale-105 transition">
                                <!-- Placeholder QR Code for 'Digital Key' -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Hostelio-Alloc-<?php echo $allocation['id']; ?>&bgcolor=ffffff&color=212529" alt="Room QR Code" class="w-full h-full object-contain mix-blend-multiply">
                            </div>
                            <p class="text-green-200 text-xs font-semibold mt-4 tracking-widest uppercase text-center">Scan to verify<br>at security point</p>
                        </div>
                    </div>
                </div>

                <!-- Rules and Guide -->
                <div class="max-w-4xl mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-green-50 text-primary rounded-lg"><i data-lucide="shield-check" class="w-5 h-5"></i></div>
                            <h4 class="font-bold text-dark text-lg">Accommodation Policy</h4>
                        </div>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex gap-2 items-start"><i data-lucide="check" class="w-4 h-4 text-success flex-shrink-0 mt-0.5"></i> Rooms must be thoroughly cleaned daily.</li>
                            <li class="flex gap-2 items-start"><i data-lucide="check" class="w-4 h-4 text-success flex-shrink-0 mt-0.5"></i> Cooking with extremely high-voltage appliances is prohibited.</li>
                            <li class="flex gap-2 items-start"><i data-lucide="check" class="w-4 h-4 text-success flex-shrink-0 mt-0.5"></i> Any damage to hostel property will be strictly penalized.</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-red-50 text-danger rounded-lg"><i data-lucide="triangle-alert" class="w-5 h-5"></i></div>
                            <h4 class="font-bold text-dark text-lg">Having Issues?</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-6">If your allocated room infrastructure requires maintenance or there are roommate disputes, utilize the official complaint module.</p>
                        <a href="complaints.php" class="text-sm font-bold text-danger hover:underline inline-flex items-center gap-1">Submit a Complaint <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </div>
                </div>

            <?php else: ?>
                
                <!-- Not Allocated Yet -->
                <div class="max-w-2xl mx-auto mt-10 bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gray-50 text-gray-300 border border-gray-200 flex items-center justify-center mb-6">
                        <i data-lucide="bed-double" class="w-12 h-12"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-dark mb-4">No Room Allocated</h2>
                    
                    <p class="text-gray-500 mb-8">
                        <?php if (isset($application)): ?>
                            <?php if ($application['status'] === 'pending'): ?>
                                You have an existing <span class="badge badge-warning">Pending</span> application. The system will assign you a room shortly. Please check back later.
                            <?php elseif ($application['status'] === 'approved'): ?>
                                Your application was approved! However, an administrator has not officially allocated a bed space to your profile yet.
                            <?php elseif ($application['status'] === 'rejected'): ?>
                                Your previous application was <span class="text-danger font-bold">rejected</span>.
                            <?php endif; ?>
                        <?php else: ?>
                            You have not submitted a hostel accommodation application yet. You must apply first to receive an assigned bed space.
                        <?php endif; ?>
                    </p>
                    
                    <?php if (!isset($application) || $application['status'] === 'rejected'): ?>
                        <a href="apply.php" class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition shadow-md">
                            Start Application <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
