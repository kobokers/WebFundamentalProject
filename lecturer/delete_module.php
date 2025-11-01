<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Authorization Check ---
// (Ensure user is logged in and is a lecturer - code omitted for brevity)

$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$module_id || !is_numeric($module_id) || !$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid module or course ID.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. INSECURE SQL Query to DELETE Module ---
// Note: You must delete related records in the 'progress' table first if they exist
// or set up CASCADE DELETE on your foreign keys. Assuming no progress data is linked for simplicity.
$delete_query = "DELETE FROM modules WHERE id = '$module_id'";

if (mysqli_query($conn, $delete_query)) {
    $_SESSION['success'] = "Module successfully deleted.";
} else {
    $_SESSION['error'] = "Failed to delete module: " . mysqli_error($conn);
}

mysqli_close($conn);

// Redirect back to the module setup page
header("Location: module_setup.php?course_id={$course_id}");
exit;
?>