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
                            'blue-light': '#E8F0FE',
                            'blue-hover': '#0047B3',
                            'purple': '#7e22ce', // Added for admin
                            'purple-dark': '#581c87', // Added for admin hover
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
                        <?php
                        $logoColorClass = 'bg-brand-blue group-hover:bg-brand-blue-dark'; // Default
                        if (isset($_SESSION['user_role'])) {
                             if ($_SESSION['user_role'] === 'admin') {
                                 $logoColorClass = 'bg-brand-blue-dark group-hover:bg-gray-900';
                             } elseif ($_SESSION['user_role'] === 'lecturer') {
                                 $logoColorClass = 'bg-brand-purple group-hover:bg-brand-purple-dark'; 
                             }
                        }
                        ?>
                        <div class="w-9 h-9 <?php echo $logoColorClass; ?> rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">OLMS</span>
                    </a>

                    <!-- Desktop Navigation (Left Aligned) -->
                    <div class="hidden md:flex items-center gap-2">
                        <?php if(!$isLoggedIn): ?>
                        <a href="<?php echo BASE_URL; ?>auth/catalog.php" 
                        class="overflow-hidden relative w-32 p-2 h-10 bg-blue-600 text-white border-none rounded-lg text-lg font-bold cursor-pointer z-10 group mr-2 shadow-sm flex items-center justify-center decoration-none">
                            
                            <span class="flex items-center gap-2 text-base">
                                <i class="fas fa-th-large text-sm"></i> Explore
                            </span>

                            <span class="absolute w-36 h-32 -top-8 -left-2 bg-white rotate-12 transform scale-x-0 group-hover:scale-x-100 transition-transform group-hover:duration-500 duration-1000 origin-left"></span>
                            <span class="absolute w-36 h-32 -top-8 -left-2 bg-blue-300 rotate-12 transform scale-x-0 group-hover:scale-x-100 transition-transform group-hover:duration-700 duration-700 origin-left"></span>
                            <span class="absolute w-36 h-32 -top-8 -left-2 bg-blue-800 rotate-12 transform scale-x-0 group-hover:scale-x-100 transition-transform group-hover:duration-1000 duration-500 origin-left"></span>

                            <span class="group-hover:opacity-100 group-hover:duration-1000 duration-100 opacity-0 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10 text-white text-base font-bold whitespace-nowrap">
                                <i class="fas fa-th-large text-sm"></i> Explore
                            </span>
                        </a>
                        <?php endif; ?>
                        
                         <?php if($isLoggedIn && $_SESSION['user_role'] === 'lecturer'): ?>
                        <a href="<?php echo BASE_URL; ?>lecturer/dashboard.php" 
                           class="text-gray-600 dark:text-gray-300 hover:text-brand-blue dark:hover:text-brand-blue font-medium px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                            Lecturer     Dashboard
                        </a>
                        <?php endif; ?>
                        
                         <?php if($isLoggedIn && $_SESSION['user_role'] === 'admin'): ?>
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
                    <div class="scale-75 origin-right">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input id="theme-toggle" class="sr-only peer" value="" type="checkbox" />
                            <div
                                class="w-24 h-12 rounded-full ring-0 peer duration-500 outline-none bg-gray-200 overflow-hidden before:flex before:items-center before:justify-center after:flex after:items-center after:justify-center before:content-['☀️'] before:absolute before:h-10 before:w-10 before:top-1/2 before:bg-white before:rounded-full before:left-1 before:-translate-y-1/2 before:transition-all before:duration-700 peer-checked:before:opacity-0 peer-checked:before:rotate-90 peer-checked:before:-translate-y-full shadow-lg shadow-gray-400 peer-checked:shadow-lg peer-checked:shadow-gray-700 peer-checked:bg-[#383838] after:content-['🌑'] after:absolute after:bg-[#1d1d1d] after:rounded-full after:top-[4px] after:right-1 after:translate-y-full after:w-10 after:h-10 after:opacity-0 after:transition-all after:duration-700 peer-checked:after:opacity-100 peer-checked:after:rotate-180 peer-checked:after:translate-y-0"
                            ></div>
                        </label>
                    </div>

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
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">
                                    <i class="fas fa-moon w-6 text-center mr-2 text-gray-400"></i>Dark Mode
                                </span>
                                <div class="scale-75 origin-right">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input id="theme-toggle-mobile" class="sr-only peer" value="" type="checkbox" />
                                        <div
                                            class="w-24 h-12 rounded-full ring-0 peer duration-500 outline-none bg-gray-200 overflow-hidden before:flex before:items-center before:justify-center after:flex after:items-center after:justify-center before:content-['☀️'] before:absolute before:h-10 before:w-10 before:top-1/2 before:bg-white before:rounded-full before:left-1 before:-translate-y-1/2 before:transition-all before:duration-700 peer-checked:before:opacity-0 peer-checked:before:rotate-90 peer-checked:before:-translate-y-full shadow-lg shadow-gray-400 peer-checked:shadow-lg peer-checked:shadow-gray-700 peer-checked:bg-[#383838] after:content-['🌑'] after:absolute after:bg-[#1d1d1d] after:rounded-full after:top-[4px] after:right-1 after:translate-y-full after:w-10 after:h-10 after:opacity-0 after:transition-all after:duration-700 peer-checked:after:opacity-100 peer-checked:after:rotate-180 peer-checked:after:translate-y-0"
                                        ></div>
                                    </label>
                                </div>
                            </div>
                        
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <a href="<?php echo BASE_URL; ?>logout.php" 
                               class="block text-red-600 dark:text-red-400 font-medium px-4 py-3 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                <i class="fas fa-sign-out-alt w-6 text-center mr-2"></i>Sign Out
                            </a>
                    <?php else: ?>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                        
                            <!-- Dark Mode Toggle Mobile -->
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">
                                    <i class="fas fa-moon w-6 text-center mr-2 text-gray-400"></i>Dark Mode
                                </span>
                                <div class="scale-75 origin-right">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input id="theme-toggle-mobile-guest" class="sr-only peer" value="" type="checkbox" />
                                        <div
                                            class="w-24 h-12 rounded-full ring-0 peer duration-500 outline-none bg-gray-200 overflow-hidden before:flex before:items-center before:justify-center after:flex after:items-center after:justify-center before:content-['☀️'] before:absolute before:h-10 before:w-10 before:top-1/2 before:bg-white before:rounded-full before:left-1 before:-translate-y-1/2 before:transition-all before:duration-700 peer-checked:before:opacity-0 peer-checked:before:rotate-90 peer-checked:before:-translate-y-full shadow-lg shadow-gray-400 peer-checked:shadow-lg peer-checked:shadow-gray-700 peer-checked:bg-[#383838] after:content-['🌑'] after:absolute after:bg-[#1d1d1d] after:rounded-full after:top-[4px] after:right-1 after:translate-y-full after:w-10 after:h-10 after:opacity-0 after:transition-all after:duration-700 peer-checked:after:opacity-100 peer-checked:after:rotate-180 peer-checked:after:translate-y-0"
                                        ></div>
                                    </label>
                                </div>
                            </div>
                        
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
