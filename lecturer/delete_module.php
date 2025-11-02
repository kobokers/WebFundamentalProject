<?php
session_start();
include("../connection.php"); 


$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$module_id || !is_numeric($module_id) || !$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid module or course ID.";
    header("Location: dashboard.php");
    exit;
}

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