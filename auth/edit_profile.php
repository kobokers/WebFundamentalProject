<?php
session_start();
include("../connection.php");
include("../header.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to edit your profile.";
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied. Only students can edit profiles.";
    header("Location: login.php");
    exit;
}

?>

