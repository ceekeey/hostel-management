<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// Fetch all payments joined with student full name
$payments_sql = "
    SELECT p.*, u.fullname, u.email, r.room_number, r.block_name
    FROM payments p
    JOIN users u ON p.student_id = u.id
    LEFT JOIN allocations a ON p.allocation_id = a.id
    LEFT JOIN rooms r ON a.room_id = r.id
    ORDER BY p.paid_at DESC
";
$payments_res = mysqli_query($conn, $payments_sql);

$payments = [];
if ($payments_res) {
    while ($row = mysqli_fetch_assoc($payments_res)) {
        $payments[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Ledger - Hostelio Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: { primary: '#16a34a', dark: '#212529', light: '#f8f9fa' }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-200 z-10 shrink-0">
            <div class="flex items-center gap-4 text-dark font-black tracking-tighter uppercase text-xl">
                <i data-lucide="receipt" class="w-8 h-8 text-primary"></i>
                <h1 class="hidden sm:block">Transaction Ledger</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-gray-100 rounded-xl text-[10px] font-black uppercase text-gray-500 tracking-widest border border-gray-200 shadow-sm">
                    Master Admin Access
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-100/50">
            
            <div class="max-w-7xl mx-auto space-y-8 animate__animated animate__fadeIn">
                
                <!-- Summary Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Managed Revenue</span>
                        <h3 class="text-3xl font-black text-dark tracking-tighter">
                            ₦<?php 
                            $totalProfit = array_sum(array_column($payments, 'amount'));
                            echo number_format($totalProfit, 2); 
                            ?>
                        </h3>
                    </div>
                    <div class="bg-primary p-8 rounded-[2rem] shadow-xl shadow-green-500/20 text-white flex flex-col relative overflow-hidden">
                        <div class="absolute -bottom-5 -right-5 w-24 h-24 bg-white/10 rounded-full"></div>
                        <span class="text-[10px] font-black text-green-100 uppercase tracking-widest mb-2 relative z-10">Successful Collections</span>
                        <h3 class="text-3xl font-black tracking-tighter relative z-10"><?php echo count($payments); ?> Receipts</h3>
                    </div>
                    <div class="bg-gray-900 p-8 rounded-[2rem] shadow-sm text-white flex flex-col">
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">System Status</span>
                        <h3 class="text-3xl font-black tracking-tighter flex items-center gap-3">
                            Online <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-lg shadow-green-500/50"></span>
                        </h3>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row justify-between items-center gap-6 bg-gray-50/30">
                        <div>
                            <h4 class="text-xl font-black text-dark tracking-tighter">All Student Payments</h4>
                            <p class="text-xs text-gray-400 font-medium">Verified historical transaction data across all hostel wings.</p>
                        </div>
                        <div class="relative w-full md:w-80">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                            <input type="text" placeholder="Filter by name or reference..." 
                                   class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary/50 transition shadow-sm placeholder:text-gray-300">
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-white/50 text-gray-400 text-[10px] font-extrabold uppercase tracking-widest border-b border-gray-50">
                                    <th class="px-8 py-5"># Reference</th>
                                    <th class="px-8 py-5">Full Name</th>
                                    <th class="px-8 py-5">Hostel Wing</th>
                                    <th class="px-8 py-5">Paid Amount</th>
                                    <th class="px-8 py-5 text-center">Status</th>
                                    <th class="px-8 py-5">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 uppercase text-[11px] font-bold tracking-tight text-gray-600">
                                <?php if(empty($payments)): ?>
                                    <tr>
                                        <td colspan="6" class="p-20 text-center text-gray-300 italic font-medium">No transactions found in the database.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($payments as $p): ?>
                                        <tr class="hover:bg-gray-50/80 transition duration-150">
                                            <td class="px-8 py-6 font-mono text-primary"><?php echo $p['reference_code']; ?></td>
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col">
                                                    <span class="text-dark font-black tracking-tighter"><?php echo htmlspecialchars($p['fullname']); ?></span>
                                                    <span class="text-[9px] text-gray-400 lowercase font-medium"><?php echo htmlspecialchars($p['email']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <?php if($p['block_name']): ?>
                                                    <div class="flex items-center gap-2">
                                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[9px]"><?php echo $p['block_name']; ?></span>
                                                        <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-[9px]">Rm <?php echo $p['room_number']; ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-400 italic">No Allocation</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-8 py-6 font-mono text-dark text-sm">₦<?php echo number_format($p['amount'], 2); ?></td>
                                            <td class="px-8 py-6 text-center">
                                                <span class="inline-flex items-center gap-1.5 bg-green-500/10 text-green-600 px-3 py-1 rounded-full border border-green-500/20 shadow-sm animate-pulse">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                    <?php echo strtoupper($p['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-6 text-gray-400 tabular-nums">
                                                <?php echo date('M d, Y - H:i', strtotime($p['paid_at'])); ?>
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
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
