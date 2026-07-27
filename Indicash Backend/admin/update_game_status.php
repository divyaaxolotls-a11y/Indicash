<?php
include('config.php');

if(isset($_POST['status'])){

$status = intval($_POST['status']);

/* MAIN GAME */
$q1 = mysqli_query($con,"UPDATE gametime_manual SET active='$status'");

/* STARLINE */
$q2 = mysqli_query($con,"UPDATE starline_markets SET active='$status'");

/* JACKPOT */
$q3 = mysqli_query($con,"UPDATE jackpot_markets SET is_active='$status'");

if($q1 && $q2 && $q3){
    echo "Game status updated successfully";
}else{
    echo "Error updating status: " . mysqli_error($con);
}

}
else{
echo "Status not received";
}
?>