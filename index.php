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
            <img src="./1.png" alt="Hostel Room" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">Modern Living & Comfort</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Experience the best campus life with secure, clean, and
                    smartly managed hostel facilities.</p>
                <a href="public/register.php"
                    class="bg-primary hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Book
                    Your Room</a>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="./2.png" alt="Student Louge" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/75"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">Seamless Management</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Administrators and staff can seamlessly handle room
                    applications, tickets, and maintenance online.</p>
                <a href="public/login.php"
                    class="bg-success hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Admin
                    Login</a>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="./3.jpg" alt="Campus Building" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-dark/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">24/7 Support Network</h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">Our dedicated maintenance teams ensure your stay is
                    comfortable and entirely worry-free.</p>
                <a href="public/register.php"
                    class="bg-primary hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition shadow-lg">Join
                    the Community</a>
            </div>
        </div>

    </div>

    <!-- Carousel Controls -->
    <button onclick="prevSlide()"
        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition backdrop-blur-sm shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button onclick="nextSlide()"
        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white rounded-full p-3 transition backdrop-blur-sm shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
</section>

<!-- 4. Features Section (Cards) -->
<section class="py-20 bg-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark">Why Choose HostelSys?</h2>
            <p class="mt-4 text-gray-500 max-w-2xl mx-auto">Our platform provides everything you need to manage and
                enjoy campus accommodation without the usual headaches.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div
                    class="w-14 h-14 bg-green-50 text-primary rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🏠
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Smart Allocation</h3>
                <p class="text-gray-500">Automated bed space assignments based on faculty and department preferences
                    making onboarding a breeze.</p>
            </div>
            <!-- Feature 2 -->
            <div
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div
                    class="w-14 h-14 bg-green-50 text-success rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🔒
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Secure Profiles</h3>
                <p class="text-gray-500">Complete data protection with mandatory profile verification steps for all
                    students ensuring absolute safety.</p>
            </div>
            <!-- Feature 3 -->
            <div
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div
                    class="w-14 h-14 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🛠️
                </div>
                <h3 class="text-xl font-bold text-dark mb-3">Rapid Maintenance</h3>
                <p class="text-gray-500">Submit requests instantly to staff workflows, reducing turnaround time from
                    stressful days directly to mere hours.</p>
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
                <div
                    class="w-20 h-20 mx-auto bg-white border-4 border-primary text-primary rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md">
                    1</div>
                <h3 class="text-xl font-bold text-dark mb-2">Create Account</h3>
                <p class="text-gray-500 px-4">Register with your university email and basic generic credentials.</p>
            </div>
            <div class="relative z-10">
                <div
                    class="w-20 h-20 mx-auto bg-white border-4 border-primary text-primary rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md">
                    2</div>
                <h3 class="text-xl font-bold text-dark mb-2">Complete Profile</h3>
                <p class="text-gray-500 px-4">Fill in your specific faculty requirements and personal data seamlessly.
                </p>
            </div>
            <div class="relative z-10">
                <div
                    class="w-20 h-20 mx-auto bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-md shadow-green-500/30">
                    3</div>
                <h3 class="text-xl font-bold text-dark mb-2">Book Room</h3>
                <p class="text-gray-500 px-4">Select your preferred block and instantly secure your allocated bed space.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 5.5 Testimonials Section -->
<section class="py-20 bg-light border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 px-4">
            <h2 class="text-3xl font-bold text-dark">Resident Experiences</h2>
            <div class="w-20 h-1 bg-primary mx-auto mt-4 rounded-full"></div>
            <p class="mt-6 text-gray-500 max-w-2xl mx-auto italic font-medium leading-relaxed">
                "Join hundreds of students who have already transformed their campus living experience with our modern management system."
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative group overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-50 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-700"></div>
                <i data-lucide="quote" class="absolute top-8 right-8 w-10 h-10 text-green-100 group-hover:text-primary/10 transition-colors"></i>
                
                <div class="flex items-center gap-1.5 mb-6 text-yellow-400">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed relative z-10 font-medium">
                    "HostelSys entirely streamlined my room allocation. I picked my block, got verified instantly, and skipped the usual long queues. Highly recommended!"
                </p>
                
                <div class="flex items-center gap-4 border-t border-gray-50 pt-6">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" 
                         alt="Sarah J." 
                         class="w-14 h-14 rounded-2xl object-cover border-4 border-white shadow-md ring-1 ring-gray-100">
                    <div>
                        <h4 class="font-bold text-dark text-base tracking-tight">Sarah Jenkins</h4>
                        <p class="text-xs font-bold text-primary uppercase tracking-widest">Science Faculty</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative group overflow-hidden md:translate-y-6">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-50 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-700"></div>
                <i data-lucide="quote" class="absolute top-8 right-8 w-10 h-10 text-green-100 group-hover:text-primary/10 transition-colors"></i>
                
                <div class="flex items-center gap-1.5 mb-6 text-yellow-400">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed relative z-10 font-medium">
                    "The maintenance ticketing system is a lifesaver. Had an issue with my fan, submitted a ticket, and it was fixed the exact same afternoon. Great service!"
                </p>
                
                <div class="flex items-center gap-4 border-t border-gray-50 pt-6">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" 
                         alt="Michael T." 
                         class="w-14 h-14 rounded-2xl object-cover border-4 border-white shadow-md ring-1 ring-gray-100">
                    <div>
                        <h4 class="font-bold text-dark text-base tracking-tight">Michael Torres</h4>
                        <p class="text-xs font-bold text-primary uppercase tracking-widest">Engineering Dept</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative group overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-50 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-700"></div>
                <i data-lucide="quote" class="absolute top-8 right-8 w-10 h-10 text-green-100 group-hover:text-primary/10 transition-colors"></i>
                
                <div class="flex items-center gap-1.5 mb-6 text-yellow-400">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed relative z-10 font-medium">
                    "Being able to view my room details digitally anytime makes moving in stress-free. The system is so intuitive even for first-time users."
                </p>
                
                <div class="flex items-center gap-4 border-t border-gray-50 pt-6">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" 
                         alt="Elena R." 
                         class="w-14 h-14 rounded-2xl object-cover border-4 border-white shadow-md ring-1 ring-gray-100">
                    <div>
                        <h4 class="font-bold text-dark text-base tracking-tight">Elena Rodriguez</h4>
                        <p class="text-xs font-bold text-primary uppercase tracking-widest">Arts Faculty</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. CTA Section -->
<section class="py-20 bg-primary relative overflow-hidden">
    <!-- Subtle Background Pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#grid)" />
        </svg>
    </div>
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-3xl md:text-5xl font-bold mb-6">Ready to secure your stay?</h2>
        <p class="text-xl text-green-100 mb-10">Join hundreds of students currently leveraging our procedural-based
            HostelSys for a seamless campus lifestyle.</p>
        <a href="public/register.php"
            class="bg-white text-primary font-bold py-4 px-10 rounded-xl text-lg hover:bg-gray-50 transition shadow-xl inline-block hover:scale-105 duration-300">Create
            Free Account</a>
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