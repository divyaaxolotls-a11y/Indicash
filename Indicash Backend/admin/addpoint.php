<?php
include('config.php');

$user_id   = $_POST['user_id'];
$pointsAdd = $_POST['pointsAdd'];

$query = "UPDATE admin SET wallet = wallet + ? WHERE sn = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $pointsAdd, $user_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
//   echo "<script>alert('Points added successfully.'); window.location.href='crud_task.php';</script>";
   echo "<script>window.location.href='crud_task.php';</script>";
   exit; 
} 
else {
   echo "<script>alert('Failed to update wallet balance.'); window.location.href='crud_task.php';</script>";
   exit; 
}
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
