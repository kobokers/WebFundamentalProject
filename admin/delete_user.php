<?php
include("../connection.php");
session_start(); 

if(!isset($_SESSION["user_id"])){
    echo "<script> alert('Please Login'); </script>";
    header("Location: ../index.php");
    exit();
}

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script> alert('Access denied.'); </script>";
    header("Location: ../index.php");
    exit();
}

if(isset($_GET['id'])) { 
    $user_id = intval($_GET['id']); // Sanitize input

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete progress records
        $query1 = "DELETE FROM progress WHERE user_id = $user_id";
        $conn->query($query1);

        // Delete enrollment records
        $query2 = "DELETE FROM enrollment WHERE user_id = $user_id";
        $conn->query($query2);

        // Delete discussion replies
        $query3 = "DELETE FROM discussion_replies WHERE user_id = $user_id";
        $conn->query($query3);

        // Delete discussion threads
        $query4 = "DELETE FROM discussion_threads WHERE user_id = $user_id";
        $conn->query($query4);

        // Delete the user
        $query5 = "DELETE FROM users WHERE id = $user_id";
        $conn->query($query5);

        // Commit transaction
        $conn->commit();

        header("Location: dashboard.php?status=success");
        exit();

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        header("Location: dashboard.php?status=error");
        exit();
    }

} else {
    header("Location: dashboard.php?status=no_id");
    exit();
}
?>