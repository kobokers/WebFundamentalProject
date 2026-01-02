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
                    Terms of Service
                </h1>
                <p class="mt-4 max-w-3xl text-xl text-blue-100">
                    Please read these terms carefully before using our platform.
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
                                <i class="fas fa-file-contract text-xl"></i>
                            </div>
                        </div>
                        <div>
                             <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">User Agreement</h2>
                             <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                By accessing and using the Online Learning Management System (OLMS) website, you agree to comply with and be bound by the following terms and conditions of use.
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- Term 1 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">1</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Age Requirement</h3>
                                <p class="text-gray-600 dark:text-gray-400">You must be at least 18 years old to use this website and access our courses.</p>
                            </div>
                        </div>

                        <!-- Term 2 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">2</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Lawful Use</h3>
                                <p class="text-gray-600 dark:text-gray-400">You must not use this website for any illegal, unauthorized, or unethical purposes.</p>
                            </div>
                        </div>

                        <!-- Term 3 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">3</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Intellectual Property</h3>
                                <p class="text-gray-600 dark:text-gray-400">You must not upload or distribute content that is copyrighted or trademarked without explicit permission.</p>
                            </div>
                        </div>

                        <!-- Term 4 -->
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">4</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">System Security</h3>
                                <p class="text-gray-600 dark:text-gray-400">You must not affect the integrity of our system, including distributing viruses or malicious software.</p>
                            </div>
                        </div>

                         <!-- Term 5 -->
                         <div class="flex gap-4">
                            <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">5</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Community Standards</h3>
                                <p class="text-gray-600 dark:text-gray-400">You must treat all users with respect. Harassment, intimidation, or hate speech will not be tolerated.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                             <i class="fas fa-exclamation-triangle"></i>
                             <span class="font-semibold">Important Note</span>
                        </div>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Failure to comply with these terms may result in the immediate termination of your account. If you do not agree to these terms, please discontinue use of the website immediately.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
include("footer.php");
?>