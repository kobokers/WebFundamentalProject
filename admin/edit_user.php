<?php
include("../connection.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    echo "<script> alert('Please Login'); </script>";
    header("Location: ../index.php");
    exit(); // Always exit after a header redirect
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script> alert('Access denied.'); </script>";
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    // Prepare the SELECT statement
    $check_stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");

    if ($check_stmt === false) {
        // Handle prepare error for the SELECT query
        die("SELECT Prepare Error: " . htmlspecialchars($conn->error));
    }

    // Bind the user ID parameter
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    
    // Bind the result variable
    $check_stmt->bind_result($current_status);
    
    // Fetch the result (assuming ID is unique, there's at most one row)
    if ($check_stmt->fetch()) {
        // A user was found
        $check_stmt->close(); // Close the SELECT statement

        if ($current_status === 'active') {
            // --- User is ALREADY active ---
            echo "<script> alert('User with ID " . $user_id . " is already active.'); </script>";
            header("Location: dashboard.php?status=already_active");
            exit();
        } else {
            // --- User is NOT active, proceed with UPDATE ---
            $update_stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");

            if ($update_stmt === false) {
                // Handle prepare error for the UPDATE query
                die("UPDATE Prepare Error: " . htmlspecialchars($conn->error));
            }

            $update_stmt->bind_param("i", $user_id);

            if ($update_stmt->execute()) {
                // Check if any rows were actually updated
                if ($update_stmt->affected_rows > 0) {
                    $redirect_url = "dashboard.php?status=edited";
                } else {
                    // This case means a user was found in the SELECT, 
                    $redirect_url = "dashboard.php?status=no_change"; 
                }
            } else {
                $redirect_url = "dashboard.php?status=error";
            }

            $update_stmt->close();

            header("Location: " . $redirect_url);
            exit();
        }
    } else {
        // --- No user found with that ID ---
        $check_stmt->close();
        header("Location: dashboard.php?status=user_not_found");
        exit();
    }
}
