<?php
session_start();
include("../connection.php");
if (isset($_SESSION['user_id'])) {
    echo "Welcome, " . $_SESSION['user_name'];
    echo "<br>";
    echo "your role is ". $_SESSION['user_role'];
    echo '<br>';
} else {
    echo "<script>";
    echo "alert('Please log in.');";
    echo "window.location.href = '../auth/login.php';";
    echo "</script>";
}
?>
<body>
    <main class="flex-grow container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Lecturer Dashboard</h1>
        <p class="text-lg">This is the lecturer dashboard. More features coming soon!</p>
        <div class="mt-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Dashboard Overview</h2>
                <p class="text-gray-700">Here you can manage your courses, view student progress, and more.</p>
            </div>
        </div>
</body>