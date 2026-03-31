<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

$student_id = $_GET['id'] ?? 0;

if ($student_id <= 0) {
    header("Location: manage_students.php");
    exit();
}

// Fetch student details
$student_sql = "SELECT * FROM users WHERE id = ? AND role = 'student'";
$stmt = mysqli_prepare($conn, $student_sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: manage_students.php");
    exit();
}

// Fetch active allocation
$alloc_sql = "
    SELECT a.*, r.room_number, r.block_name, r.room_type 
    FROM allocations a 
    JOIN rooms r ON a.room_id = r.id 
    WHERE a.student_id = ? AND a.status = 'active' 
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $alloc_sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$allocation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Fetch payment history
$payments_sql = "SELECT * FROM payments WHERE student_id = ? ORDER BY paid_at DESC";
$stmt = mysqli_prepare($conn, $payments_sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$payments_res = mysqli_stmt_get_result($stmt);
$payments = [];
while ($row = mysqli_fetch_assoc($payments_res)) {
    $payments[] = $row;
}
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - <?php echo htmlspecialchars($student['fullname']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: { primary: '#16a34a', dark: '#212529', light: '#f8f9fa', success: '#198754' }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-10 border-b border-gray-200 z-10 shrink-0">
            <div class="flex items-center gap-4">
                <a href="manage_students.php" class="p-2 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-primary transition">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </a>
                <h1 class="text-2xl font-bold text-dark tracking-tight">Student Profile</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border border-primary/20">Verified Admin</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-10 bg-gray-100/30">
            <div class="max-w-5xl mx-auto space-y-8 pb-20">
                
                <!-- Profile Header Card -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 md:p-12 relative overflow-hidden group">
                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-primary/5 rounded-full group-hover:scale-110 transition-transform duration-700"></div>
                    
                    <div class="flex flex-col md:flex-row gap-10 items-center md:items-start relative z-10">
                        <div class="w-32 h-32 rounded-3xl bg-primary text-white flex items-center justify-center text-5xl font-black shadow-xl shadow-green-500/20 ring-4 ring-white">
                            <?php echo strtoupper(substr($student['fullname'], 0, 1)); ?>
                        </div>
                        <div class="flex-1 text-center md:text-left space-y-4">
                            <div>
                                <h2 class="text-4xl font-black text-dark tracking-tight mb-1"><?php echo htmlspecialchars($student['fullname']); ?></h2>
                                <p class="text-gray-400 font-medium flex items-center justify-center md:justify-start gap-2">
                                    <i data-lucide="mail" class="w-4 h-4"></i> <?php echo htmlspecialchars($student['email']); ?>
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                                <?php if($student['profile_completed']): ?>
                                    <span class="bg-green-50 text-green-700 px-4 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 border border-green-100">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Profile Complete
                                    </span>
                                <?php else: ?>
                                    <span class="bg-red-50 text-red-600 px-4 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 border border-red-100">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Incomplete Profile
                                    </span>
                                <?php endif; ?>
                                
                                <span class="bg-gray-100 text-gray-600 px-4 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 border border-gray-200">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i> Joined <?php echo date('M Y', strtotime($student['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Academic & Contact Stats -->
                    <div class="lg:col-span-1 space-y-8">
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 space-y-6">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-primary"></i> Information Detail
                            </h4>
                            <div class="space-y-6">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Faculty</p>
                                    <p class="text-sm font-bold text-dark"><?php echo htmlspecialchars($student['faculty'] ?? 'Not set'); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Department</p>
                                    <p class="text-sm font-bold text-dark"><?php echo htmlspecialchars($student['department'] ?? 'Not set'); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Phone Number</p>
                                    <p class="text-sm font-bold text-dark"><?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Allocation Summary -->
                        <div class="bg-gray-900 rounded-[2rem] shadow-xl p-8 text-white relative overflow-hidden">
                            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/5 rounded-full"></div>
                            <h4 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-6 relative z-10">Accommodation Status</h4>
                            <?php if($allocation): ?>
                                <div class="space-y-4 relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center">
                                            <i data-lucide="bed-double" class="w-6 h-6 text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="text-xl font-black tracking-tight"><?php echo htmlspecialchars($allocation['block_name']); ?></p>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Room <?php echo htmlspecialchars($allocation['room_number']); ?> (<?php echo $allocation['room_type']; ?>)</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-500 font-medium italic mt-4">Allocated on <?php echo date('M j, Y', strtotime($allocation['allocation_date'])); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="py-4 text-center">
                                    <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="door-closed" class="w-6 h-6 text-gray-600"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">No Active Room</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 h-full overflow-hidden flex flex-col">
                            <div class="p-8 border-b border-gray-50 flex justify-between items-center shrink-0">
                                <h4 class="text-lg font-black text-dark tracking-tighter flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-5 h-5 text-primary"></i> Payment Record
                                </h4>
                                <span class="bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg"><?php echo count($payments); ?> Entries</span>
                            </div>
                            
                            <div class="flex-1 overflow-x-auto">
                                <table class="w-full text-left border-collapse whitespace-nowrap">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-100">
                                            <th class="px-8 py-4">Ref Code</th>
                                            <th class="px-8 py-4">Amount</th>
                                            <th class="px-8 py-4 text-center">Status</th>
                                            <th class="px-8 py-4">Paid On</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 uppercase text-[11px] font-bold tracking-tight">
                                        <?php if(empty($payments)): ?>
                                            <tr>
                                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 font-medium italic">No payment transactions found for this student.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($payments as $p): ?>
                                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                                    <td class="px-8 py-5 font-mono text-dark"><?php echo $p['reference_code']; ?></td>
                                                    <td class="px-8 py-5 text-dark">₦<?php echo number_format($p['amount'], 2); ?></td>
                                                    <td class="px-8 py-5 text-center">
                                                        <?php if($p['status'] === 'paid'): ?>
                                                            <span class="text-success bg-green-50 px-3 py-1 rounded-full border border-green-100">Paid</span>
                                                        <?php else: ?>
                                                            <span class="text-red-500 bg-red-50 px-3 py-1 rounded-full border border-red-100"><?php echo ucfirst($p['status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-8 py-5 text-gray-500">
                                                        <?php echo $p['paid_at'] ? date('d M, Y', strtotime($p['paid_at'])) : '---'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
