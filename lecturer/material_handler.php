<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../auth/login.php");
    exit;
}

// Check for POST submission
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['module_id'], $_POST['course_id'])) {
    $_SESSION['error'] = "Invalid request method or missing parameters.";
    header("Location: dashboard.php");
    exit;
}

// Get required IDs
$lecturer_id = $_SESSION['user_id'];
$module_id = (int)$_POST['module_id'];
$course_id = (int)$_POST['course_id']; // For redirection

// Get and sanitize inputs
$material_title = mysqli_real_escape_string($conn, $_POST['material_title']);
$content_type = mysqli_real_escape_string($conn, $_POST['content_type']);
$content_url = mysqli_real_escape_string($conn, $_POST['content_url'] ?? '');
$material_order = (int)$_POST['material_order'];

// --- 2. Security Check: Verify Lecturer Owns the Module's Course ---
// Prevent a lecturer from adding a material to a module they don't own.
$verify_query = "
    SELECT 
        T1.id
    FROM 
        modules AS T1
    INNER JOIN 
        courses AS T2 ON T1.course_id = T2.id
    WHERE 
        T1.id = '$module_id' AND T2.lecturer_id = '$lecturer_id'";

$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Module not found or you do not have permission to add content to it.";
    header("Location: module_setup.php?course_id={$course_id}");
    exit;
}

// --- 3. Insert New Learning Material ---
$insert_material_query = "
    INSERT INTO learning_materials 
        (module_id, title, content_type, content_url, material_order) 
    VALUES 
        ('$module_id', '$material_title', '$content_type', '$content_url', '$material_order')";

if (mysqli_query($conn, $insert_material_query)) {
    $_SESSION['success'] = "Learning material '{$material_title}' added successfully.";
} else {
    $_SESSION['error'] = "Failed to add material: " . mysqli_error($conn);
}

mysqli_close($conn);

// Redirect back to the course module setup page
header("Location: module_setup.php?course_id={$course_id}");
exit;