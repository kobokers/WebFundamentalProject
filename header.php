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
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script>
        // Check for saved user preference, if any, on load of the website
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
    <header class="bg-slate-800 dark:bg-gray-900 shadow-lg sticky top-0 z-50 transition-colors duration-200">
        <nav class="container mx-auto px-4 relative">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>index.php" class="text-2xl font-bold text-white">
                    OLMS
                </a>

                <!-- Mobile menu button -->
                <button id="navToggle" class="md:hidden text-white focus:outline-none z-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Desktop Navigation -->
                <ul class="hidden md:flex space-x-1 items-center">
                    <li><a href="<?php echo BASE_URL; ?>index.php"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">Home</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>auth/catalog.php"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">Courses</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>about.php"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">About</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">Contact</a>
                    </li>

                    <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
                    <li><a href="<?php echo $dashboardUrl; ?>"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">Dashboard</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>logout.php"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Logout</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php"
                            class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">Login</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Register</a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Dark Mode Toggle (Desktop) -->
                    <li>
                        <button id="theme-toggle" type="button" class="text-gray-200 hover:text-white hover:bg-slate-700 dark:hover:bg-slate-800 focus:outline-none rounded-lg text-sm p-2.5 ml-2 transition">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                        </button>
                    </li>

                </ul>
            </div>

            <!-- Mobile Navigation -->
            <ul id="mobileMenu"
                class="hidden md:hidden absolute left-0 right-0 top-full bg-slate-800 dark:bg-gray-900 shadow-lg space-y-2 py-4 z-40 transition-colors duration-200">
                <li><a href="<?php echo BASE_URL; ?>index.php"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">Home</a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>auth/catalog.php"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">Courses</a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>about.php"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">About</a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>contact.php"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">Contact</a>
                </li>

                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
                <li><a href="<?php echo $dashboardUrl; ?>"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">Dashboard</a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>logout.php"
                        class="block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center mx-2">Logout</a>
                </li>
                <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>auth/login.php"
                        class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 mx-2 transition">Login</a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>auth/register.php"
                        class="block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center mx-2">Register</a>
                </li>
                <?php endif; ?>
                
                <!-- Dark Mode Toggle (Mobile) -->
                <li class="mx-2">
                    <button id="theme-toggle-mobile" type="button" class="flex items-center justify-between w-full text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 dark:hover:bg-slate-800 transition">
                        <span class="text-sm font-medium">Dark Mode</span>
                        <div class="flex items-center">
                            <svg id="theme-toggle-dark-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                            <svg id="theme-toggle-light-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                        </div>
                    </button>
                </li>

            </ul>
        </nav>
    </header>

<!-- Main content starts here -->
    <main class="flex-grow">
