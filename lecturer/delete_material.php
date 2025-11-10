<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../auth/login.php");
    exit;
}

// Check for required IDs
$material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : null;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null; // Used for redirection

if (!$material_id || !$course_id) {
    $_SESSION['error'] = "Missing required material or course ID for deletion.";
    header("Location: dashboard.php");
    exit;
}

$lecturer_id = $_SESSION['user_id'];

// --- 2. Security Check: Verify Lecturer Owns the Material's Course ---
// Join materials -> modules -> courses to verify lecturer_id
$verify_query = "
    SELECT 
        T1.title 
    FROM 
        learning_materials AS T1
    INNER JOIN 
        modules AS T2 ON T1.module_id = T2.id
    INNER JOIN
        courses AS T3 ON T2.course_id = T3.id
    WHERE 
        T1.id = '$material_id' AND T3.lecturer_id = '$lecturer_id'";

$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Cannot delete: Material not found or you do not have permission.";
    header("Location: module_setup.php?course_id={$course_id}");
    exit;
}

$material = mysqli_fetch_assoc($verify_result);
$material_title = $material['title'];

// --- 3. Perform Deletion ---
$delete_query = "DELETE FROM learning_materials WHERE id = '$material_id'";

if (mysqli_query($conn, $delete_query)) {
    $_SESSION['success'] = "Learning material '{$material_title}' was successfully deleted.";
} else {
    $_SESSION['error'] = "Failed to delete material: " . mysqli_error($conn);
}

mysqli_close($conn);

// Redirect back to the course module setup page
header("Location: module_setup.php?course_id={$course_id}");
exit;