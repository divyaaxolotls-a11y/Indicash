<?php
include('header.php');

if (isset($_POST['SubmitUpdate'])) {
    // Navin values ghene
    $new_user = mysqli_real_escape_string($con, $_POST['new_username']);
    $new_pass = mysqli_real_escape_string($con, $_POST['new_password']);
    $new_cont = mysqli_real_escape_string($con, $_POST['new_contact']);

    // Database Update Query
    $query = "UPDATE admin SET username='$new_user', password='$new_pass', contact='$new_cont' WHERE email='$idd'";

    if (mysqli_query($con, $query)) {
        echo "<script>alert('Profile Saved Successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Update Failed!'); window.location.href='profile.php';</script>";
    }
}
?>