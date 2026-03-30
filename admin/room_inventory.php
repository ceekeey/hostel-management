<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

if(!$room_id) {
    header("Location: manage_rooms.php");
    exit();
}

// Fetch room details
$room_sql = "SELECT * FROM rooms WHERE id = $room_id";
$room_res = mysqli_query($conn, $room_sql);
$room = mysqli_fetch_assoc($room_res);

if(!$room) {
    header("Location: manage_rooms.php");
    exit();
}

// Fetch items assigned to this room
$items_sql = "SELECT ri.*, i.item_name, i.category 
              FROM room_inventory ri 
              JOIN inventory i ON ri.item_id = i.id 
              WHERE ri.room_id = $room_id 
              ORDER BY i.item_name ASC";
$items_res = mysqli_query($conn, $items_sql);

$room_items = [];
if($items_res) {
    while($row = mysqli_fetch_assoc($items_res)) {
        $room_items[] = $row;
    }
}

// Fetch available inventory items for master list dropdown
$master_sql = "SELECT * FROM inventory ORDER BY item_name ASC";
$master_res = mysqli_query($conn, $master_sql);
$master_items = [];
while($row = mysqli_fetch_assoc($master_res)) {
    $master_items[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Inventory - HostelSys</title>
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
                <a href="manage_rooms.php" class="text-gray-400 hover:text-primary transition p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </a>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Room Assets: <?php echo htmlspecialchars($room['block_name'] . ' - ' . $room['room_number']); ?></h1>
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
                
                <!-- Assign New Item Form -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp p-6 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark text-lg mb-2">Assign Asset</h3>
                        <p class="text-xs text-gray-400 mb-6 uppercase tracking-widest">Allocation to this room</p>
                        
                        <form action="../actions/assign_inventory_action.php" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="assign">
                            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Select Asset</label>
                                <select name="item_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                    <option value="">-- Choose Item --</option>
                                    <?php foreach($master_items as $item): ?>
                                        <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['item_name']); ?> (<?php echo $item['category']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Quantity</label>
                                    <input type="number" name="quantity" required min="1" value="1" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Condition</label>
                                    <select name="condition_status" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                        <option value="Good">Good</option>
                                        <option value="Damaged">Damaged</option>
                                        <option value="Missing">Missing</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-dark hover:bg-gray-800 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-md mt-6">
                                <i data-lucide="plus" class="w-4 h-4"></i> Add to Room
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Room Items Table -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-1s shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-dark text-lg">Allocated Assets</h3>
                            <span class="bg-gray-200 text-gray-600 font-bold px-3 py-1 rounded-full text-xs"><?php echo count($room_items); ?> Items</span>
                        </div>
                        
                        <div class="flex-1 overflow-auto">
                            <?php if(empty($room_items)): ?>
                                <div class="p-12 text-center">
                                    <i data-lucide="briefcase" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <h3 class="text-lg font-bold text-gray-400">No assets in this room</h3>
                                    <p class="text-sm text-gray-400 mt-1">Add beds, fans, or chairs using the form on the left.</p>
                                </div>
                            <?php else: ?>
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                            <th class="p-4 font-bold">Item Name</th>
                                            <th class="p-4 font-bold text-center">Qty</th>
                                            <th class="p-4 font-bold text-center">Condition</th>
                                            <th class="p-4 font-bold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <?php foreach($room_items as $item): ?>
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="p-4">
                                                    <p class="font-bold text-dark text-sm leading-tight"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo $item['category']; ?></p>
                                                </td>
                                                <td class="p-4 text-center">
                                                    <span class="font-black text-dark text-sm"><?php echo $item['quantity']; ?></span>
                                                </td>
                                                <td class="p-4 text-center">
                                                    <?php 
                                                        $cond = $item['condition_status'];
                                                        if($cond == 'Good') echo '<span class="text-success font-bold text-[10px] uppercase tracking-wider">Good Condition</span>';
                                                        elseif($cond == 'Damaged') echo '<span class="text-warning font-bold text-[10px] uppercase tracking-wider">Damaged</span>';
                                                        else echo '<span class="text-danger font-bold text-[10px] uppercase tracking-wider">Missing Item</span>';
                                                    ?>
                                                </td>
                                                <td class="p-4 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <form action="../actions/assign_inventory_action.php" method="POST" class="inline" onsubmit="return confirm('Remove this item from the room?');">
                                                            <input type="hidden" name="action" value="remove">
                                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-danger transition bg-gray-50 hover:bg-red-50 rounded"><i data-lucide="x" class="w-4 h-4"></i></button>
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
