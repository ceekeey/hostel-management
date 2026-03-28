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

// 1. Check if they have an active allocation to attach the payment to
$alloc_sql = "SELECT id, room_id FROM allocations WHERE student_id = ? AND status = 'active' LIMIT 1";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $alloc_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$alloc_res = mysqli_stmt_get_result($stmt);
$allocation = mysqli_fetch_assoc($alloc_res);
mysqli_stmt_close($stmt);

// 2. Fetch specific payment records for this student
$pay_sql = "SELECT * FROM payments WHERE student_id = ? ORDER BY id DESC LIMIT 1";
$pay_stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($pay_stmt, $pay_sql);
mysqli_stmt_bind_param($pay_stmt, "i", $user_id);
mysqli_stmt_execute($pay_stmt);
$pay_res = mysqli_stmt_get_result($pay_stmt);
$payment = mysqli_fetch_assoc($pay_res);
mysqli_stmt_close($pay_stmt);

// Define Base Fees (Could be dynamic from DB but static for scale)
$base_fee_ngn = 150000;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - HostelSys</title>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Reusable Sidebar -->
    <?php include '../includes/student_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10">
        
        <header class="h-20 bg-white/80 backdrop-blur-md shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-100 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Hostel Fees</h1>
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

        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            
            <?php 
            $alertScript = "";
            if(isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "Swal.fire({icon: 'error', title: 'Payment Failed', text: '$errorMsg', confirmButtonColor: '#0d6efd'});";
                unset($_SESSION['error']); 
            }
            if(isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Payment Successful!', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']); 
            }
            ?>

            <div class="max-w-3xl mx-auto">
                
                <?php if (!$allocation): ?>
                    <!-- Cannot pay without allocation -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center mt-10">
                        <div class="w-24 h-24 mx-auto rounded-full bg-gray-50 text-gray-300 border border-gray-200 flex items-center justify-center mb-6">
                            <i data-lucide="shield-alert" class="w-12 h-12"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-dark mb-4">Action Required</h2>
                        <p class="text-gray-500 mb-8 max-w-lg mx-auto">You cannot make a hostel payment because no bed space has been assigned to you yet. Administrative allocation must be completed first.</p>
                        
                        <a href="my_room.php" class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-dark font-bold py-3 px-8 rounded-xl transition">
                            Check Allocation Status
                        </a>
                    </div>
                
                <?php else: ?>
                    
                    <?php if ($payment && $payment['status'] === 'paid'): ?>
                        
                        <!-- Official Paid Invoice -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-12">
                            <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8">
                                <div>
                                    <h2 class="text-3xl font-bold text-dark mb-1">Official Receipt</h2>
                                    <p class="text-gray-400 font-mono text-sm">REF: <?php echo htmlspecialchars($payment['reference_code']); ?></p>
                                </div>
                                <div class="px-4 py-2 bg-success/10 text-success rounded-full flex items-center gap-2 mt-2">
                                    <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                                    <span class="text-sm font-bold tracking-wide uppercase">Paid in Full</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8 mb-10">
                                <div>
                                    <p class="text-gray-400 text-xs uppercase tracking-widest font-bold mb-1">Billed To</p>
                                    <p class="text-dark font-medium"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs uppercase tracking-widest font-bold mb-1">Date Paid</p>
                                    <p class="text-dark font-medium"><?php echo date('F j, Y', strtotime($payment['paid_at'])); ?></p>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200 mb-4 pb-4">
                                    <p class="font-semibold text-gray-700">Hostel Allocation Fee (Annual)</p>
                                    <p class="font-bold text-dark font-mono">₦<?php echo number_format($payment['amount'], 2); ?></p>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <p class="font-bold text-gray-500">Total Paid</p>
                                    <p class="text-2xl font-black text-success font-mono">₦<?php echo number_format($payment['amount'], 2); ?></p>
                                </div>
                            </div>

                            <div class="flex justify-center">
                                <button onclick="window.print()" class="flex items-center gap-2 text-primary hover:text-blue-800 font-medium transition cursor-pointer">
                                    <i data-lucide="printer" class="w-5 h-5"></i> Print Receipt
                                </button>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- Payment Gateway Checkout UI -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-dark mb-2">Secure Checkout</h2>
                                <p class="text-gray-500 text-sm">Complete your hostel accommodation fee payment below.</p>
                            </div>

                            <!-- Cart Summary -->
                            <div class="bg-blue-50/50 rounded-xl p-6 border border-blue-100 mb-8">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-blue-100 flex items-center justify-center text-primary">
                                            <i data-lucide="bed-double" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-dark">Annual Bed Space Fee</p>
                                            <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">Allocation #<?php echo $allocation['id']; ?></p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-dark font-mono">₦<?php echo number_format($base_fee_ngn, 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dummy Gateway Form -->
                            <form action="../actions/pay_action.php" method="POST" id="payForm">
                                <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                <input type="hidden" name="amount" value="<?php echo $base_fee_ngn; ?>">
                                
                                <div class="border-t border-gray-100 pt-8 mt-4">
                                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                                        <div class="flex items-center gap-2 text-gray-400">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                            <span class="text-xs font-medium uppercase tracking-widest">SSL Secured Transaction</span>
                                        </div>
                                        <button type="submit" id="submitBtn" class="w-full sm:w-auto flex justify-center items-center gap-2 bg-[#092a49] hover:bg-[#06182c] text-white font-bold py-4 px-10 rounded-xl transition duration-200 shadow-lg hover:shadow-xl hover:-translate-y-1">
                                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                                            <span>Pay ₦<?php echo number_format($base_fee_ngn, 2); ?> Now</span>
                                            <svg id="btnSpinner" class="animate-spin -ml-1 text-white hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        document.addEventListener("DOMContentLoaded", () => {
            <?php echo $alertScript; ?>
        });

        // Safe Loading Spinner Logic
        const form = document.getElementById('payForm');
        if(form) {
            form.addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                const text = btn.querySelector('span');
                const spin = document.getElementById('btnSpinner');
                // Give it a tiny delay to allow POST to submit the button's payload if needed (even though we're using a generic handler)
                setTimeout(() => {
                    btn.classList.add('opacity-90', 'cursor-not-allowed');
                    spin.classList.remove('hidden');
                    text.textContent = "Processing details...";
                }, 10);
            });
        }
    </script>
</body>
</html>
