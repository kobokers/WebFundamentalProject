<?php
session_start();
include("header.php");
?>

<body>
    <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        
        <!-- Hero Section -->
        <div class="relative bg-brand-blue overflow-hidden">
            <div class="absolute inset-0">
                <svg class="absolute bottom-0 left-0 right-0 top-0 h-full w-full stroke-white/10" viewBox="0 0 100 100" preserveAspectRatio="none">
                     <path d="M0 100 C 20 0 50 0 100 100 Z" />
                </svg>
            </div>
            <div class="relative max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center sm:text-left">
                <h1 class="text-4xl font-extrabold text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
                    Privacy Policy
                </h1>
                <p class="mt-4 max-w-3xl text-xl text-blue-100">
                    Please read these privacy policy carefully before using our platform.
                </p>
            </div>
        </div>

        <!-- Content Section -->
        <div class="flex-grow container mx-auto px-4 lg:px-8 py-12 -mt-10 relative z-10">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-8 lg:p-12">
                    
                    <div class="flex items-start gap-6 mb-8">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-brand-blue text-white">
                                <i class="fa-solid fa-shield-halved text-xl"></i>
                            </div>
                        </div>
                        <div>
                             <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Privacy Policy</h2>
                             <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                This Privacy Policy outlines how we collect, use, and protect your personal information when you use our Online Learning Management System (OLMS) website.
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- privacy 1 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">1</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Data Collection</h3>
                                <p class="text-gray-600 dark:text-gray-400">We collect personal information such as your name, email address, and contact details when you register for an account or make a purchase.</p>
                            </div>
                        </div>

                        <!-- privacy 2 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">2</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Data Usage</h3>
                                <p class="text-gray-600 dark:text-gray-400">We use your personal information to provide and improve our services, including delivering courses, processing payments, and sending you updates and notifications.</p>
                            </div>
                        </div>

                        <!-- privacy 3 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">3</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Data Security</h3>
                                <p class="text-gray-600 dark:text-gray-400">We implement strong security measures to protect your personal information from unauthorized access, disclosure, or loss.</p>
                            </div>
                        </div>

                        <!-- privacy 4 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">4</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Data Retention</h3>
                                <p class="text-gray-600 dark:text-gray-400">We retain your personal information for as long as necessary to fulfill our obligations and comply with legal requirements.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
include("footer.php");
?>