<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "Welcome, " . $_SESSION['user_name'];
    echo "<br>";
    echo "your role is ". $_SESSION['user_role'];
} else {
    echo "Please log in.";
}
?>