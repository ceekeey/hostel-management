<?php
require_once '../includes/auth.php';
require_role('student');

// Ensure profile is completed
if (!isset($_SESSION['profile_completed']) || $_SESSION['profile_completed'] == 0) {
    header("Location: update_profile.php");
    exit();
}

require_once '../config/database.php';
require_once '../config/paystack.php';
$user_id = $_SESSION['user_id'];

// Fetch user email for Paystack
$user_sql = "SELECT email FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_res = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_res);
$email = $user_data['email'] ?? '';
mysqli_stmt_close($user_stmt);

// 1. Check if they have an active allocation to attach the payment to
$alloc_sql = "SELECT a.id, a.room_id, r.room_number, r.block_name, r.room_type 
              FROM allocations a 
              JOIN rooms r ON a.room_id = r.id 
              WHERE a.student_id = ? AND a.status = 'active' LIMIT 1";
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
    <title>Make Payment - Hostelio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Paystack Inline JS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>

<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Reusable Sidebar -->
    <?php include '../includes/student_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10">

        <header
            class="h-20 bg-white/80 backdrop-blur-md shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-100 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <button
                    class="md:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
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
            if (isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "Swal.fire({icon: 'error', title: 'Payment Failed', text: '$errorMsg', confirmButtonColor: '#0d6efd'});";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Payment Successful!', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']);
            }
            ?>

            <div class="max-w-3xl mx-auto">

                <?php if (!$allocation): ?>
                    <!-- Cannot pay without allocation -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center mt-10">
                        <div
                            class="w-24 h-24 mx-auto rounded-full bg-gray-50 text-gray-300 border border-gray-200 flex items-center justify-center mb-6">
                            <i data-lucide="shield-alert" class="w-12 h-12"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-dark mb-4">Action Required</h2>
                        <p class="text-gray-500 mb-8 max-w-lg mx-auto">You cannot make a hostel payment because no bed space
                            has been assigned to you yet. Administrative allocation must be completed first.</p>

                        <a href="my_room.php"
                            class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-dark font-bold py-3 px-8 rounded-xl transition">
                            Check Allocation Status
                        </a>
                    </div>

                <?php else: ?>

                    <?php if ($payment && $payment['status'] === 'paid'): ?>

                        <!-- Official Paid Invoice -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-12" id="receipt-container">
                            <!-- Receipt Content for PDF -->
                            <div id="receipt-content" class="bg-white p-4">
                                <!-- Header -->
                                <div class="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-primary flex items-center justify-center rounded-xl text-white">
                                            <i data-lucide="building-2" class="w-7 h-7"></i>
                                        </div>
                                        <div>
                                            <h2 class="text-2xl font-black text-dark tracking-tighter uppercase">Hostel<span
                                                    class="text-primary">io</span></h2>
                                            <p
                                                class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">
                                                Official Payment Receipt</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div
                                            class="px-4 py-1.5 bg-green-500 text-white rounded-lg inline-flex items-center gap-2 mb-2">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            <span class="text-xs font-black uppercase tracking-wider">Verified Paid</span>
                                        </div>
                                        <p class="text-[10px] text-gray-400 font-mono tracking-tighter">ID:
                                            <?php echo date('Ymd'); ?>-<?php echo str_pad($payment['id'], 5, '0', STR_PAD_LEFT); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="grid grid-cols-2 gap-12 mb-10">
                                    <div>
                                        <p
                                            class="text-[10px] text-gray-400 uppercase tracking-widest font-black mb-3 border-l-4 border-primary pl-2">
                                            Payer Information</p>
                                        <h3 class="text-lg font-bold text-dark leading-tight">
                                            <?php echo htmlspecialchars($_SESSION['fullname']); ?></h3>
                                        <p class="text-sm text-gray-500 font-medium">
                                            <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                        <p class="text-xs text-gray-400 mt-2 italic font-semibold">Allocated Room: <span
                                                class="text-dark not-italic"><?php echo htmlspecialchars($allocation['block_name']); ?>
                                                - Room <?php echo htmlspecialchars($allocation['room_number']); ?></span></p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-[10px] text-gray-400 uppercase tracking-widest font-black mb-3 border-r-4 border-gray-200 pr-2">
                                            Transaction Details</p>
                                        <p class="text-sm font-bold text-dark">Ref: <span
                                                class="font-mono text-primary"><?php echo htmlspecialchars($payment['reference_code']); ?></span>
                                        </p>
                                        <p class="text-sm text-gray-500 font-medium">Date:
                                            <?php echo date('d M, Y', strtotime($payment['paid_at'])); ?></p>
                                        <p class="text-sm text-gray-500 font-medium">Method:
                                            <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="overflow-hidden rounded-2xl border border-gray-100 mb-10 shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th
                                                    class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                                    Description</th>
                                                <th
                                                    class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">
                                                    Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-50 uppercase text-xs font-bold text-gray-600 tracking-tight">
                                            <tr>
                                                <td class="px-6 py-5">
                                                    Annual Hostel Maintenance & Accommodation Fee
                                                    <p class="text-[10px] text-primary mt-1 font-black leading-none">Session:
                                                        2025 / 2026 Academic Period</p>
                                                </td>
                                                <td class="px-6 py-5 text-right font-mono text-dark">
                                                    ₦<?php echo number_format($payment['amount'], 2); ?></td>
                                            </tr>
                                            <tr class="bg-green-50/30">
                                                <td
                                                    class="px-6 py-6 text-right font-black text-gray-500 uppercase tracking-widest">
                                                    Total Paid</td>
                                                <td
                                                    class="px-6 py-6 text-right text-2xl font-black text-primary font-mono tracking-tighter">
                                                    ₦<?php echo number_format($payment['amount'], 2); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Footer Note -->
                                <div class="flex justify-between items-end">
                                    <div class="max-w-[250px]">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase leading-relaxed mb-4 italic">
                                            This is an electronically generated receipt. No physical signature is required for
                                            validity. Please keep this for your records.</p>
                                        <div
                                            class="w-20 h-20 border-2 border-gray-100 rounded-xl flex items-center justify-center bg-gray-50 group">
                                            <i data-lucide="qr-code"
                                                class="w-12 h-12 text-gray-200 group-hover:text-primary transition-colors"></i>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <div class="w-32 h-1 bg-gray-100 rounded-full mb-2"></div>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Bursar's
                                            Office</p>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Hostelio-Alloc-<?php echo $allocation['id']; ?>&bgcolor=ffffff&color=212529" alt="Room QR Code" class="w-full h-full object-contain mix-blend-multiply">
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-center flex-wrap gap-4 mt-12 pt-8 border-t border-gray-100 no-print">

                                <button onclick="downloadReceipt()"
                                    class="flex items-center gap-2 bg-primary hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition cursor-pointer shadow-lg shadow-primary/20">
                                    <i data-lucide="download" class="w-5 h-5"></i> Download PDF
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
                            <div class="bg-green-50/50 rounded-xl p-6 border border-green-100 mb-8">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 bg-white rounded-xl shadow-sm border border-green-100 flex items-center justify-center text-primary">
                                            <i data-lucide="bed-double" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-dark">Annual Bed Space Fee</p>
                                            <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">Allocation
                                                #<?php echo $allocation['id']; ?></p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-dark font-mono">
                                            ₦<?php echo number_format($base_fee_ngn, 2); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Paystack Payment Form -->
                            <form id="payForm">
                                <input type="hidden" id="email-address" value="<?php echo htmlspecialchars($email); ?>">
                                <input type="hidden" id="amount" value="<?php echo $base_fee_ngn; ?>">
                                <input type="hidden" id="allocation-id" value="<?php echo $allocation['id']; ?>">

                                <div class="border-t border-gray-100 pt-8 mt-4">
                                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                                        <div class="flex items-center gap-2 text-gray-400">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                            <span class="text-xs font-medium uppercase tracking-widest">SSL Secured Paystack
                                                Transaction</span>
                                        </div>
                                        <button type="button" onclick="payWithPaystack()" id="submitBtn"
                                            class="w-full sm:w-auto flex justify-center items-center gap-2 bg-[#092a49] hover:bg-[#06182c] text-white font-bold py-4 px-10 rounded-xl transition duration-200 shadow-lg hover:shadow-xl hover:-translate-y-1">
                                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                                            <span>Pay ₦<?php echo number_format($base_fee_ngn, 2); ?> Now</span>
                                            <svg id="btnSpinner" class="animate-spin -ml-1 text-white hidden w-5 h-5"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
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

        function downloadReceipt() {
            const element = document.getElementById('receipt-content');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Hostel_Receipt_<?php echo $payment['reference_code']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
        }

        function payWithPaystack() {
            const email = document.getElementById('email-address').value;
            const amount = document.getElementById('amount').value;
            const allocId = document.getElementById('allocation-id').value;

            const btn = document.getElementById('submitBtn');
            const spin = document.getElementById('btnSpinner');
            const text = btn.querySelector('span');

            // Show loading
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            spin.classList.remove('hidden');
            text.textContent = "Opening Checkout...";

            let handler = PaystackPop.setup({
                key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
                email: email,
                amount: amount * 100, // Amount in kobo
                currency: "NGN",
                ref: 'HOSTEL-' + Math.floor((Math.random() * 1000000000) + 1),
                callback: function (response) {
                    // Payment successful
                    window.location.href = "../actions/verify_payment.php?reference=" + response.reference + "&alloc_id=" + allocId;
                },
                onClose: function () {
                    // Payment closed
                    btn.disabled = false;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                    spin.classList.add('hidden');
                    text.textContent = "Pay ₦" + Number(amount).toLocaleString() + " Now";

                    Swal.fire({
                        icon: 'info',
                        title: 'Cancelled',
                        text: 'You cancelled the payment process.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
            handler.openIframe();
        }
    </script>
</body>

</html>