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

if(isset($_GET['id'])) { 
    $user_id = $_GET['id']; 

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
            $redirect_url = "dashboard.php?status=error";
        }

        $stmt->close();

        header("Location: " . $redirect_url); 
        exit();
    }
} else {
    header("Location: dashboard.php?status=no_id");
    exit();
}

?>