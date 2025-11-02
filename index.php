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
//     echo "<script> alert('Please Login'); </script>";
//     header("Location: ./index.php");
//     exit(); // Always exit after a header redirect
// }
?>

<body>
    <main>
        <div>
            <h1 class="text-4xl font-bold text-center mt-10">Welcome to OLMS</h1>
            <p class="text-center mt-4 text-gray-600">Your Online Learning Management System</p>
            <p class="text-center mt-2 text-gray-600">Explore our courses and start learning today!</p>
            <p class="text-center mt-2 text-grey-600">Add body information here</p>
            <p class="text-center mt-2 text-grey-600">add browse course here</p>
        </div>
    </main>
</body>

<!-- Swiper JS -->

<?php include('footer.php'); ?>