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
    $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : 'NULL';
    $fee = (float)$_POST['fee']; 
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // --- Image Upload Handling ---
    $course_image = 'NULL'; // Default value for SQL
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['course_image']['tmp_name'];
        $file_name = $_FILES['course_image']['name'];
        $file_size = $_FILES['course_image']['size'];
        $file_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_parts));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= 5 * 1024 * 1024) { // 5MB limit
                $new_file_name = uniqid('course_') . '.' . $file_ext;
                $upload_dir = '../uploads/courses/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $course_image = "'$new_file_name'"; // Quote string for SQL
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file.";
                    header("Location: add_course_form.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "File size exceeds 5MB limit.";
                header("Location: add_course_form.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            header("Location: add_course_form.php");
            exit;
        }
    }

    $query = "INSERT INTO courses (title, level, language, fee, lecturer_id, description, category, duration, course_image) 
              VALUES ('$title', '$level', '$language', '$fee', '$lecturer_id', '$description', '$category', $duration, $course_image)";

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