<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<!-- 2. Hero Section with 3. Image Slider -->
<section class="relative h-[600px] overflow-hidden bg-dark">
    <!-- Carousel Track -->
    <div id="slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
        
        <!-- Slide 1 -->
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Hostel Room" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">Modern Living & Comfort</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Experience the best campus life with secure, clean, and smartly managed hostel facilities.</p>
                <a href="public/register.php" class="bg-primary hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Book Your Room</a>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="https://images.unsplash.com/photo-1522771731470-ea43eb21c09d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Student Louge" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/75"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">Seamless Management</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Administrators and staff can seamlessly handle room applications, tickets, and maintenance online.</p>
                <a href="public/login.php" class="bg-success hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Admin Login</a>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Campus Building" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">24/7 Support Network</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Our dedicated maintenance teams ensure your stay is comfortable and entirely worry-free.</p>
                <a href="public/register.php" class="bg-primary hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Join the Community</a>
            </div>
        </div>
        
    </div>
    
    <!-- Carousel Controls -->
    <button onclick="prevSlide()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition backdrop-blur-sm shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </button>
    <button onclick="nextSlide()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition backdrop-blur-sm shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </button>
</section>

<!-- 4. Features Section (Cards) -->
<section class="py-20 bg-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark">Why Choose HostelSys?</h2>
            <p class="mt-4 text-gray-500 max-w-2xl mx-auto">Our platform provides everything you need to manage and enjoy campus accommodation without the usual headaches.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div class="w-14 h-14 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🏠
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Smart Allocation</h3>
                <p class="text-gray-500">Automated bed space assignments based on faculty and department preferences making onboarding a breeze.</p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div class="w-14 h-14 bg-green-50 text-success rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🔒
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Secure Profiles</h3>
                <p class="text-gray-500">Complete data protection with mandatory profile verification steps for all students ensuring absolute safety.</p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div class="w-14 h-14 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🛠️
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Rapid Maintenance</h3>
                <p class="text-gray-500">Submit requests instantly to staff workflows, reducing turnaround time from stressful days directly to mere hours.</p>
            </div>
        </div>
    </div>
</section>

<!-- 5. How It Works Section (3 steps) -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark">How It Works</h2>
            <p class="mt-4 text-gray-500">Get your room approved in three simple, stress-free steps.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center relative">
            <!-- Connecting Line for Desktop -->
            <div class="hidden md:block absolute top-10 left-[16%] right-[16%] h-0.5 bg-gray-200 z-0"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto bg-white border-4 border-primary text-primary rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md">1</div>
                <h3 class="text-xl font-bold text-dark mb-2">Create Account</h3>
                <p class="text-gray-500 px-4">Register with your university email and basic generic credentials.</p>
            </div>
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto bg-white border-4 border-primary text-primary rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md">2</div>
                <h3 class="text-xl font-bold text-dark mb-2">Complete Profile</h3>
                <p class="text-gray-500 px-4">Fill in your specific faculty requirements and personal data seamlessly.</p>
            </div>
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md shadow-blue-500/30">3</div>
                <h3 class="text-xl font-bold text-dark mb-2">Book Room</h3>
                <p class="text-gray-500 px-4">Select your preferred block and instantly secure your allocated bed space.</p>
            </div>
        </div>
    </div>
</section>

<!-- 6. CTA Section -->
<section class="py-20 bg-primary">
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-3xl md:text-5xl font-bold mb-6">Ready to secure your stay?</h2>
        <p class="text-xl text-blue-100 mb-10">Join hundreds of students currently leveraging our procedural-based HostelSys for a seamless campus lifestyle.</p>
        <a href="public/register.php" class="bg-white text-primary font-bold py-4 px-10 rounded-xl text-lg hover:bg-gray-50 transition shadow-xl inline-block hover:scale-105 duration-300">Create Free Account</a>
    </div>
</section>

<!-- Vanilla JavaScript Setup for Auto Carousel -->
<script>
    const slider = document.getElementById('slider');
    let currentSlide = 0;
    const totalSlides = 3; 
    
    // Auto slide timer configuration (4-5 seconds)
    let slideInterval = setInterval(nextSlide, 4500);

    function updateSlider() {
        // Shift slider wrapper horizontally based on slide index
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    function nextSlide() {
        // Wrap around using modulo logic
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
        resetTimer();
    }

    function prevSlide() {
        // Wrap around reversely
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlider();
        resetTimer();
    }

    // Reset the 4.5-second timer if the user manually triggers a click
    function resetTimer() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 4500);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
