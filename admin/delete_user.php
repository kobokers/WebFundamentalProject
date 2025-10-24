<?php
include("../connection.php");
session_start(); // Assuming you need session_start() in the delete script too

if(!isset($_SESSION["user_id"])){
    echo "<script> alert('Please Login'); </script>";
    header("Location: ../index.php");
    exit(); // Always exit after a header redirect
}

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script> alert('Access denied.'); </script>";
    header("Location: ../index.php");
    exit();
}

// *** CHANGE THIS LINE ***
if(isset($_GET['id'])) { 
    // *** CHANGE THIS LINE ***
    $user_id = $_GET['id']; 

    // Assuming your users table primary key column is 'id' (from the dashboard code)
    // If your column is 'user_id', change the SQL: WHERE user_id = ?
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?"); 
    
    // Check if the prepare statement failed
    if ($stmt === false) {
        echo "<script> alert('Database Prepare Error: " . htmlspecialchars($conn->error) . "'); </script>";
        // Skip the rest and redirect
    } else {
        $stmt->bind_param("i", $user_id);

        if($stmt->execute()) {
            $redirect_url = "dashboard.php?status=success"; // Adjust the path as necessary
        } else {
            // *** CRITICAL DEBUGGING STEP: SHOW THE MYSQL ERROR ***
            $redirect_url = "dashboard.php?status=error";
        }

        $stmt->close();

        header("Location: " . $redirect_url); // Assuming delete_user.php is in the same directory as dashboard.php
        exit();
    }
} else {
    header("Location: dashboard.php?status=no_id");
    exit();
}

?>