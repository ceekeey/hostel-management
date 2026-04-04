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

// Fetch all complaints submitted by this specific student
$comp_sql = "SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $comp_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$complaints_res = mysqli_stmt_get_result($stmt);

$complaints = [];
while ($row = mysqli_fetch_assoc($complaints_res)) {
    $complaints[] = $row;
}
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints - Hostelio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Maintenance & Complaints</h1>
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
                $alertScript = "Swal.fire({icon: 'error', title: 'Action Failed', text: '$errorMsg', confirmButtonColor: '#0d6efd'});";
                unset($_SESSION['error']); 
            }
            if(isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Ticket Submitted!', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']); 
            }
            ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                
                <!-- Submission Form (1/3 Width) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-dark mb-2">New Ticket</h2>
                        <p class="text-gray-500 text-sm mb-6">Need maintenance or reporting an issue? Create a new complaint below.</p>
                        
                        <form action="../actions/complaint_action.php" method="POST" id="compForm" class="space-y-4">
                            <div>
                                <label for="subject" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Issue Subject</label>
                                <input type="text" name="subject" id="subject" required placeholder="e.g. Broken AC in Room 10B" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition">
                            </div>
                            
                            <div>
                                <label for="message" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Detailed Description</label>
                                <textarea name="message" id="message" rows="5" required placeholder="Please describe the problem accurately so our technicians can prepare the right tools." 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition resize-none"></textarea>
                            </div>
                            
                            <button type="submit" id="submitBtn" class="w-full flex justify-center items-center gap-2 bg-primary hover:bg-green-700 text-white font-bold py-3.5 px-6 rounded-xl transition duration-200 shadow-md mt-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Submit Ticket</span>
                                <svg id="btnSpinner" class="animate-spin -ml-1 text-white hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Complaint History (2/3 Width) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h2 class="text-xl font-bold text-dark flex items-center gap-2">
                                <i data-lucide="history" class="w-5 h-5 text-gray-400"></i> Past Tickets
                            </h2>
                            <span class="bg-gray-200 text-gray-600 font-bold px-3 py-1 rounded-full text-xs"><?php echo count($complaints); ?></span>
                        </div>
                        
                        <?php if(empty($complaints)): ?>
                            <div class="p-12 text-center">
                                <i data-lucide="folder-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <h3 class="text-lg font-bold text-gray-400">No complaints filed</h3>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                                <?php foreach($complaints as $c): ?>
                                    <div class="p-6 hover:bg-gray-50 transition">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-bold text-dark text-lg"><?php echo htmlspecialchars($c['subject']); ?></h3>
                                            <?php 
                                                // Dynamic Status Badges
                                                $s = $c['status'];
                                                if($s == 'open') echo '<span class="bg-warning/20 text-warning px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider h-fit">Open</span>';
                                                elseif($s == 'in_progress') echo '<span class="bg-primary/20 text-primary px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider h-fit">In Progress</span>';
                                                else echo '<span class="bg-success/20 text-success px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider h-fit">Resolved</span>';
                                            ?>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-4"><?php echo nl2br(htmlspecialchars($c['message'])); ?></p>
                                        
                                        <div class="flex items-center gap-4 text-xs font-medium text-gray-400">
                                            <div class="flex items-center gap-1.5 border border-gray-200 rounded-md px-2 py-1">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                                <?php echo date('M j, Y h:i A', strtotime($c['created_at'])); ?>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="ticket" class="w-3.5 h-3.5"></i> #<?php echo str_pad($c['id'], 6, '0', STR_PAD_LEFT); ?>
                                            </div>
                                        </div>
                                        
                                        <?php if($s == 'resolved' && !empty($c['resolved_by'])): ?>
                                            <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-100 flex items-start gap-2">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                                <p class="text-sm text-green-800"><span class="font-bold">Admin Note:</span> Issue was officially resolved by <?php echo htmlspecialchars($c['resolved_by']); ?>.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        document.addEventListener("DOMContentLoaded", () => {
            <?php echo $alertScript; ?>
        });

        const form = document.getElementById('compForm');
        if(form) {
            form.addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                const text = btn.querySelector('span');
                const spin = document.getElementById('btnSpinner');
                // Allow POST execution sync before visual disable
                setTimeout(() => {
                    btn.classList.add('opacity-90', 'cursor-not-allowed');
                    spin.classList.remove('hidden');
                    text.textContent = "Sending...";
                }, 10);
            });
        }
    </script>
</body>
</html>
