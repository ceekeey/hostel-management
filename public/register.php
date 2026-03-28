<?php
session_start();

// Redirect already logged-in users 
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else if ($_SESSION['role'] === 'staff') {
        header("Location: ../staff/dashboard.php");
    } else {
        header("Location: ../student/dashboard.php");
    }
    exit();
}

// Function to get base URL gracefully
$base_url = '/hotel';

// Pre-fill fields if there was an error
$old_fullname = $_SESSION['old']['fullname'] ?? '';
$old_email = $_SESSION['old']['email'] ?? '';
$old_phone = $_SESSION['old']['phone'] ?? '';
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hostel Management System</title>
    
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0d6efd',
                        dark: '#212529',
                        light: '#f8f9fa',
                        success: '#198754'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        /* Base styles */
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .bg-image {
            background-image: url('https://images.unsplash.com/photo-1576495199011-eb94736d05d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex md:grid md:grid-cols-2">

    <!-- Left Column: Image Area (Hidden on mobile) -->
    <div class="hidden md:flex relative bg-image animate__animated animate__fadeIn">
        <div class="absolute inset-0 bg-primary/80 mix-blend-multiply"></div>
        <div class="relative z-10 flex flex-col justify-center items-start p-16 text-white h-full w-full bg-gradient-to-t from-dark/90 to-transparent">
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">Join the Community!</h1>
            <p class="text-lg lg:text-xl text-gray-200">Register today to secure your bed space and manage all your hostel needs digitally.</p>
        </div>
    </div>
    
    <!-- Right Column: Form Area -->
    <div class="flex-1 flex flex-col justify-center p-8 sm:p-12 lg:p-16 xl:p-24 bg-white relative w-full h-full min-h-screen md:min-h-0 overflow-y-auto animate__animated animate__fadeInRight">
        
        <!-- Optional Back to Home Link -->
        <a href="../index.php" class="absolute top-8 right-8 text-gray-500 hover:text-primary transition font-medium flex items-center gap-1 z-10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Home
        </a>

        <div class="w-full max-w-md mx-auto relative pt-10 md:pt-0">
            
            <div class="text-center mb-8">
                <a href="../index.php" class="text-3xl font-bold text-primary tracking-tight inline-block mb-2">
                    Hostel<span class="text-dark">Sys</span>
                </a>
                <h2 class="text-2xl font-bold text-dark mt-4">Create your account</h2>
                <p class="text-gray-500 mt-2">Sign up to get started as a student.</p>
            </div>

            <!-- Pre-process SweetAlert triggers if present -->
            <?php 
                $alertScript = "";
                if(isset($_SESSION['error'])) {
                    $errorMsg = addslashes($_SESSION['error']);
                    $alertScript = "
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: '$errorMsg',
                            confirmButtonColor: '#0d6efd'
                        });
                    ";
                    unset($_SESSION['error']); 
                }
                if(isset($_SESSION['success'])) {
                    $successMsg = addslashes($_SESSION['success']);
                    $alertScript = "
                        Swal.fire({
                            icon: 'success',
                            title: 'Account Created!',
                            text: '$successMsg',
                            confirmButtonColor: '#198754'
                        });
                    ";
                    unset($_SESSION['success']); 
                }
            ?>

            <form action="../actions/register_action.php" method="POST" id="authForm" class="space-y-4">
                <div>
                    <label for="fullname" class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="fullname" id="fullname" required placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($old_fullname); ?>"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" required placeholder="name@edu.com" value="<?php echo htmlspecialchars($old_email); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" id="phone" required placeholder="+123456789" value="<?php echo htmlspecialchars($old_phone); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" id="password" required placeholder="••••••••" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-1">Confirm</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="••••••••" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                    </div>
                </div>
                
                <button type="submit" name="register_btn" id="submitBtn" 
                    class="w-full flex justify-center items-center gap-2 bg-primary hover:bg-blue-700 hover:-translate-y-1 text-white font-bold py-3 px-4 rounded-lg mt-2 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30">
                    <span>Create Account</span>
                    <!-- Spinner SVG (Hidden by default) -->
                    <svg id="btnSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
            
            <div class="mt-8 text-center bg-gray-50 border border-gray-100 rounded-lg p-4">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="font-bold text-primary hover:text-blue-700 hover:underline">Log in</a>
                </p>
            </div>
            
        </div>
    </div>
    
    <!-- Injection of SweetAlert JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            <?php echo $alertScript; ?>
        });

        // Form Submission Loading State UX
        const form = document.getElementById('authForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('btnSpinner');
        const btnText = submitBtn.querySelector('span');

        form.addEventListener('submit', function(e) {
            // Password match checking purely on client-side for faster UX
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if(pass !== confirm) {
                e.preventDefault(); // Stop form submission
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Passwords do not match.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
            // Show spinner
            spinner.classList.remove('hidden');
            // Change text
            btnText.textContent = "Processing...";
        });
    </script>
</body>
</html>
