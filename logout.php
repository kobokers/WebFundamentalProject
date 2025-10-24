<?php
// Ensure session is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Finally destroy the session
session_destroy();

// Redirect to login page. Use header when possible; fallback to JS.
$loginUrl = 'auth/login.php';
if (!headers_sent()) {
    header('Location: ' . $loginUrl);
    exit;
} else {
    echo "<script>window.location.href = '{$loginUrl}';</script>";
    exit;
}
?>