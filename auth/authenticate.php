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

    // Check if account active
    if ($user['status'] != 'active') {
        echo "<p class='text-yellow-500'>Your account is not activated by admin.</p>";
        echo "<script> window.history.back(); </script>";
        exit;
    }

    // If password stored as plain text for testing will use hashed version in production
    if ($password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect by role
        if ($user['role'] == 'student') {
            header("Location: ../auth/dashboard.php");
        } elseif ($user['role'] == 'lecturer') {
            header("Location: ../lecturer/dashboard.php");
        } else {
            header("Location: ../admin/dashboard.php");
        }
        exit;
    } else {
        echo "<p class='text-red-500'>Incorrect password.</p>";
    }
} else {
    echo "<p class='text-red-500'>Email not found.</p>";
}
