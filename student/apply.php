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

// Check if the student has already applied
$check_sql = "SELECT * FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $check_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing_app = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch available blocks from the rooms table to dynamically populate the dropdown
$blocks_sql = "SELECT DISTINCT block_name FROM rooms WHERE status = 'available'";
$blocks_res = mysqli_query($conn, $blocks_sql);
$blocks = [];
if ($blocks_res) {
    while ($row = mysqli_fetch_assoc($blocks_res)) {
        $blocks[] = $row['block_name'];
    }
}
// Fallback if no rooms are in the database yet
if (empty($blocks)) {
    $blocks = ['Block A', 'Block B', 'Block C'];
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Hostel - Hostelio</title>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 font-sans text-dark flex h-screen overflow-hidden antialiased">

    <!-- Reusable Sidebar (Same exact layout as dashboard) -->
    <?php include '../includes/student_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10">
        
        <!-- Reusable Top Navbar -->
        <header class="h-20 bg-white/80 backdrop-blur-md shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-100 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-400 hover:text-primary transition focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Hostel Application</h1>
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

        <!-- Main Form Area -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            
            <?php 
            // Handle SweetAlert injections
            $alertScript = "";
            if(isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "Swal.fire({icon: 'error', title: 'Error', text: '$errorMsg', confirmButtonColor: '#0d6efd'});";
                unset($_SESSION['error']); 
            }
            if(isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Success!', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']); 
            }
            ?>

            <div class="max-w-3xl mx-auto">
                
                <?php if ($existing_app && $existing_app['status'] != 'rejected'): ?>
                    
                    <!-- Has Already Applied (Pending or Approved) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                        <div class="w-20 h-20 mx-auto rounded-full 
                            <?php 
                                if($existing_app['status'] == 'pending') echo 'bg-warning/20 text-warning';
                                else if($existing_app['status'] == 'approved') echo 'bg-success/20 text-success';
                            ?> flex items-center justify-center mb-6">
                            <?php 
                                if($existing_app['status'] == 'pending') echo '<i data-lucide="clock" class="w-10 h-10"></i>';
                                else if($existing_app['status'] == 'approved') echo '<i data-lucide="check-circle" class="w-10 h-10"></i>';
                            ?>
                        </div>
                        <h2 class="text-3xl font-bold text-dark mb-4">Application <?php echo ucfirst($existing_app['status']); ?></h2>
                        
                        <p class="text-gray-500 mb-8 max-w-lg mx-auto">
                            <?php if($existing_app['status'] == 'pending'): ?>
                                Your application for a <?php echo htmlspecialchars($existing_app['room_type']); ?> room in 
                                <?php echo htmlspecialchars($existing_app['preferred_block']); ?> is currently being reviewed by the administration.
                            <?php elseif($existing_app['status'] == 'approved'): ?>
                                Congratulations! Your application was approved. Please proceed to checking your room allocation and making payment.
                            <?php endif; ?>
                        </p>
                        
                        <?php if($existing_app['status'] == 'approved'): ?>
                            <a href="my_room.php" class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition shadow-md">
                                View Allocation <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    
                    <!-- Form Area (Shown if No App OR Rejected App) -->
                    <div id="form-container">
                        
                        <?php if ($existing_app && $existing_app['status'] == 'rejected'): ?>
                            <!-- Rejection Notice -->
                            <div class="bg-red-50 border border-red-100 rounded-2xl p-8 mb-8 text-center animate__animated animate__shakeX">
                                <div class="w-16 h-16 mx-auto rounded-full bg-danger/20 text-danger flex items-center justify-center mb-4">
                                    <i data-lucide="x-circle" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-xl font-bold text-danger mb-2">Previous Application Rejected</h3>
                                <p class="text-gray-600 mb-6 text-sm">Unfortunately, your previous request was not successful. However, you are welcome to submit a **new** application below for reconsiderations or different preferences.</p>
                                <div class="w-12 h-1 bg-red-200 mx-auto rounded-full"></div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-8">
                            <h2 class="text-3xl font-bold text-dark mb-2 tracking-tight">
                                <?php echo ($existing_app && $existing_app['status'] == 'rejected') ? 'Submit New Application' : 'Request an Accommodation'; ?>
                            </h2>
                            <p class="text-gray-500 font-medium">Please select your preferences below. Applications are subject to administrative review and availability.</p>
                        </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <form action="../actions/apply_action.php" method="POST" id="appForm" class="p-8">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div>
                                    <label for="preferred_block" class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Preferred Block</label>
                                    <select name="preferred_block" id="preferred_block" required
                                        class="w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Select a Block --</option>
                                        <?php foreach($blocks as $block): ?>
                                            <option value="<?php echo htmlspecialchars($block); ?>"><?php echo htmlspecialchars($block); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="room_type" class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Room Type</label>
                                    <select name="room_type" id="room_type" required
                                        class="w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition appearance-none cursor-pointer">
                                        <option value="Standard" selected>Standard (4 Students/Room)</option>
                                        <option value="Premium">Premium (2 Students/Room)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Read-only consent block -->
                            <div class="bg-green-50/50 rounded-xl p-5 border border-green-100 mb-8 flex gap-4">
                                <div class="text-primary mt-0.5"><i data-lucide="info" class="w-5 h-5"></i></div>
                                <div>
                                    <h4 class="text-sm font-bold text-dark mb-1">Terms of Application</h4>
                                    <p class="text-sm text-gray-600">By submitting this application, you agree to adhere to the university's housing guidelines. Room allocations are completely randomized within selected blocks based on capacity limits.</p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" id="submitBtn" class="flex justify-center items-center gap-2 bg-primary hover:bg-green-700 text-white font-bold py-3.5 px-8 rounded-xl transition duration-200 shadow-md hover:-translate-y-0.5 w-full md:w-auto">
                                    <span>Submit Application</span>
                                    <svg id="btnSpinner" class="animate-spin -ml-1 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

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
        const form = document.getElementById('appForm');
        if(form) {
            form.addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                const text = btn.querySelector('span');
                const spin = document.getElementById('btnSpinner');
                // Don't disable immediately to preserve POST logic, or use a tiny timeout
                setTimeout(() => {
                    btn.classList.add('opacity-80', 'cursor-not-allowed');
                    spin.classList.remove('hidden');
                    text.textContent = "Submitting...";
                }, 10); // 10ms ensures the synchronous form post continues flawlessly
            });
        }
    </script>
</body>
</html>
