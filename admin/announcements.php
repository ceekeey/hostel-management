<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';

// Fetch all existing announcements
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements_res = mysqli_query($conn, $announcements_sql);

$announcements = [];
if($announcements_res) {
    while($row = mysqli_fetch_assoc($announcements_res)) {
        $announcements[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - HostelSys</title>
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
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Post Announcement</h1>
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
                
                <!-- Add Announcement Form -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp p-6 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark text-lg mb-2">Create Notice</h3>
                        <p class="text-xs text-gray-400 mb-6 uppercase tracking-widest">Global Broadcast</p>
                        
                        <form action="../actions/announcement_action.php" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Headline</label>
                                <input type="text" name="title" required placeholder="e.g. Electricity Outage Notice" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Priority Level</label>
                                <select name="priority" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                                    <option value="Low">Low - Informational</option>
                                    <option value="Medium">Medium - Important</option>
                                    <option value="High">High - Urgent / Immediate Action</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Message Content</label>
                                <textarea name="content" rows="6" required placeholder="Details about this announcement..." 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition resize-none"></textarea>
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-dark hover:bg-gray-800 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-md mt-6">
                                <i data-lucide="megaphone" class="w-4 h-4"></i> Post Announcement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Announcements History -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl animate__animated animate__fadeInUp animate__delay-1s shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-dark text-lg">Past Notices</h3>
                            <span class="bg-gray-200 text-gray-600 font-bold px-3 py-1 rounded-full text-xs"><?php echo count($announcements); ?> Total</span>
                        </div>
                        
                        <div class="flex-1 overflow-auto p-6 space-y-4">
                            <?php if(empty($announcements)): ?>
                                <div class="p-12 text-center">
                                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <h3 class="text-lg font-bold text-gray-400">No announcements yet</h3>
                                    <p class="text-sm text-gray-400 mt-1">Broadcast important news to all your students.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($announcements as $a): ?>
                                    <div class="group bg-white border border-gray-100 hover:border-primary/30 rounded-2xl p-5 transition-all duration-300 hover:shadow-md relative overflow-hidden">
                                        <?php 
                                            $priorityColor = 'bg-gray-400';
                                            if($a['priority'] == 'High') $priorityColor = 'bg-danger shadow-red-500/20';
                                            elseif($a['priority'] == 'Medium') $priorityColor = 'bg-warning shadow-yellow-500/20';
                                            else $priorityColor = 'bg-primary shadow-green-500/20';
                                        ?>
                                        <div class="absolute top-0 left-0 w-1.5 h-full <?php echo $priorityColor; ?>"></div>
                                        
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest text-white <?php echo $priorityColor; ?> shadow-sm">
                                                        <?php echo $a['priority']; ?>
                                                    </span>
                                                    <span class="text-xs text-gray-400 font-bold tracking-tight">
                                                        <?php echo date('M d, Y • h:i A', strtotime($a['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <h4 class="font-bold text-dark text-lg leading-tight mb-2"><?php echo htmlspecialchars($a['title']); ?></h4>
                                                <p class="text-sm text-gray-500 leading-relaxed font-medium">
                                                    <?php echo nl2br(htmlspecialchars($a['content'])); ?>
                                                </p>
                                            </div>
                                            
                                            <form action="../actions/announcement_action.php" method="POST" class="inline" onsubmit="return confirm('Remove this announcement?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                                <button type="submit" class="p-2 text-gray-300 hover:text-danger hover:bg-red-50 rounded-xl transition">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
