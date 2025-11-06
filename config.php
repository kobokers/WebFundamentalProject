<?php
// ===============================
// Basic Site Configuration
// ===============================

// Website name
define('SITE_NAME', 'OLMS');

// Base URL
define('BASE_URL', '/WebFundamentalProject/');

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'olms');

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}
?>