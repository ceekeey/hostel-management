</main>
<!-- 7. Footer -->
<footer class="bg-dark text-white pt-20 pb-10 relative overflow-hidden">
    <!-- Decoration -->
    <div
        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary to-transparent opacity-50">
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- Brand Section -->
            <div class="lg:col-span-1">
                <a href="<?php echo $base_url; ?>/index.php" class="flex items-center gap-2 mb-6 group">
                    <img src="<?php echo $base_url; ?>/logo.png" alt="Logo" class="h-10 w-auto">
                    <span class="text-2xl font-bold text-white tracking-tight">
                        Hostel<span class="text-primary">Sys</span>
                    </span>
                </a>
                <p class="text-gray-400 leading-relaxed mb-6 font-medium">
                    Empowering students and administrators with a seamless, secure, and modern campus housing
                    experience.
                </p>
                <!-- Social Links -->
                <div class="flex items-center gap-4">
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-primary/20">
                        <i data-lucide="facebook" class="w-5 h-5"></i>
                    </a>
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-primary/20">
                        <i data-lucide="twitter" class="w-5 h-5"></i>
                    </a>
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-primary/20">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                    </a>
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-primary/20">
                        <i data-lucide="linkedin" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div>
                <h4 class="text-lg font-bold mb-8 relative inline-block">
                    Quick Links
                    <span class="absolute -bottom-2 left-0 w-8 h-1 bg-primary rounded-full"></span>
                </h4>
                <ul class="space-y-4">
                    <li>
                        <a href="<?php echo $base_url; ?>/index.php"
                            class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 group">
                            <i data-lucide="chevron-right"
                                class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 group">
                            <i data-lucide="chevron-right"
                                class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all"></i>
                            About Our Hostels
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/public/register.php"
                            class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 group">
                            <i data-lucide="chevron-right"
                                class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all"></i>
                            Apply for Room
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 group">
                            <i data-lucide="chevron-right"
                                class="w-4 h-4 opacity-0 group-hover:opacity-100 -ml-4 group-hover:ml-0 transition-all"></i>
                            Support Center
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Section -->
            <div>
                <h4 class="text-lg font-bold mb-8 relative inline-block">
                    Contact Us
                    <span class="absolute -bottom-2 left-0 w-8 h-1 bg-primary rounded-full"></span>
                </h4>
                <ul class="space-y-6">
                    <li class="flex items-start gap-4 group">
                        <div
                            class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-primary shrink-0 group-hover:bg-primary group-hover:text-white transition-all">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed pt-1">
                            123 Campus Drive, University City,<br>Sector 7-B, Education Square
                        </p>
                    </li>
                    <li class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-primary shrink-0 group-hover:bg-primary group-hover:text-white transition-all">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <a href="mailto:support@hostelsys.com"
                            class="text-gray-400 hover:text-white transition-colors text-sm font-medium">
                            support@hostelsys.com
                        </a>
                    </li>
                    <li class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-primary shrink-0 group-hover:bg-primary group-hover:text-white transition-all">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <span class="text-gray-400 text-sm font-medium">+234 567 8900</span>
                    </li>
                </ul>
            </div>

            <!-- Newsletter/Newsletter Mock -->
            <div>
                <h4 class="text-lg font-bold mb-8 relative inline-block">
                    Stay Updated
                    <span class="absolute -bottom-2 left-0 w-8 h-1 bg-primary rounded-full"></span>
                </h4>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                    Subscribe to receive important hostel updates and announcements.
                </p>
                <div class="flex flex-col gap-3">
                    <input type="email" placeholder="Email address"
                        class="bg-gray-800 border-none rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-primary w-full outline-none transition-all">
                    <button
                        class="bg-primary hover:bg-green-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-lg shadow-green-500/20 active:scale-95">
                        Subscribe Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div
            class="border-t border-gray-800 pt-10 flex flex-col md:flex-row justify-between items-center gap-6 text-gray-500 text-xs font-semibold uppercase tracking-wider text-center md:text-left">
            <p>&copy; <?php echo date('Y'); ?> Campus Hostel Management System. All Rights Reserved.</p>
            <div class="flex gap-8">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
                <a href="#" class="hover:text-white transition">Security</a>
            </div>
        </div>
    </div>
</footer>
<script>
    // Ensure Lucide icons are created after everything is loaded
    function initIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        } else {
            // Retry if library is slow to load
            setTimeout(initIcons, 100);
        }
    }
    document.addEventListener("DOMContentLoaded", initIcons);
    // Also run immediately in case it's already loaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initIcons();
    }
</script>
</body>

</html>