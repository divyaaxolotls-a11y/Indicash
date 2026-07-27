<?php 
    session_start();
    session_unset();
    session_destroy(); // Destroy the session

    echo "<script>window.location.href= 'index.php';</script>";
?>

