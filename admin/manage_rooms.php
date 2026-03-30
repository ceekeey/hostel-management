<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// Fetch all rooms
$rooms_sql = "SELECT * FROM rooms ORDER BY block_name ASC, room_number ASC";
$rooms_res = mysqli_query($conn, $rooms_sql);

$rooms = [];
if($rooms_res) {
    while($row = mysqli_fetch_assoc($rooms_res)) {
        $rooms[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - HostelSys</title>
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
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Hostel Inventory</h1>
            </div>
            <div class="flex items-center gap-4">
                <p class="font-bold text-dark text-sm"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-100/50">
            
            <?php 
            $alertScript = "";
            if(isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "Swal.fire({icon: 'error', title: 'Error', text: '$errorMsg', confirmButtonColor: '#dc3545'});";
                unset($_SESSION['error']); 
            }
            if(isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "Swal.fire({icon: 'success', title: 'Success', text: '$successMsg', confirmButtonColor: '#198754'});";
                unset($_SESSION['success']); 
            }
            ?>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Add Room Form -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp p-6 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark text-lg mb-2">Create New Room</h3>
                        <p class="text-xs text-gray-400 mb-6 uppercase tracking-widest">Database Insertion</p>
                        
                        <form action="../actions/add_room_action.php" method="POST" class="space-y-4 shadow-none">
                            <div>
                                <label for="block_name" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Block/Hostel Name</label>
                                <input type="text" name="block_name" id="block_name" required placeholder="e.g. Block A" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>

                            <div>
                                <label for="room_number" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Room Number</label>
                                <input type="text" name="room_number" id="room_number" required placeholder="e.g. 101" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="capacity" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Bed Capacity</label>
                                    <input type="number" name="capacity" id="capacity" required min="1" max="10" value="4" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                </div>
                                <div>
                                    <label for="room_type" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Category</label>
                                    <select name="room_type" id="room_type" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                        <option value="Standard">Standard</option>
                                        <option value="Premium">Premium</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-dark hover:bg-gray-800 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-md mt-6">
                                <i data-lucide="plus" class="w-4 h-4"></i> Add to Database
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Rooms Table -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-1s shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-dark text-lg">Active Inventory</h3>
                            <span class="bg-gray-200 text-gray-600 font-bold px-3 py-1 rounded-full text-xs"><?php echo count($rooms); ?> Rooms Total</span>
                        </div>
                        
                        <div class="flex-1 overflow-auto">
                            <?php if(empty($rooms)): ?>
                                <div class="p-12 text-center">
                                    <i data-lucide="layout-grid" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <h3 class="text-lg font-bold text-gray-400">No rooms configured</h3>
                                    <p class="text-sm text-gray-400 mt-1">Use the panel on the left to initialize your hostel blocks.</p>
                                </div>
                            <?php else: ?>
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                            <th class="p-4 font-bold">Room Details</th>
                                            <th class="p-4 font-bold">Capacity</th>
                                            <th class="p-4 font-bold">Status</th>
                                            <th class="p-4 font-bold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <?php foreach($rooms as $r): ?>
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="p-4">
                                                    <p class="font-bold text-dark text-sm leading-tight"><?php echo htmlspecialchars($r['block_name']); ?> - Room <?php echo htmlspecialchars($r['room_number']); ?></p>
                                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($r['room_type']); ?></p>
                                                </td>
                                                <td class="p-4">
                                                    <div class="flex items-center gap-2">
                                                        <?php 
                                                            $occupancy_percent = ($r['current_occupancy'] / $r['capacity']) * 100;
                                                            $color = $occupancy_percent >= 100 ? 'bg-danger' : 'bg-primary';
                                                        ?>
                                                        <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                            <div class="h-full <?php echo $color; ?>" style="width: <?php echo $occupancy_percent; ?>%"></div>
                                                        </div>
                                                        <span class="text-xs font-bold text-dark"><?php echo $r['current_occupancy']; ?>/<?php echo $r['capacity']; ?></span>
                                                    </div>
                                                </td>
                                                <td class="p-4">
                                                    <?php 
                                                        $stat = $r['status'];
                                                        if($stat == 'available') echo '<span class="bg-success/10 text-success border border-success/20 px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Available</span>';
                                                        elseif($stat == 'full') echo '<span class="bg-danger/10 text-danger border border-danger/20 px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Full</span>';
                                                        else echo '<span class="bg-warning/10 text-warning border border-warning/20 px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest">Maint</span>';
                                                    ?>
                                                </td>
                                                <td class="p-4 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <a href="room_inventory.php?room_id=<?php echo $r['id']; ?>" class="p-1.5 text-gray-400 hover:text-primary transition bg-gray-50 hover:bg-green-50 rounded" title="Manage Inventory">
                                                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                                                        </a>
                                                        <button class="p-1.5 text-gray-400 hover:text-primary transition bg-gray-50 hover:bg-green-50 rounded"><i data-lucide="edit" class="w-4 h-4"></i></button>
                                                        <form action="../actions/delete_room_action.php" method="POST" class="inline" onsubmit="return confirm('Are you sure? This action is highly destructive and will delete associated allocations.');">
                                                            <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-danger transition bg-gray-50 hover:bg-red-50 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
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
    </script>
</body>
</html>
