<?php
session_start();
$pageTitle = "Home - OLMS";
include('connection.php');
include('header.php');


// bugged code
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     echo "<script> alert('Access denied.'); </script>";
//     header("Location: ./index.php");
//     exit();
// }

// if (!isset($_SESSION["user_id"])) {

// bugged code
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     echo "<script> alert('Access denied.'); </script>";
//     header("Location: ./index.php");
//     exit();
// }

// if (!isset($_SESSION["user_id"])) {
//     echo "<script> alert('Please Login'); </script>";
//     header("Location: ./index.php");
//     exit(); // Always exit after a header redirect
// }
?>

        <div>
            <h1 class="text-4xl font-bold text-center mt-10 text-gray-900 dark:text-white transition-colors duration-200">Welcome to OLMS</h1>
            <p class="text-center mt-4 text-gray-600 dark:text-gray-300 transition-colors duration-200">Your Online Learning Management System</p>
            <p class="text-center mt-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">Explore our courses and start learning today!</p>
            <p class="text-center mt-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">Add body information here</p>
            <p class="text-center mt-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">add browse course here</p>
        </div>

<?php include('footer.php'); ?>