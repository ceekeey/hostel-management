<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// 1. Fetch all rooms joined with their current inventory assets (summary)
$rooms_sql = "
    SELECT r.*, 
           GROUP_CONCAT(CONCAT(i.item_name, ' x', ri.quantity) SEPARATOR ', ') as asset_summary,
           (SELECT COUNT(*) FROM room_inventory WHERE room_id = r.id) as total_asset_types
    FROM rooms r 
    LEFT JOIN room_inventory ri ON r.id = ri.room_id 
    LEFT JOIN inventory i ON ri.item_id = i.id 
    GROUP BY r.id
    ORDER BY r.block_name ASC, r.room_number ASC
";
$rooms_res = mysqli_query($conn, $rooms_sql);

$rooms = [];
if($rooms_res) {
    while($row = mysqli_fetch_assoc($rooms_res)) {
        $rooms[] = $row;
    }
}

// 2. Fetch available inventory items for the "Quick Allocate" modal
$inventory_sql = "SELECT * FROM inventory ORDER BY item_name ASC";
$inventory_res = mysqli_query($conn, $inventory_sql);
$inventory_items = [];
while($row = mysqli_fetch_assoc($inventory_res)) {
    $inventory_items[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Hostelio</title>
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
                                            <th class="p-4 font-bold">Room Assets</th>
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
                                                    <?php if($r['asset_summary']): ?>
                                                        <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                                            <?php 
                                                            $assets = explode(', ', $r['asset_summary']);
                                                            foreach($assets as $asset): 
                                                                $icon = 'package';
                                                                if(stripos($asset, 'fan') !== false) $icon = 'fan';
                                                                if(stripos($asset, 'bed') !== false) $icon = 'bed';
                                                                if(stripos($asset, 'chair') !== false) $icon = 'armchair';
                                                            ?>
                                                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tight border border-gray-200">
                                                                    <i data-lucide="<?php echo $icon; ?>" class="w-3 h-3 text-gray-400"></i>
                                                                    <?php echo htmlspecialchars($asset); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-[10px] font-bold text-gray-300 italic">No assets allocated</span>
                                                    <?php endif; ?>
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
                                                        <button onclick="openQuickAllocate(<?php echo $r['id']; ?>, '<?php echo $r['block_name'] . ' Rm ' . $r['room_number']; ?>')" 
                                                                class="p-1.5 text-primary hover:bg-green-50 rounded border border-transparent hover:border-green-100 transition" title="Quick Assets">
                                                            <i data-lucide="zap" class="w-4 h-4"></i>
                                                        </button>
                                                        <a href="room_inventory.php?room_id=<?php echo $r['id']; ?>" class="p-1.5 text-gray-400 hover:text-primary transition bg-gray-50 hover:bg-green-50 rounded" title="Detailed Inventory">
                                                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                                                        </a>
                                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($r)); ?>)" 
                                                                class="p-1.5 text-gray-400 hover:text-primary transition bg-gray-50 hover:bg-green-50 rounded" title="Edit Room">
                                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                                        </button>
                                                        <form action="../actions/delete_room_action.php" method="POST" class="inline" onsubmit="return confirm('WARNING: Are you sure? This will delete Room <?php echo $r['room_number']; ?> and ALL associated data (allocations & assets). This cannot be undone!');">
                                                            <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-danger transition bg-gray-50 hover:bg-red-50 rounded" title="Delete Room">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
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

    <!-- Edit Room Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg p-10 animate__animated animate__fadeInUp animate__faster shadow-2xl border border-gray-100 overflow-hidden relative">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary/5 rounded-full"></div>
            
            <div class="flex justify-between items-center mb-8 relative z-10">
                <div>
                    <h2 class="text-3xl font-black text-dark tracking-tighter">Edit Room Detail</h2>
                    <p class="text-xs text-primary font-black uppercase tracking-widest mt-1">Updating Property Records</p>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-dark transition p-2 hover:bg-gray-100 rounded-xl"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            
            <form action="../actions/edit_room_action.php" method="POST" class="space-y-6 relative z-10">
                <input type="hidden" name="room_id" id="edit_room_id">
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Block Name</label>
                        <input type="text" name="block_name" id="edit_block_name" required 
                               class="w-full px-5 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all font-bold text-dark">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Room Number</label>
                        <input type="text" name="room_number" id="edit_room_number" required 
                               class="w-full px-5 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all font-bold text-dark">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Bed Capacity</label>
                        <input type="number" name="capacity" id="edit_capacity" required min="1" max="10"
                               class="w-full px-5 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all font-bold text-dark">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Room Type</label>
                        <select name="room_type" id="edit_room_type" class="w-full px-5 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all font-bold text-dark">
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 flex gap-4">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl transition hover:bg-gray-200">Discard Changes</button>
                    <button type="submit" class="flex-1 bg-dark hover:bg-gray-800 text-white font-black py-4 rounded-2xl transition shadow-lg active:scale-95">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Allocate Modal -->
    <div id="allocateModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-3xl w-full max-w-md p-8 animate__animated animate__zoomIn animate__faster border-4 border-primary/20 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-black text-dark tracking-tighter">Quick Asset Allocation</h2>
                    <p id="modal_room_info" class="text-xs text-primary font-black uppercase tracking-widest">Room 101 - Block A</p>
                </div>
                <button onclick="closeQuickAllocate()" class="text-gray-400 hover:text-dark transition p-2"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            
            <form action="../actions/quick_allocate_action.php" method="POST" class="space-y-6">
                <input type="hidden" name="room_id" id="modal_room_id">
                
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Select Inventory Asset</label>
                    <select name="item_id" required class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all font-bold text-dark">
                        <option value="">-- Choose Asset --</option>
                        <?php foreach($inventory_items as $item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['item_name']); ?> (Category: <?php echo $item['category']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Quantity to Allocate</label>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="adjustQty(-1)" class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center text-xl font-bold hover:bg-gray-200 transition">-</button>
                        <input type="number" name="quantity" id="alloc_qty" value="1" min="1" required class="flex-1 px-5 py-4 rounded-2xl border-2 border-gray-100 bg-gray-50 text-center font-black text-2xl outline-none focus:border-primary transition-all">
                        <button type="button" onclick="adjustQty(1)" class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center text-xl font-bold hover:bg-gray-200 transition">+</button>
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="closeQuickAllocate()" class="flex-1 bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl transition hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="flex-1 bg-primary hover:bg-green-700 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-green-500/20 active:scale-95">Allocate Asset</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
        document.addEventListener("DOMContentLoaded", () => {
            <?php echo $alertScript; ?>
        });

        // Quick Allocate Logic
        const modal = document.getElementById('allocateModal');
        const roomIdInput = document.getElementById('modal_room_id');
        const roomInfoText = document.getElementById('modal_room_info');
        const qtyInput = document.getElementById('alloc_qty');

        function openQuickAllocate(id, info) {
            roomIdInput.value = id;
            roomInfoText.textContent = info;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            qtyInput.value = 1;
        }

        function closeQuickAllocate() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function adjustQty(amount) {
            let val = parseInt(qtyInput.value);
            val = isNaN(val) ? 1 : val + amount;
            if(val < 1) val = 1;
            qtyInput.value = val;
        }

        // Edit Room Modal Logic
        const editModal = document.getElementById('editModal');
        
        function openEditModal(room) {
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_block_name').value = room.block_name;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('edit_room_type').value = room.room_type;
            
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        }

        function closeEditModal() {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        }

        // Close on backdrop click
        editModal.addEventListener('click', (e) => {
            if(e.target === editModal) closeEditModal();
        });
    </script>
</body>
</html>
