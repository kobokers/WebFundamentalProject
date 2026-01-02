<?php
session_start();
$pageTitle = "Home - OLMS";
include('header.php');
?>

<main class="bg-white dark:bg-gray-900 font-sans antialiased overflow-x-hidden transition-colors duration-200 relative">

    <section
        class="relative min-h-screen lg:min-h-[95vh] flex items-center overflow-hidden bg-[#F8F7FF] dark:bg-gray-900 py-20 lg:py-0">
        <div
            class="absolute top-[-10%] right-[-5%] w-[300px] md:w-[600px] h-[300px] md:h-[600px] bg-indigo-200 dark:bg-indigo-900 rounded-full blur-[80px] md:blur-[120px] opacity-40">
        </div>
        <div
            class="absolute bottom-[-10%] left-[-5%] w-[250px] md:w-[500px] h-[250px] md:h-[500px] bg-purple-200 dark:bg-purple-900 rounded-full blur-[70px] md:blur-[100px] opacity-30">
        </div>

        <div
            class="relative z-10 max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center w-full">

            <div class="max-w-2xl text-center lg:text-left">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-[10px] md:text-xs font-bold uppercase tracking-widest mb-6 md:mb-8">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Live Learning Platform
                </div>

                <h1
                    class="text-4xl md:text-5xl lg:text-7xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.1] mb-6 md:mb-8">
                    Master your <br>
                    <span class="text-indigo-600 dark:text-indigo-400">skills</span> with OLMS.
                </h1>

                <p
                    class="text-lg md:text-xl text-slate-600 dark:text-gray-300 leading-relaxed max-w-lg mb-8 md:mb-10 mx-auto lg:mx-0">
                    The most intuitive online learning management system. Join thousands of students mastering new
                    technologies every day.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6 md:gap-8">
                    <button
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-10 py-4 md:py-5 rounded-2xl shadow-2xl shadow-indigo-200 dark:shadow-indigo-900/50 transition-all hover:-translate-y-1 active:scale-95 text-lg">
                        Get started free
                    </button>
                    <div class="flex flex-col items-center sm:items-start">
                        <div class="flex text-yellow-400 text-xl tracking-tighter">★★★★★</div>
                        <span
                            class="text-[10px] text-slate-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">5-Star
                            Community Rating</span>
                    </div>
                </div>

                <p class="mt-8 text-xs md:text-sm text-slate-400 dark:text-gray-500 font-medium">No credit card required
                    • Instant access to 50+ courses</p>
            </div>

            <div
                class="relative mt-12 lg:mt-0 h-auto min-h-[450px] md:min-h-[550px] lg:h-[650px] flex items-center justify-center">

                <div
                    class="relative w-full max-w-[520px] bg-white dark:bg-gray-800 rounded-[1.5rem] md:rounded-[2.5rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.12)] dark:shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] p-6 md:p-10 z-10 border border-slate-100 dark:border-gray-700 mx-auto">

                    <div
                        class="absolute -left-4 md:-left-12 lg:-left-20 bottom-24 md:bottom-32 bg-white dark:bg-gray-800 rounded-xl md:rounded-2xl shadow-[0_20px_50px_-10px_rgba(0,0,0,0.2)] dark:shadow-[0_20px_50px_-10px_rgba(0,0,0,0.5)] p-4 md:p-5 w-[180px] md:w-[260px] z-30 border-t-4 border-indigo-500 animate-float-slow">
                        <p
                            class="text-[9px] md:text-xs text-slate-600 dark:text-gray-300 leading-relaxed font-semibold italic">
                            "Education is the most powerful weapon which you can use to change the world."
                        </p>
                        <p
                            class="mt-2 md:mt-3 text-[7px] md:text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em]">
                            — Nelson Mandela</p>
                    </div>

                    <div class="flex justify-between items-center mb-6 md:mb-8">
                        <h3 class="font-black text-slate-800 dark:text-white text-base md:text-lg">Course Overview</h3>
                        <div class="flex gap-2">
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-slate-100 dark:bg-gray-700"></div>
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-slate-100 dark:bg-gray-700"></div>
                        </div>
                    </div>

                    <div
                        class="relative aspect-video bg-indigo-50/50 dark:bg-indigo-900/20 rounded-2xl md:rounded-3xl mb-6 md:mb-10 overflow-hidden border border-indigo-100/50 dark:border-indigo-800/50">
                        <div id="image-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                            <div class="min-w-full h-full flex items-center justify-center p-4 md:p-6">
                                <img src="<?php echo BASE_URL; ?>uploads/asset/img4.png" alt="Analytics"
                                    class="w-full h-full object-contain">
                            </div>
                            <div class="min-w-full h-full flex items-center justify-center p-4 md:p-6">
                                <img src="<?php echo BASE_URL; ?>uploads/asset/img5.avif" alt="Curriculum"
                                    class="w-full h-full object-contain">
                            </div>
                            <div class="min-w-full h-full flex items-center justify-center p-4 md:p-6">
                                <img src="<?php echo BASE_URL; ?>uploads/asset/img6.jpg" alt="Progress"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:gap-6">
                        <div
                            class="p-4 md:p-5 bg-slate-50 dark:bg-gray-700/50 rounded-xl md:rounded-2xl border border-slate-100 dark:border-gray-600">
                            <p
                                class="text-[8px] md:text-[10px] uppercase tracking-widest text-slate-400 dark:text-gray-400 font-bold mb-1 md:mb-2">
                                Active Students</p>
                            <p class="text-xl md:text-3xl font-black text-slate-800 dark:text-white tracking-tighter">
                                1,240</p>
                        </div>
                        <div
                            class="p-4 md:p-5 bg-indigo-50/50 dark:bg-indigo-900/30 rounded-xl md:rounded-2xl border border-indigo-100 dark:border-indigo-800">
                            <p
                                class="text-[8px] md:text-[10px] uppercase tracking-widest text-indigo-400 font-bold mb-1 md:mb-2">
                                Completion</p>
                            <p
                                class="text-xl md:text-3xl font-black text-indigo-600 dark:text-indigo-400 tracking-tighter">
                                84%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 lg:py-32 bg-white dark:bg-gray-900 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-16 lg:mb-24 gap-8">
                <div class="max-w-2xl text-center md:text-left">
                    <h2 class="text-3xl lg:text-5xl font-black text-slate-900 dark:text-white mb-4 md:mb-6">Explore by
                        category.</h2>
                    <p class="text-lg lg:text-xl text-slate-500 dark:text-gray-400 leading-relaxed font-medium">Discover
                        expert-led courses across various disciplines.</p>
                </div>
                <a href="#"
                    class="group inline-flex items-center text-indigo-600 dark:text-indigo-400 font-black text-lg transition-all">
                    View All Categories
                    <svg class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-10">
                <a href="<?php echo BASE_URL; ?>auth/catalog.php?level=&category=Design&instructor="
                    class="group relative p-8 md:p-12 rounded-[2rem] md:rounded-[3rem] bg-slate-50 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700 hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.06)] dark:hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.3)] transition-all duration-500 cursor-pointer border-2 border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div
                        class="w-16 h-16 md:w-20 md:h-20 bg-orange-500 rounded-[1.25rem] md:rounded-[1.75rem] flex items-center justify-center text-white mb-8 md:mb-10 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-2xl shadow-orange-200 dark:shadow-orange-900/50">
                        <svg class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.172-1.172a4 4 0 115.656 5.656L10 17.657" />
                        </svg>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white mb-2">UI/UX Design</h3>
                    <p
                        class="text-slate-400 dark:text-gray-500 font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em]">
                        120+ Courses Available</p>
                </a>

                <a href="<?php echo BASE_URL; ?>auth/catalog.php?level=&category=Programming&instructor="
                    class="group relative p-8 md:p-12 rounded-[2rem] md:rounded-[3rem] bg-slate-50 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700 hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.06)] dark:hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.3)] transition-all duration-500 cursor-pointer border-2 border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div
                        class="w-16 h-16 md:w-20 md:h-20 bg-blue-500 rounded-[1.25rem] md:rounded-[1.75rem] flex items-center justify-center text-white mb-8 md:mb-10 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-2xl shadow-blue-200 dark:shadow-blue-900/50">
                        <svg class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white mb-2">Development</h3>
                    <p
                        class="text-slate-400 dark:text-gray-500 font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em]">
                        250+ Courses Available</p>
                </a>

                <a href="<?php echo BASE_URL; ?>auth/catalog.php?level=&category=Language&instructor="
                    class="group relative p-8 md:p-12 rounded-[2rem] md:rounded-[3rem] bg-slate-50 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700 hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.06)] dark:hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.3)] transition-all duration-500 cursor-pointer border-2 border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div
                        class="w-16 h-16 md:w-20 md:h-20 bg-green-500 rounded-[1.25rem] md:rounded-[1.75rem] flex items-center justify-center text-white mb-8 md:mb-10 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-2xl shadow-green-200 dark:shadow-green-900/50">
                        <svg class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        </svg>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white mb-2">Language</h3>
                    <p
                        class="text-slate-400 dark:text-gray-500 font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em]">
                        85+ Courses Available</p>
                </a>

                <a href="<?php echo BASE_URL; ?>auth/catalog.php?level=&category=Business&instructor="
                    class="group relative p-8 md:p-12 rounded-[2rem] md:rounded-[3rem] bg-slate-50 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700 hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.06)] dark:hover:shadow-[0_50px_80px_-20px_rgba(0,0,0,0.3)] transition-all duration-500 cursor-pointer border-2 border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div
                        class="w-16 h-16 md:w-20 md:h-20 bg-purple-500 rounded-[1.25rem] md:rounded-[1.75rem] flex items-center justify-center text-white mb-8 md:mb-10 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-2xl shadow-purple-200 dark:shadow-purple-900/50">
                        <svg class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white mb-2">Business</h3>
                    <p
                        class="text-slate-400 dark:text-gray-500 font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em]">
                        150+ Courses Available</p>
                </a>
            </div>
        </div>
    </section>
</main>

<style>
    /* Professional Floating Animation */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .animate-float-slow {
        animation: float 6s ease-in-out infinite;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Image Slider
        const slider = document.getElementById('image-slider');
        if (slider) {
            const slides = slider.children;
            let index = 0;

            function startSlider() {
                setInterval(() => {
                    index++;
                    if (index >= slides.length) index = 0;
                    slider.style.transform = `translateX(-${index * 100}%)`;
                }, 4000);
            }
            startSlider();
        }
    });
</script>

<?php include('footer.php'); ?>