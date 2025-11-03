<?php
include("connection.php");
include("header.php");

// if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     echo "<script> alert('Access denied.'); </script>";
//     header("Location: ./index.php");
//     exit();
// }

// if(!isset($_SESSION["user_id"])) {
//     echo "<script> alert('Please Login'); </script>";
//     header("Location: ../index.php");
//     exit(); // Always exit after a header redirect
// }
// bug bug bug bug bug bug bug bug bug bug

?>

<body>
    <main>
        <div>
            <p>this is admin dashboard that can go to manage user or else gbye</p>
        </div>
    </main>
</body>