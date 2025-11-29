<?php
session_start();
include "../config.php";
include "../connection.php";

// Get POST data
$email = $_POST['email'];
$password = $_POST['password'];

// Query the database
$query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Trim status to remove any whitespace
    $user_status = trim(strtolower($user['status']));

    // Check if account is pending approval
    if ($user_status === 'pending') {
        $_SESSION['error'] = "Your account is pending approval. Please wait for admin activation.";
        header("Location: login.php");
        exit;
    }

    // Check if account is active
    if ($user_status !== 'active') {
        $_SESSION['error'] = "Your account is inactive. Please contact admin for assistance.";
        header("Location: login.php");
        exit;
    }

    if (password_verify($password, $user['password'])) {

        // Credentials are correct - proceed to set sessions and redirect
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] == 'student') {
            header("Location: dashboard.php"); 
        } elseif ($user['role'] == 'lecturer') {
            header("Location: ../lecturer/dashboard.php");
        } else {
            header("Location: ../admin/dashboard.php");
        }
        exit;
    } else {
        // Handle incorrect password with session and redirect
        $_SESSION['error'] = "Incorrect password.";
    }
} else {
    // Handle email not found with session and redirect
    $_SESSION['error'] = "Email not found.";
}

// Final redirection for any failed login attempt
if (isset($_SESSION['error'])) {
    header("Location: login.php");
    exit;
}
?>