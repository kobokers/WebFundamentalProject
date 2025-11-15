<?php
session_start();
include "../config.php";
include "../connection.php";

// Set a default error message for failed login attempts (password or email not found)
$_SESSION['error'] = "Incorrect email or password.";

// Check if POST data is available (Basic check)
if (!isset($_POST['email'], $_POST['password'])) {
    header("Location: login.php");
    exit;
}

// Get POST data
$email = $_POST['email'];
$password = $_POST['password'];

// Query the database
$query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // 1. Check if account active
    if ($user['status'] != 'active') {
        $_SESSION['error'] = "Account inactive. Please contact admin for activation.";
        header("Location: login.php");
        exit; 
    }

    // 2. Verify Password
    if (password_verify($password, $user['password'])) {

        // Credentials are correct - CLEAR the error and proceed
        unset($_SESSION['error']); 
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect by role (using BASE_URL for external redirects)
        if ($user['role'] == 'student') {
            header("Location: " . BASE_URL . "auth/dashboard.php");
        } elseif ($user['role'] == 'lecturer') {
            header("Location: " . BASE_URL . "lecturer/dashboard.php");
        } else {
            header("Location: " . BASE_URL . "admin/dashboard.php");
        }
        exit;
    } 
    // If password_verify fails, the script continues to the final redirect.
} 

header("Location: login.php");
exit;
?>