<?php
session_start();

// Redirect already logged-in users based on their role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else if ($_SESSION['role'] === 'student') {
        if (isset($_SESSION['profile_completed']) && $_SESSION['profile_completed'] == 0) {
            header("Location: ../student/update_profile.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
    } else if ($_SESSION['role'] === 'staff') {
        header("Location: ../staff/dashboard.php");
    }
    exit();
}

// Function to get base URL gracefully
$base_url = '/hotel';

// Pre-fill fields if there was a login error
$old_email = $_SESSION['old_login']['email'] ?? '';
unset($_SESSION['old_login']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostelio</title>

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16a34a',
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        /* Base styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .bg-image {
            background-image: url('../1a.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="antialiased min-h-screen grid grid-cols-1 md:grid-cols-2">

    <!-- Left Column: Image Area (Hidden on mobile) -->
    <div class="hidden md:flex relative bg-image animate__animated animate__fadeIn">
        <div class="absolute inset-0 bg-primary/80 mix-blend-multiply"></div>
        <div
            class="relative z-10 flex flex-col justify-center items-start p-16 text-white h-full w-full bg-gradient-to-t from-dark/90 to-transparent">
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">Welcome Back!</h1>
            <p class="text-lg lg:text-xl text-gray-200">Log in to manage your campus stay, review your allocations, and
                resolve your concerns.</p>
        </div>
    </div>

    <!-- Right Column: Form Area -->
    <div
        class="flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white relative animate__animated animate__fadeInRight">

        <!-- Optional Back to Home Link -->
        <a href="../index.php"
            class="absolute top-8 right-8 text-gray-500 hover:text-primary transition font-medium flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Home
        </a>

        <div class="w-full max-w-md">

            <div class="text-center mb-10">
                <a href="../index.php" class="text-3xl font-bold text-primary tracking-tight inline-block mb-2">
                    Hostel<span class="text-dark">io</span>
                </a>
                <h2 class="text-2xl font-bold text-dark mt-4">Sign in to your account</h2>
                <p class="text-gray-500 mt-2">Please enter your credentials to continue.</p>
            </div>

            <!-- Pre-process SweetAlert triggers if present -->
            <?php
            $alertScript = "";
            if (isset($_SESSION['error'])) {
                $errorMsg = addslashes($_SESSION['error']);
                $alertScript = "
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: '$errorMsg',
                            confirmButtonColor: '#0d6efd'
                        });
                    ";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                $successMsg = addslashes($_SESSION['success']);
                $alertScript = "
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: '$successMsg',
                            confirmButtonColor: '#198754'
                        });
                    ";
                unset($_SESSION['success']);
            }
            ?>

            <form action="../actions/login_action.php" method="POST" id="authForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="name@student.edu"
                        value="<?php echo htmlspecialchars($old_email); ?>"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <a href="#" class="text-sm font-medium text-primary hover:text-green-700 hover:underline">Forgot
                            password?</a>
                    </div>
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition transition-shadow">
                </div>

                <button type="submit" name="login_btn" id="submitBtn"
                    class="w-full flex justify-center items-center gap-2 bg-primary hover:bg-green-700 hover:-translate-y-1 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-green-500/30">
                    <span>Sign In</span>
                    <!-- Spinner SVG (Hidden by default) -->
                    <svg id="btnSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </form>

            <div class="mt-8 text-center bg-gray-50 border border-gray-100 rounded-lg p-4">
                <p class="text-sm text-gray-600">
                    Don't have an account yet?
                    <a href="register.php" class="font-bold text-primary hover:text-green-700 hover:underline">Create
                        one</a>
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

        form.addEventListener('submit', function () {
            // Disable button
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
            // Show spinner
            spinner.classList.remove('hidden');
            // Change text
            btnText.textContent = "Signing in...";
        });
    </script>
</body>

</html>