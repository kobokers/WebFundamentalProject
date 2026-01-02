<?php
session_start();
include("header.php");
?>

<body>
    <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">

        <!-- Hero Section -->
        <div class="relative bg-brand-blue overflow-hidden">
            <div class="absolute inset-0">
                <svg class="absolute bottom-0 left-0 right-0 top-0 h-full w-full stroke-white/10" viewBox="0 0 100 100"
                    preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" />
                </svg>
            </div>
            <div class="relative max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center sm:text-left">
                <h1 class="text-4xl font-extrabold text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
                    Cookie Policy
                </h1>
                <p class="mt-4 max-w-3xl text-xl text-blue-100">
                    Please read these cookie policy carefully before using our platform.
                </p>
            </div>
        </div>

        <!-- Content Section -->
        <div class="flex-grow container mx-auto px-4 lg:px-8 py-12 -mt-10 relative z-10">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-8 lg:p-12">

                    <div class="flex items-start gap-6 mb-8">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-brand-blue text-white">
                                <i class="fa-solid fa-cookie text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Cookie Policy</h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                This Cookie Policy outlines how we collect, use, and protect your personal information
                                when you use our Online Learning Management System (OLMS) website.
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- cookie consent 1 -->
                        <div class="flex gap-4">
                            <span
                                class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">1</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Purpose of Cookies
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400">Cookies are used to improve the user
                                    experience by storing user preferences and tracking website usage.</p>
                            </div>
                        </div>

                        <!-- cookie consent 2 -->
                        <div class="flex gap-4">
                            <span
                                class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">2</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Types of Cookies
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400">Cookies are categorized into three types:
                                    session cookies, persistent cookies, and third-party cookies.</p>
                            </div>
                        </div>

                        <!-- cookie consent 3 -->
                        <div class="flex gap-4">
                            <span
                                class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-brand-blue dark:text-blue-300 font-bold text-sm">3</span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Cookie Consent</h3>
                                <p class="text-gray-600 dark:text-gray-400">Users must explicitly consent to the use of
                                    cookies before accessing the website.</p>
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