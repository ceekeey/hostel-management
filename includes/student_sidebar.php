<?php
$current_page = basename($_SERVER['PHP_SELF']);
$nav_items = [
    'dashboard.php' => ['icon' => 'layout-dashboard', 'label' => 'Dashboard'],
    'apply.php' => ['icon' => 'file-form-signature', 'label' => 'Application'],
    'my_room.php' => ['icon' => 'door-open', 'label' => 'Allocation'],
    'payment.php' => ['icon' => 'credit-card', 'label' => 'Payments'],
    'complaints.php' => ['icon' => 'message-square-warning', 'label' => 'Support'],
];
?>
<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-30 opacity-0 pointer-events-none transition lg:hidden"></div>

<!-- 1. Sidebar Navigation (Desktop) -->
<aside class="w-72 bg-gray-900 shadow-xl hidden lg:flex flex-col h-full border-r border-gray-800 relative z-20">
    <div class="h-20 flex items-center px-8 border-b border-gray-800">
        <a href="../index.php" class="text-3xl font-bold text-primary tracking-tight">Hostel<span class="text-white">Sys</span></a>
    </div>
    <nav class="flex-1 py-8 px-5 space-y-2 overflow-y-auto no-scrollbar">
        <h4 class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Main Menu</h4>
        <?php foreach ($nav_items as $url => $item): ?>
            <?php 
            $isActive = ($current_page == $url);
            $activeClass = $isActive ? 'bg-primary text-white shadow-md shadow-green-500/20 hover:-translate-y-0.5' : 'text-gray-400 hover:bg-gray-800 hover:text-white';
            ?>
            <a href="<?php echo $url; ?>" class="flex items-center gap-3 px-4 py-3.5 rounded-xl font-medium transition duration-200 <?php echo $activeClass; ?>">
                <i data-lucide="<?php echo $item['icon']; ?>" class="w-5 h-5"></i> <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-5 border-t border-gray-800">
        <a href="../actions/logout.php" class="flex items-center gap-3 px-4 py-3.5 text-red-500 hover:bg-gray-800 rounded-xl font-medium transition duration-200">
            <i data-lucide="log-out" class="w-5 h-5"></i> Sign Out
        </a>
    </div>
</aside>

<!-- Mobile Sidebar Drawer -->
<aside id="mobile-sidebar" class="fixed top-0 left-0 w-80 h-full bg-gray-900 z-40 lg:hidden -translate-x-full shadow-2xl flex flex-col transition-transform duration-300">
    <div class="h-20 flex items-center justify-between px-8 border-b border-gray-800">
        <a href="../index.php" class="text-3xl font-bold text-primary tracking-tight">Hostel<span class="text-white">Sys</span></a>
        <button id="close-sidebar" class="text-gray-400 p-2 hover:bg-gray-800 rounded-lg"><i data-lucide="x" class="w-6 h-6"></i></button>
    </div>
    <nav class="flex-1 py-10 px-6 space-y-3">
        <?php foreach ($nav_items as $url => $item): ?>
            <?php 
            $isActive = ($current_page == $url);
            $activeClass = $isActive ? 'bg-primary text-white shadow-lg shadow-green-500/30 font-bold' : 'text-gray-400 hover:bg-gray-800 hover:text-white font-bold';
            ?>
            <a href="<?php echo $url; ?>" class="flex items-center gap-4 px-5 py-4 rounded-2xl transition <?php echo $activeClass; ?>">
                <i data-lucide="<?php echo $item['icon']; ?>" class="w-6 h-6"></i> <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-8 border-t border-gray-800">
        <a href="../actions/logout.php" class="flex items-center justify-center gap-3 w-full py-4 text-red-500 bg-gray-800 font-black rounded-2xl transition hover:bg-gray-700">
            <i data-lucide="log-out" class="w-5 h-5"></i> SIGN OUT
        </a>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        const toggleSidebar = (isOpen) => {
            if (!mobileSidebar || !sidebarOverlay) return;
            if (isOpen) {
                mobileSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
                sidebarOverlay.classList.add('opacity-100');
                document.body.style.overflow = 'hidden';
            } else {
                mobileSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
                sidebarOverlay.classList.remove('opacity-100');
                document.body.style.overflow = 'auto';
            }
        };

        if(openBtn) openBtn.addEventListener('click', () => toggleSidebar(true));
        if(closeBtn) closeBtn.addEventListener('click', () => toggleSidebar(false));
        if(sidebarOverlay) sidebarOverlay.addEventListener('click', () => toggleSidebar(false));
    });
</script>
