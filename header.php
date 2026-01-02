<?php
if (file_exists('config.php')) {
    require_once('config.php');
} elseif (file_exists('../config.php')) {
    require_once('../config.php');
} else {
    die("Configuration file not found!");
}

// Determine dashboard URL based on user role
$dashboardUrl = '';
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
if ($isLoggedIn) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            $dashboardUrl = BASE_URL . 'admin/dashboard.php';
            break;
        case 'lecturer':
            $dashboardUrl = BASE_URL . 'lecturer/dashboard.php';
            break;
        case 'student':
            $dashboardUrl = BASE_URL . 'auth/dashboard.php';
            break;
        default:
            $dashboardUrl = BASE_URL . 'auth/dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?></title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'brand': {
                            'blue': '#0056D2',
                            'blue-dark': '#003E99',
                            'blue-light': '#E8F0FE',
                            'blue-hover': '#0047B3',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    
    <script>
        // Check for saved user preference, if any, on load of the website
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 font-inter transition-colors duration-200">
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 transition-colors duration-200">
        <nav class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Left Section: Logo + Desktop Nav -->
                <div class="flex items-center gap-8">
                    <!-- Logo -->
                    <a href="<?php echo BASE_URL; ?>index.php" class="flex items-center gap-2 group flex-shrink-0">
                        <div class="w-9 h-9 bg-brand-blue rounded-lg flex items-center justify-center group-hover:bg-brand-blue-dark transition-colors">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">OLMS</span>
                    </a>

                    <!-- Desktop Navigation (Left Aligned) -->
                    <div class="hidden md:flex items-center gap-2">
                        <!-- Explore Dropdown-style Link -->
                        <?php if (!$isLoggedIn): ?>
                            <a href="<?php echo BASE_URL; ?>auth/catalog.php" 
                               class="flex items-center gap-1 bg-blue-600 text-white hover:bg-blue-700 font-medium px-4 py-2 rounded-lg transition-all mr-2 shadow-sm">
                                <i class="fas fa-th-large text-sm"></i>
                                Explore
                            </a>
                        <?php endif; ?>

                        <?php if ($isLoggedIn && $_SESSION['user_role'] === 'student'): ?>
                            <a href="<?php echo BASE_URL; ?>auth/dashboard.php" 
                               class="text-gray-600 dark:text-gray-300 hover:text-brand-blue dark:hover:text-brand-blue font-medium px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                My Learning
                            </a>
                        <?php endif; ?>
                        
                         <?php if ($isLoggedIn && $_SESSION['user_role'] === 'lecturer'): ?>
                            <a href="<?php echo BASE_URL; ?>lecturer/dashboard.php" 
                               class="text-gray-600 dark:text-gray-300 hover:text-brand-blue dark:hover:text-brand-blue font-medium px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                Instructor Dashboard
                            </a>
                        <?php endif; ?>
                        
                         <?php if ($isLoggedIn && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" 
                               class="text-gray-600 dark:text-gray-300 hover:text-brand-blue dark:hover:text-brand-blue font-medium px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="hidden md:flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" type="button" 
                            class="p-2.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-all">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <?php if ($isLoggedIn): ?>
                            <!-- User Menu -->
                            <div class="relative" id="userMenuContainer">
                                <button id="userMenuBtn" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                    <?php if (!empty($_SESSION['user_profile_picture'])): ?>
                                             <img src="<?php echo BASE_URL; ?>./uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_profile_picture']); ?>" 
                                                  alt="Profile" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                                    <?php else: ?>
                                            <div class="w-8 h-8 bg-brand-blue rounded-full flex items-center justify-center">
                                                <span class="text-white text-sm font-semibold"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
                                            </div>
                                    <?php endif; ?>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium max-w-[100px] truncate hidden lg:block"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </button>
                            
                                <!-- Dropdown Menu -->
                                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?php echo $_SESSION['user_role']; ?></p>
                                    </div>
                                
                                    <a href="<?php echo $dashboardUrl; ?>" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <i class="fas fa-tachometer-alt w-4 text-center text-gray-400"></i>
                                        Dashboard
                                    </a>
                                
                                    <?php if ($_SESSION['user_role'] === 'student'): ?>
                                        <a href="<?php echo BASE_URL; ?>auth/profile_edit.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <i class="fas fa-user-cog w-4 text-center text-gray-400"></i>
                                            Edit Profile
                                        </a>
                                    <?php elseif ($_SESSION['user_role'] === 'lecturer'): ?>
                                        <a href="<?php echo BASE_URL; ?>lecturer/edit_profile.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <i class="fas fa-user-cog w-4 text-center text-gray-400"></i>
                                            Edit Profile
                                        </a>
                                    <?php endif; ?>
                                
                                    <div class="border-t border-gray-100 dark:border-gray-700 my-2"></div>
                                
                                    <a href="<?php echo BASE_URL; ?>logout.php" class="flex items-center gap-3 px-4 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i class="fas fa-sign-out-alt w-4 text-center"></i>
                                        Sign Out
                                    </a>
                                </div>
                            </div>
                    <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/login.php" 
                               class="text-gray-700 dark:text-gray-300 font-medium px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                Log In
                            </a>
                            <a href="<?php echo BASE_URL; ?>auth/register.php" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition-all shadow-sm hover:shadow-md">
                                Join for Free
                            </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button id="navToggle" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="menuIconOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path id="menuIconClose" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="hidden md:hidden pb-4 border-t border-gray-100 dark:border-gray-700 mt-2 pt-4">
                <div class="space-y-1">
                    <a href="<?php echo BASE_URL; ?>index.php" 
                       class="block text-gray-600 dark:text-gray-300 font-medium px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-home w-6 text-center mr-2 text-gray-400"></i>Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/catalog.php" 
                       class="block text-gray-600 dark:text-gray-300 font-medium px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-compass w-6 text-center mr-2 text-gray-400"></i>Explore Courses
                    </a>

                    <?php if ($isLoggedIn): ?>
                            <a href="<?php echo $dashboardUrl; ?>" 
                               class="block text-gray-600 dark:text-gray-300 font-medium px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                <i class="fas fa-tachometer-alt w-6 text-center mr-2 text-gray-400"></i>Dashboard
                            </a>
                        
                            <?php if ($_SESSION['user_role'] === 'student'): ?>
                                <a href="<?php echo BASE_URL; ?>auth/dashboard.php" 
                                   class="block text-brand-blue dark:text-blue-400 font-semibold px-4 py-3 rounded-lg bg-brand-blue-light dark:bg-blue-900/30 transition-all">
                                    <i class="fas fa-book-open w-6 text-center mr-2"></i>My Learning
                                </a>
                            <?php endif; ?>
                        
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <!-- Dark Mode Toggle Mobile -->
                            <button id="theme-toggle-mobile" type="button" 
                                    class="w-full flex items-center justify-between text-gray-600 dark:text-gray-300 font-medium px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                <span><i class="fas fa-moon w-6 text-center mr-2 text-gray-400"></i>Dark Mode</span>
                                <div class="flex items-center">
                                    <svg id="theme-toggle-dark-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                                    </svg>
                                    <svg id="theme-toggle-light-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </button>
                        
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <a href="<?php echo BASE_URL; ?>logout.php" 
                               class="block text-red-600 dark:text-red-400 font-medium px-4 py-3 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                <i class="fas fa-sign-out-alt w-6 text-center mr-2"></i>Sign Out
                            </a>
                    <?php else: ?>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <!-- Dark Mode Toggle Mobile -->
                            <button id="theme-toggle-mobile-guest" type="button" 
                                    class="w-full flex items-center justify-between text-gray-600 dark:text-gray-300 font-medium px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                <span><i class="fas fa-moon w-6 text-center mr-2 text-gray-400"></i>Dark Mode</span>
                                <div class="flex items-center">
                                    <svg id="theme-toggle-dark-icon-mobile-guest" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                                    </svg>
                                    <svg id="theme-toggle-light-icon-mobile-guest" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </button>
                        
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <div class="flex flex-col gap-2 px-4">
                                <a href="<?php echo BASE_URL; ?>auth/login.php" 
                                   class="w-full text-center text-gray-700 dark:text-gray-300 font-medium py-3 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                                    Log In
                                </a>
                                <a href="<?php echo BASE_URL; ?>auth/register.php" 
                                   class="w-full text-center bg-brand-blue hover:bg-brand-blue-dark text-white font-semibold py-3 rounded-lg transition-all">
                                    Join for Free
                                </a>
                            </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

<!-- Main content starts here -->
    <main class="flex-grow">
