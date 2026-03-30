<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// 1. Fetch all pending applications with student details
$app_sql = "
    SELECT a.*, u.fullname, u.email, u.faculty, u.department 
    FROM applications a 
    JOIN users u ON a.student_id = u.id 
    WHERE a.status = 'pending' 
    ORDER BY a.created_at ASC
";
$app_res = mysqli_query($conn, $app_sql);

$applications = [];
if($app_res) {
    while($row = mysqli_fetch_assoc($app_res)) {
        $applications[] = $row;
    }
}

// 2. Fetch all currently available rooms (capacity > occupancy) to populate admin dropdowns
$rooms_sql = "SELECT * FROM rooms WHERE status = 'available' AND current_occupancy < capacity ORDER BY block_name ASC, room_number ASC";
$rooms_res = mysqli_query($conn, $rooms_sql);

$available_rooms = [];
if($rooms_res) {
    while($row = mysqli_fetch_assoc($rooms_res)) {
        $available_rooms[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocations - HostelSys</title>
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
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Bed Assignments</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                    <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                </div>
                <p class="font-bold text-dark text-sm hidden sm:block"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-100/50">
            
            <?php 
            $alertScript = "";
            if(isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "Swal.fire({icon: 'error', title: 'Allocation Failed', text: '$errorMsg', confirmButtonColor: '#dc3545'});";
                unset($_SESSION['error']); 
            }
            if(isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Assigned', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']); 
            }
            ?>

            <div class="max-w-7xl mx-auto">
                <div class="mb-8 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-dark tracking-tight mb-1">Pending Room Applications</h2>
                        <p class="text-gray-500 text-sm">Assign available beds to incoming student requests.</p>
                    </div>
                    <?php if(empty($available_rooms)): ?>
                        <div class="bg-danger/10 text-danger px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                            <i data-lucide="triangle-alert" class="w-4 h-4 text-danger"></i> No Vacant Rooms Available
                        </div>
                    <?php else: ?>
                        <div class="bg-success/10 text-success px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-success"></i> <?php echo count($available_rooms); ?> Rooms Vacant
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(empty($applications)): ?>
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp shadow-sm border border-gray-100 p-16 text-center">
                        <div class="w-20 h-20 mx-auto rounded-full bg-gray-50 flex items-center justify-center mb-4">
                            <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-dark mb-2">Queue is Empty</h3>
                        <p class="text-gray-500 max-w-sm mx-auto">There are currently no pending student applications requiring administrative allocation.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <?php foreach($applications as $app): ?>
                            <div class="bg-white rounded-2xl animate__animated animate__fadeInUp shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-green-50 text-primary flex items-center justify-center font-bold text-lg">
                                            <?php echo strtoupper(substr($app['fullname'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-dark text-lg leading-tight"><?php echo htmlspecialchars($app['fullname']); ?></h3>
                                            <p class="text-xs text-gray-500 font-medium tracking-wide uppercase mt-0.5">App #<?php echo str_pad($app['id'], 5, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                    </div>
                                    <span class="bg-warning/10 text-warning px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border border-warning/20">Pending</span>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-6 flex-1">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Preference</p>
                                            <p class="text-sm font-bold text-dark"><?php echo htmlspecialchars($app['preferred_block']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Room Type</p>
                                            <p class="text-sm font-bold text-dark"><?php echo htmlspecialchars($app['room_type']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Faculty</p>
                                            <p class="text-xs font-bold text-gray-600 truncate"><?php echo htmlspecialchars($app['faculty']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Department</p>
                                            <p class="text-xs font-bold text-gray-600 truncate"><?php echo htmlspecialchars($app['department']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <form action="../actions/allocate_action.php" method="POST" class="pt-4 border-t border-gray-100">
                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $app['student_id']; ?>">
                                    
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Assign Bed Space</label>
                                    <div class="flex items-center gap-3">
                                        <select name="room_id" required <?php if(empty($available_rooms)) echo 'disabled'; ?> class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-medium disabled:opacity-50">
                                            <option value="" disabled selected>-- Select an Available Room --</option>
                                            <?php 
                                            // Priority sorting: Match exactly what they requested if it exists
                                            foreach($available_rooms as $r) {
                                                $match = ($r['block_name'] == $app['preferred_block'] && $r['room_type'] == $app['room_type']);
                                                $lbl = "{$r['block_name']} - Rm {$r['room_number']} ({$r['room_type']} : {$r['current_occupancy']}/{$r['capacity']})";
                                                if($match) {
                                                    echo "<option value='{$r['id']}' class='bg-green-50 text-primary font-bold'>★ MATCH: {$lbl}</option>";
                                                } else {
                                                    echo "<option value='{$r['id']}'>{$lbl}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        
                                        <!-- Actions -->
                                        <button type="submit" name="action_approve" value="1" <?php if(empty($available_rooms)) echo 'disabled'; ?> class="p-3 bg-primary hover:bg-green-700 text-white rounded-xl shadow-md transition disabled:opacity-50 flex items-center justify-center shrink-0" title="Approve & Allocate">
                                            <i data-lucide="check" class="w-5 h-5"></i>
                                        </button>
                                        
                                        <!-- Built-in Reject Mechanism -->
                                        <button type="submit" name="action_reject" value="1" class="p-3 bg-gray-100 text-danger hover:bg-red-50 hover:text-red-600 rounded-xl transition flex items-center justify-center shrink-0" title="Reject Application" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                            <i data-lucide="x" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </form>

                            </div>
                        <?php endforeach; ?>
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
    </script>
</body>
</html>
