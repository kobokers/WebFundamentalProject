<?php
// Include config from wherever we are
if (file_exists('config.php')) {
    require_once('config.php');
} elseif (file_exists('../config.php')) {
    require_once('../config.php');
} else {
    die("Configuration file not found!");
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
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <header class="bg-slate-800 shadow-lg sticky top-0 z-50">
        <nav class="container mx-auto px-4 relative">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>index.php" class="text-2xl font-bold text-white">
                    OLMS
                </a>

                <!-- Mobile menu button -->
                <button id="navToggle" class="md:hidden text-white focus:outline-none z-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Desktop Navigation -->
                <ul class="hidden md:flex space-x-1 items-center">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>courses.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">Courses</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">About</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">Contact</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/dashboard.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 transition">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Mobile Navigation -->
            <ul id="mobileMenu" class="hidden md:hidden absolute left-0 right-0 top-full bg-slate-800 shadow-lg space-y-2 py-4 z-40">
                <li><a href="<?php echo BASE_URL; ?>index.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>courses.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">Courses</a></li>
                <li><a href="<?php echo BASE_URL; ?>about.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">About</a></li>
                <li><a href="<?php echo BASE_URL; ?>contact.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">Contact</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/dashboard.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/logout.php" class="block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center mx-2">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="block text-gray-200 hover:text-white px-4 py-2 rounded hover:bg-slate-700 mx-2">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center mx-2">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <!-- Main content starts here -->
    <main class="flex-grow">