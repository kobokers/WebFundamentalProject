<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Validation ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied. Please log in as a student to enroll.";
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID provided.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Check Course Fee and Existing Enrollment ---
$check_query = "SELECT fee, title 
                FROM courses 
                WHERE id = '$course_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = "Course not found.";
    header("Location: ../catalog.php");
    exit;
}

$course_data = mysqli_fetch_assoc($check_result);
$course_fee = $course_data['fee'];
$course_title = $course_data['title'];

// Determine initial payment status
$initial_status = ($course_fee > 0) ? 'pending' : 'paid';

// --- 3. Perform Enrollment INSERT ---
// Note: A unique index on (user_id, course_id) prevents duplicate enrollment.

$enroll_query = "INSERT INTO enrollment (user_id, course_id, payment_status, enroll_date)
                 VALUES ('$user_id', '$course_id', '$initial_status', NOW())";

$result = mysqli_query($conn, $enroll_query);

if ($result) {
    if ($initial_status == 'paid') {
        // --- Free Course: Grant immediate access ---
        $_SESSION['success'] = "Successfully enrolled in the free course: {$course_title}!";
        header("Location: course_view.php?id={$course_id}");
    } else {
        // --- Paid Course: Redirect to payment page ---
        $_SESSION['success'] = "Enrolled in {$course_title}. Please complete payment to start the course.";
        header("Location: ../auth/payment.php?course_id={$course_id}");
    }
    exit;
} else {
    // Check if the error is due to duplicate enrollment
    if (mysqli_errno($conn) == 1062) { // 1062 is typically the code for Duplicate entry for key 'PRIMARY'
        $_SESSION['error'] = "You are already enrolled in this course.";
        // Check current status to redirect to the right place
        $status_query = "SELECT payment_status FROM enrollment WHERE user_id = '$user_id' AND course_id = '$course_id'";
        $status_result = mysqli_query($conn, $status_query);
        $current_status = mysqli_fetch_assoc($status_result)['payment_status'];

        if ($current_status == 'paid') {
            header("Location: course_view.php?id={$course_id}");
        } else {
            header("Location: ../auth/payment.php?course_id={$course_id}");
        }
        
    } else {
        $_SESSION['error'] = "Enrollment failed: " . mysqli_error($conn);
        header("Location: ../catalog.php");
    }
    mysqli_close(mysql: $conn);
    exit;
}
?>