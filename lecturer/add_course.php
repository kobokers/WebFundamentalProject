<?php
session_start();
include("../connection.php"); // Includes the database connection logic

// --- 1. Authentication and Authorization ---
// Ensure user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Only lecturers can add courses.";
    header("Location: ../auth/login.php");
    exit;
}

// Get lecturer ID from session
$lecturer_id = $_SESSION['user_id'];

// --- 2. Input Collection and Sanitization ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $language = mysqli_real_escape_string($conn, $_POST['language']);
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? 'General');
    $fee = (float)$_POST['fee']; 
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $query = "INSERT INTO courses (title, level, language, fee, lecturer_id, description, category) 
              VALUES ('$title', '$level', '$language', '$fee', '$lecturer_id', '$description', '$category')";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $new_course_id = mysqli_insert_id($conn);
        $_SESSION['success'] = "Course '{$title}' successfully registered! Now add modules.";
        
        header("Location: module_setup.php?course_id=$new_course_id");
        exit;
    } else {
        $_SESSION['error'] = "Course registration failed: " . mysqli_error($conn);
        header("Location: add_course_form.php"); // Redirect back to form on failure
        exit;
    }
} else {
    // If not a POST request, redirect back to the form
    header("Location: add_course_form.php");
    exit;
}
?>