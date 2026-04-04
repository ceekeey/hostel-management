<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// Fetch all inventory items
$inventory_sql = "SELECT i.*, 
                 (SELECT SUM(quantity) FROM room_inventory WHERE item_id = i.id) as assigned_stock 
                 FROM inventory i 
                 ORDER BY i.item_name ASC";
$inventory_res = mysqli_query($conn, $inventory_sql);

$inventory = [];
if($inventory_res) {
    while($row = mysqli_fetch_assoc($inventory_res)) {
        $row['available_stock'] = $row['total_stock'] - ($row['assigned_stock'] ?? 0);
        $inventory[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Inventory - Hostelio</title>
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
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Master Inventory</h1>
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
                
                <!-- Add Inventory Form -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp p-6 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark text-lg mb-2">New Asset</h3>
                        <p class="text-xs text-gray-400 mb-6 uppercase tracking-widest">Global Stock Entry</p>
                        
                        <form action="../actions/inventory_action.php" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Item Name</label>
                                <input type="text" name="item_name" required placeholder="e.g. Single Bed" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Category</label>
                                <select name="category" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                    <option value="Furniture">Furniture</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Bedding">Bedding</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Total Units Owned</label>
                                <input type="number" name="total_stock" required min="1" value="10" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-dark hover:bg-gray-800 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-md mt-6">
                                <i data-lucide="plus" class="w-4 h-4"></i> Register Asset
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-1s shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-dark text-lg">Inventory Stock</h3>
                            <span class="bg-gray-200 text-gray-600 font-bold px-3 py-1 rounded-full text-xs"><?php echo count($inventory); ?> Asset Types</span>
                        </div>
                        
                        <div class="flex-1 overflow-auto">
                            <?php if(empty($inventory)): ?>
                                <div class="p-12 text-center">
                                    <i data-lucide="package" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <h3 class="text-lg font-bold text-gray-400">No assets registered</h3>
                                    <p class="text-sm text-gray-400 mt-1">Register items like beds, chairs, and lamps here.</p>
                                </div>
                            <?php else: ?>
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                            <th class="p-4 font-bold">Item Details</th>
                                            <th class="p-4 font-bold">Category</th>
                                            <th class="p-4 font-bold">Stock Status</th>
                                            <th class="p-4 font-bold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <?php foreach($inventory as $item): ?>
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="p-4">
                                                    <p class="font-bold text-dark text-sm leading-tight"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                                    <p class="text-xs text-gray-400 mt-0.5">ID: #<?php echo $item['id']; ?></p>
                                                </td>
                                                <td class="p-4">
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase tracking-wider"><?php echo $item['category']; ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <div class="space-y-1">
                                                        <div class="flex justify-between text-[10px] font-bold">
                                                            <span class="text-gray-400 uppercase">Utilization</span>
                                                            <span class="text-dark"><?php echo ($item['assigned_stock'] ?? 0); ?> / <?php echo $item['total_stock']; ?></span>
                                                        </div>
                                                        <?php 
                                                            $percent = ($item['total_stock'] > 0) ? (($item['assigned_stock'] ?? 0) / $item['total_stock']) * 100 : 0;
                                                            $color = $percent >= 90 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-primary');
                                                        ?>
                                                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                            <div class="h-full <?php echo $color; ?>" style="width: <?php echo $percent; ?>%"></div>
                                                        </div>
                                                        <p class="text-[10px] font-bold text-success capitalize"><?php echo $item['available_stock']; ?> units available</p>
                                                    </div>
                                                </td>
                                                <td class="p-4 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <form action="../actions/inventory_action.php" method="POST" class="inline" onsubmit="return confirm('Delete this asset type? All room assignments will be removed.');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
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
