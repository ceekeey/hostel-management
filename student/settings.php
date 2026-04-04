<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';
$user_id = $_SESSION['user_id'];

// Fetch latest user data
$user_sql = "SELECT fullname, email, phone, faculty, department, profile_pic FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_res);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Hostelio</title>
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

    <!-- Student Sidebar -->
    <?php include '../includes/student_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden z-10 w-full">
        
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-6 lg:px-10 border-b border-gray-200 z-10">
            <div class="flex items-center gap-4">
                <button id="open-sidebar" class="md:hidden text-gray-400 hover:text-primary transition p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-dark hidden sm:block tracking-tight">Account Settings</h1>
            </div>
            <div class="flex items-center gap-4">
                <p class="font-bold text-dark text-sm"><?php echo htmlspecialchars($user['fullname']); ?></p>
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

            <div class="max-w-4xl mx-auto space-y-8 pb-10">
                
                <!-- Profile Settings -->
                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate__animated animate__fadeInUp">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex items-center gap-3">
                        <i data-lucide="user-cog" class="w-5 h-5 text-primary"></i>
                        <h3 class="font-bold text-lg text-dark">Profile Management</h3>
                    </div>
                    
                    <form action="../actions/update_settings_action.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                        <div class="flex flex-col md:flex-row gap-10">
                            <!-- Avatar Upload -->
                            <div class="w-full md:w-1/3 flex flex-col items-center">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 text-center w-full">Profile Picture</label>
                                <div class="relative group">
                                    <div class="w-40 h-40 rounded-3xl bg-gray-100 border-4 border-white shadow-md overflow-hidden flex items-center justify-center transition group-hover:opacity-90">
                                        <?php if(!empty($user['profile_pic'])): ?>
                                            <img id="preview-img" src="../public/uploads/profiles/<?php echo $user['profile_pic']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div id="preview-placeholder" class="w-full h-full bg-primary/10 text-primary flex items-center justify-center font-bold text-5xl">
                                                <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                            </div>
                                            <img id="preview-img" class="w-full h-full object-cover hidden">
                                        <?php endif; ?>
                                    </div>
                                    <label for="profile_pic" class="absolute bottom-2 right-2 w-10 h-10 bg-primary text-white rounded-xl shadow-lg flex items-center justify-center cursor-pointer hover:scale-110 transition active:scale-95">
                                        <i data-lucide="camera" class="w-5 h-5"></i>
                                    </label>
                                    <input type="file" name="profile_pic" id="profile_pic" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-4 text-center font-medium leading-relaxed uppercase tracking-tighter">Recommended: Square image, <br> Max 2MB (JPG, PNG)</p>
                            </div>

                            <!-- Basic Details -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Full Name</label>
                                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email (Locked)</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-100 text-gray-400 cursor-not-allowed font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Phone Number</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Faculty</label>
                                    <select name="faculty" required class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition font-medium">
                                        <option value="Science" <?php echo $user['faculty'] == 'Science' ? 'selected' : ''; ?>>Science</option>
                                        <option value="Engineering" <?php echo $user['faculty'] == 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                                        <option value="Arts" <?php echo $user['faculty'] == 'Arts' ? 'selected' : ''; ?>>Arts</option>
                                        <option value="Business" <?php echo $user['faculty'] == 'Business' ? 'selected' : ''; ?>>Business</option>
                                        <option value="Medicine" <?php echo $user['faculty'] == 'Medicine' ? 'selected' : ''; ?>>Medicine</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Department</label>
                                    <input type="text" name="department" value="<?php echo htmlspecialchars($user['department']); ?>" required 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition font-medium">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-50 flex justify-end">
                            <button type="submit" class="bg-primary hover:bg-green-700 text-white font-bold py-3.5 px-10 rounded-xl transition shadow-lg shadow-green-500/20 active:scale-95">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Security Settings -->
                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex items-center gap-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-danger"></i>
                        <h3 class="font-bold text-lg text-dark">Password & Security</h3>
                    </div>
                    
                    <form action="../actions/change_password_action.php" method="POST" class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Current Password</label>
                                <input type="password" name="old_password" required placeholder="••••••••" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">New Password</label>
                                <input type="password" name="new_password" required placeholder="••••••••" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Confirm New Password</label>
                                <input type="password" name="confirm_password" required placeholder="••••••••" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-50 flex justify-end">
                            <button type="submit" class="bg-dark hover:bg-gray-800 text-white font-bold py-3.5 px-10 rounded-xl transition shadow-lg active:scale-95">
                                Save New Password
                            </button>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        document.addEventListener("DOMContentLoaded", () => {
            <?php echo $alertScript; ?>
        });

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('preview-placeholder');
                    const img = document.getElementById('preview-img');
                    
                    if(placeholder) placeholder.classList.add('hidden');
                    img.classList.remove('hidden');
                    img.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
