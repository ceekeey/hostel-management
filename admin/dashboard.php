<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// 1. Total Students
$res_students = mysqli_query($conn, "SELECT COUNT(id) as total FROM users WHERE role='student'");
$total_students = mysqli_fetch_assoc($res_students)['total'] ?? 0;

// 2. Pending Applications
$res_apps = mysqli_query($conn, "SELECT COUNT(id) as total FROM applications WHERE status='pending'");
$pending_apps = mysqli_fetch_assoc($res_apps)['total'] ?? 0;

// 3. Available Beds (Capacity - Current Occupancy across all active rooms)
$res_beds = mysqli_query($conn, "SELECT SUM(capacity - current_occupancy) as available FROM rooms WHERE status != 'maintenance'");
$available_beds = mysqli_fetch_assoc($res_beds)['available'] ?? 0;

// 4. Total Expected Revenue (vs Actually Paid ideally, but let's just do Total Successfully Paid)
$res_rev = mysqli_query($conn, "SELECT SUM(amount) as revenue FROM payments WHERE status='paid'");
$total_revenue = mysqli_fetch_assoc($res_rev)['revenue'] ?? 0;

// Recent Action Activity (Last 5 Complaints)
$res_comp = mysqli_query($conn, "
    SELECT c.*, u.fullname 
    FROM complaints c 
    JOIN users u ON c.student_id = u.id 
    ORDER BY c.created_at DESC LIMIT 5
");
$recent_complaints = [];
if($res_comp) {
    while($row = mysqli_fetch_assoc($res_comp)) {
        $recent_complaints[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HostelSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10 w-full">
        
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-200 z-10">
            <div class="flex items-center gap-4">
                <button id="open-sidebar" class="md:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">System Overview</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                    <div class="text-right">
                        <p class="font-bold text-dark text-sm"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
                        <p class="text-primary text-xs font-bold uppercase tracking-wider">Administrator</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-100/50">
            
            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                
                <!-- Metric 1 -->
                <div class="bg-white rounded-2xl animate__animated animate__fadeInUp p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Total Students</p>
                        <h3 class="text-3xl font-black text-dark"><?php echo number_format($total_students); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center shadow-inner">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Metric 2 -->
                <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-1s p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Available Beds</p>
                        <h3 class="text-3xl font-black text-dark"><?php echo number_format($available_beds); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-inner">
                        <i data-lucide="bed-single" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Metric 3 -->
                <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-2s p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pending Apps</p>
                        <h3 class="text-3xl font-black text-dark"><?php echo number_format($pending_apps); ?></h3>
                        <?php if($pending_apps > 0): ?>
                            <a href="allocations.php" class="text-[10px] font-bold text-primary uppercase inline-flex items-center gap-1 mt-2 hover:underline">Review Now <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="w-12 h-12 bg-warning/10 text-warning rounded-xl flex items-center justify-center shadow-inner relative z-10">
                        <i data-lucide="file-clock" class="w-6 h-6"></i>
                    </div>
                    <?php if($pending_apps > 0): ?>
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-warning/5 rounded-full animate-pulse z-0"></div>
                    <?php endif; ?>
                </div>

                <!-- Metric 4 -->
                <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-3s p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Revenue Generated</p>
                        <h3 class="text-2xl font-black text-success font-mono">₦<?php echo number_format($total_revenue); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-success/10 text-success rounded-xl flex items-center justify-center shadow-inner">
                        <i data-lucide="banknote" class="w-6 h-6"></i>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Administrative Actions -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-4s p-6 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark text-lg mb-6">Quick Actions</h3>
                        
                        <div class="space-y-4">
                            <a href="manage_rooms.php" class="block w-full text-left p-4 rounded-xl border border-gray-100 hover:border-primary/30 hover:bg-blue-50/50 transition group">
                                <div class="flex items-center gap-3 mb-1">
                                    <i data-lucide="plus-square" class="w-5 h-5 text-primary group-hover:scale-110 transition shrink-0"></i>
                                    <span class="font-bold text-dark text-sm">Add New Hostel Room</span>
                                </div>
                                <p class="text-xs text-gray-500 ml-8">Expand campus capacity</p>
                            </a>
                            
                            <a href="allocations.php" class="block w-full text-left p-4 rounded-xl border border-gray-100 hover:border-indigo-500/30 hover:bg-indigo-50/50 transition group">
                                <div class="flex items-center gap-3 mb-1">
                                    <i data-lucide="users" class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition shrink-0"></i>
                                    <span class="font-bold text-dark text-sm">Review Pending Applicants</span>
                                </div>
                                <p class="text-xs text-gray-500 ml-8">Allocate bedrooms manually</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets Panel -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-5s shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-dark text-lg">Recent Support Tickets</h3>
                            <button class="text-xs font-bold text-primary bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">View All</button>
                        </div>
                        
                        <div class="divide-y divide-gray-50">
                            <?php if(empty($recent_complaints)): ?>
                                <div class="p-10 text-center">
                                    <p class="text-gray-400 font-medium">No active student complaints filed.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($recent_complaints as $c): ?>
                                    <div class="p-5 flex flex-col sm:flex-row gap-4 sm:items-center justify-between hover:bg-gray-50/50 transition">
                                        <div>
                                            <h4 class="font-bold text-dark text-sm mb-1"><?php echo htmlspecialchars($c['subject']); ?></h4>
                                            <p class="text-xs text-gray-500 truncate max-w-sm mb-1.5"><?php echo htmlspecialchars($c['message']); ?></p>
                                            <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
                                                <span><i data-lucide="user" class="inline w-3 h-3 text-gray-400"></i> <?php echo htmlspecialchars($c['fullname']); ?></span>
                                                <span><i data-lucide="clock" class="inline w-3 h-3 text-gray-400"></i> <?php echo date('M j, Y', strtotime($c['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 flex sm:flex-col items-center gap-2 sm:items-end">
                                            <?php 
                                                $s = $c['status'];
                                                if($s == 'open') echo '<span class="badge bg-warning/20 text-warning px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Open</span>';
                                                elseif($s == 'in_progress') echo '<span class="badge bg-primary/20 text-primary px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">WIP</span>';
                                                else echo '<span class="badge bg-success/20 text-success px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Resolved</span>';
                                            ?>
                                            <button class="text-xs font-bold text-primary hover:underline">Inspect</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
