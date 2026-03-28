<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// Fetch all students and left join with their active allocation
$students_sql = "
    SELECT u.id, u.fullname, u.email, u.phone, u.faculty, u.department, u.profile_completed, u.created_at,
           a.status as allocation_status, r.block_name, r.room_number 
    FROM users u 
    LEFT JOIN allocations a ON u.id = a.student_id AND a.status = 'active'
    LEFT JOIN rooms r ON a.room_id = r.id
    WHERE u.role = 'student' 
    ORDER BY u.created_at DESC
";
$students_res = mysqli_query($conn, $students_sql);

$students = [];
if($students_res) {
    while($row = mysqli_fetch_assoc($students_res)) {
        $students[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - HostelSys</title>
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
                <button id="open-sidebar" class="md:hidden text-gray-400 hover:text-primary transition p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Registered Students</h1>
            </div>
            <div class="flex items-center gap-4">
                <p class="font-bold text-dark text-sm"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-100/50">
            
            <div class="bg-white rounded-2xl animate__animated animate__fadeInUp shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" placeholder="Search students..." class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 transition w-64">
                        </div>
                    </div>
                    <span class="bg-primary/10 text-primary font-bold px-3 py-1 rounded-full text-xs"><?php echo count($students); ?> Registered</span>
                </div>
                
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-4 font-bold">Student Identity</th>
                                <th class="px-6 py-4 font-bold">Contact</th>
                                <th class="px-6 py-4 font-bold">Academic Detail</th>
                                <th class="px-6 py-4 font-bold">Hostel Status</th>
                                <th class="px-6 py-4 font-bold">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if(empty($students)): ?>
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-400 font-medium">No students registered in the system yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($students as $s): ?>
                                    <tr class="hover:bg-blue-50/30 transition">
                                        
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                                    <?php echo strtoupper(substr($s['fullname'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-dark text-sm"><?php echo htmlspecialchars($s['fullname']); ?></p>
                                                    <?php if($s['profile_completed'] == 1): ?>
                                                        <p class="text-[10px] font-bold text-success uppercase tracking-widest flex items-center gap-1 mt-0.5"><i data-lucide="check-circle" class="w-3 h-3"></i> Profile Verified</p>
                                                    <?php else: ?>
                                                        <p class="text-[10px] font-bold text-warning uppercase tracking-widest flex items-center gap-1 mt-0.5"><i data-lucide="alert-triangle" class="w-3 h-3"></i> Incomplete</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-gray-600 mb-0.5"><i data-lucide="mail" class="inline w-3.5 h-3.5 text-gray-400 mr-1"></i> <?php echo htmlspecialchars($s['email']); ?></p>
                                            <p class="text-xs text-gray-500"><i data-lucide="phone" class="inline w-3.5 h-3.5 text-gray-400 mr-1"></i> <?php echo htmlspecialchars($s['phone']); ?></p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <?php if($s['profile_completed'] == 1): ?>
                                                <p class="text-sm font-bold text-dark mb-0.5"><?php echo htmlspecialchars($s['department']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($s['faculty']); ?></p>
                                            <?php else: ?>
                                                <span class="text-xs italic text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-6 py-4">
                                            <?php if($s['allocation_status'] === 'active'): ?>
                                                <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-100 rounded-lg px-2.5 py-1 text-xs">
                                                    <i data-lucide="bed-double" class="w-3.5 h-3.5 text-indigo-500"></i>
                                                    <span class="font-bold text-indigo-700"><?php echo htmlspecialchars($s['block_name']) . " Room " . htmlspecialchars($s['room_number']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="bg-gray-100 text-gray-500 border border-gray-200 px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Unallocated</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-6 py-4 text-xs font-medium text-gray-500">
                                            <?php echo date('M j, Y', strtotime($s['created_at'])); ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
