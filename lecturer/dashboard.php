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