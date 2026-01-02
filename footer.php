</main>

<footer
    class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto transition-colors duration-200">
    <!-- Main Footer -->
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Brand Column -->
            <div class="lg:col-span-1">
                <a href="<?php echo BASE_URL; ?>index.php" class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-brand-blue rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">OLMS</span>
                </a>
                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-4">
                    Online Learning Management System - Your gateway to quality education and skill development.
                </p>
                <!-- Social Links -->
                <div class="flex gap-4 items-center">
                    <a href="https://www.facebook.com" target="_blank"
                        class="group relative flex items-center justify-center w-10 h-10">
                        <div
                            class="text-gray-500 dark:text-gray-400 transform transition-all duration-200 group-hover:scale-125 group-hover:text-blue-600">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </div>
                        <span
                            class="absolute top-10 left-[50%] -translate-x-[50%] z-20 origin-top scale-0 px-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 py-2 text-sm font-bold shadow-md transition-all duration-300 ease-in-out group-hover:scale-100 text-gray-900 dark:text-white
                        before:absolute before:-top-1.5 before:left-1/2 before:-translate-x-1/2 before:w-3 before:h-3 before:rotate-45 before:border-t before:border-l before:border-gray-300 before:dark:border-gray-600 before:bg-white before:dark:bg-gray-800">
                            Facebook
                        </span>
                    </a>

                    <a href="https://x.com/?lang=en" target="_blank"
                        class="group relative flex items-center justify-center w-10 h-10">
                        <div
                            class="text-gray-500 dark:text-gray-400 transform transition-all duration-200 group-hover:scale-125 group-hover:text-sky-500">
                            <i class="fab fa-twitter text-xl"></i>
                        </div>
                        <span
                            class="absolute top-10 left-[50%] -translate-x-[50%] z-20 origin-top scale-0 px-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 py-2 text-sm font-bold shadow-md transition-all duration-300 ease-in-out group-hover:scale-100 text-gray-900 dark:text-white
                        before:absolute before:-top-1.5 before:left-1/2 before:-translate-x-1/2 before:w-3 before:h-3 before:rotate-45 before:border-t before:border-l before:border-gray-300 before:dark:border-gray-600 before:bg-white before:dark:bg-gray-800">
                            Twitter
                        </span>
                    </a>

                    <a href="https://linkedin.com" target="_blank"
                        class="group relative flex items-center justify-center w-10 h-10">
                        <div
                            class="text-gray-500 dark:text-gray-400 transform transition-all duration-200 group-hover:scale-125 group-hover:text-blue-700">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </div>
                        <span
                            class="absolute top-10 left-[50%] -translate-x-[50%] z-20 origin-top scale-0 px-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 py-2 text-sm font-bold shadow-md transition-all duration-300 ease-in-out group-hover:scale-100 text-gray-900 dark:text-white
                        before:absolute before:-top-1.5 before:left-1/2 before:-translate-x-1/2 before:w-3 before:h-3 before:rotate-45 before:border-t before:border-l before:border-gray-300 before:dark:border-gray-600 before:bg-white before:dark:bg-gray-800">
                            LinkedIn
                        </span>
                    </a>

                    <a href="https://github.com/kobokers" target="_blank"
                        class="group relative flex items-center justify-center w-10 h-10">
                        <div
                            class="text-gray-500 dark:text-gray-400 transform transition-all duration-200 group-hover:scale-125 group-hover:text-black dark:group-hover:text-white">
                            <i class="fab fa-github text-xl"></i>
                        </div>
                        <span
                            class="absolute top-10 left-[50%] -translate-x-[50%] z-20 origin-top scale-0 px-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 py-2 text-sm font-bold shadow-md transition-all duration-300 ease-in-out group-hover:scale-100 text-gray-900 dark:text-white
                        before:absolute before:-top-1.5 before:left-1/2 before:-translate-x-1/2 before:w-3 before:h-3 before:rotate-45 before:border-t before:border-l before:border-gray-300 before:dark:border-gray-600 before:bg-white before:dark:bg-gray-800">
                            GitHub
                        </span>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Quick Links</h4>
                <ul class="space-y-3">
                    <li>
                        <a href="<?php echo BASE_URL; ?>auth/catalog.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Browse Courses</span>
                        </a>
                    </li>
                    <li>
                        <?php if (!$isLoggedIn): ?>
                            <a href="<?php echo BASE_URL; ?>auth/login.php"
                                class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                                <span
                                    class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                    <i class="fas fa-chevron-right text-xs mr-2"></i>
                                </span>
                                <span>Sign In</span>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/dashboard.php"
                                class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                                <span
                                    class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                    <i class="fas fa-chevron-right text-xs mr-2"></i>
                                </span>
                                <span>Dashboard</span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if (!$isLoggedIn): ?>
                            <a href="<?php echo BASE_URL; ?>auth/register.php"
                                class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                                <span
                                    class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                    <i class="fas fa-chevron-right text-xs mr-2"></i>
                                </span>
                                <span>Create Account</span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>about.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>About Us</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Support</h4>
                <ul class="space-y-3">
                    <li>
                        <a href="<?php echo BASE_URL; ?>help.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Help Center</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>contact.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Contact Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>faq.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>FAQs</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Legal</h4>
                <ul class="space-y-3">
                    <li>
                        <a href="<?php echo BASE_URL; ?>tos.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Terms of Service</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>privacy.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Privacy Policy</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>cookie.php"
                            class="group flex items-center text-gray-600 dark:text-gray-400 hover:text-brand-blue dark:hover:text-brand-blue text-sm transition-colors">
                            <span
                                class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-xs group-hover:opacity-100 transition-all duration-300 ease-in-out">
                                <i class="fas fa-chevron-right text-xs mr-2"></i>
                            </span>
                            <span>Cookie Policy</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> OLMS. All rights reserved.
                </p>
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    Developed with <span class="text-red-500">♥</span> by
                    <a href="https://github.com/kobokers" target="_blank" class="text-brand-blue hover:underline">onyx
                        and team</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<script>
    // Mobile menu toggle 
    const navToggle = document.getElementById('navToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuIconOpen = document.getElementById('menuIconOpen');
    const menuIconClose = document.getElementById('menuIconClose');

    if (navToggle && mobileMenu) {
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle('hidden');
            menuIconOpen.classList.toggle('hidden');
            menuIconClose.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                menuIconOpen.classList.remove('hidden');
                menuIconClose.classList.add('hidden');
            }
        });
    }

    // User dropdown menu
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }

    // Dark mode toggle logic
    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
    var themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
    var themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');
    var themeToggleDarkIconMobileGuest = document.getElementById('theme-toggle-dark-icon-mobile-guest');
    var themeToggleLightIconMobileGuest = document.getElementById('theme-toggle-light-icon-mobile-guest');

    // Update toggle icons
    function updateThemeIcons() {
        const isDark = localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isDark) {
            if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
            if (themeToggleLightIconMobile) themeToggleLightIconMobile.classList.remove('hidden');
            if (themeToggleDarkIconMobile) themeToggleDarkIconMobile.classList.add('hidden');
            if (themeToggleLightIconMobileGuest) themeToggleLightIconMobileGuest.classList.remove('hidden');
            if (themeToggleDarkIconMobileGuest) themeToggleDarkIconMobileGuest.classList.add('hidden');
        } else {
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
            if (themeToggleLightIcon) themeToggleLightIcon.classList.add('hidden');
            if (themeToggleDarkIconMobile) themeToggleDarkIconMobile.classList.remove('hidden');
            if (themeToggleLightIconMobile) themeToggleLightIconMobile.classList.add('hidden');
            if (themeToggleDarkIconMobileGuest) themeToggleDarkIconMobileGuest.classList.remove('hidden');
            if (themeToggleLightIconMobileGuest) themeToggleLightIconMobileGuest.classList.add('hidden');
        }
    }

    updateThemeIcons();

    var themeToggleBtn = document.getElementById('theme-toggle');
    var themeToggleBtnMobile = document.getElementById('theme-toggle-mobile');
    var themeToggleBtnMobileGuest = document.getElementById('theme-toggle-mobile-guest');

    function toggleDarkMode() {
        // Toggle dark class
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
        updateThemeIcons();
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleDarkMode);
    }

    if (themeToggleBtnMobile) {
        themeToggleBtnMobile.addEventListener('click', toggleDarkMode);
    }

    if (themeToggleBtnMobileGuest) {
        themeToggleBtnMobileGuest.addEventListener('click', toggleDarkMode);
    }
</script>
</body>

</html>