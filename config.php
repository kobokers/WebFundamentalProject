<?php
// ===============================
// Basic Site Configuration
// ===============================

// Website name
define('SITE_NAME', 'OLMS');

// Base URL (adjust according to your local XAMPP path)
define('BASE_URL', '/WebFundamentalProject/');

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'olms');

// ===============================
// Common Utility Functions
// ===============================

// Helper: redirect to a specific page
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}
?>
