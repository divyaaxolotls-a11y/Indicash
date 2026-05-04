<?php
include "con.php";
date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
extract($_REQUEST);

if(isset($_REQUEST['date'])){
    $sx = mysqli_query($con,"SELECT * FROM `games` where user='$mobile' AND date='".$_REQUEST['date']."' order by created_at desc");
} else {
    $sx = mysqli_query($con,"SELECT * FROM `games` where user='$mobile' order by sn desc");
}

while($x = mysqli_fetch_array($sx)) {
    $data['data'][] = $x;
}

// if(isset($_REQUEST['date'])){
// $sx = mysqli_query($con,"SELECT * FROM `starline_games` where user='$mobile' AND date='".$_REQUEST['date']."' order by created_at desc");
// } else {
// $sx = mysqli_query($con,"SELECT * FROM `starline_games` where user='$mobile' order by created_at desc");
// }

while($x = mysqli_fetch_array($sx)) {
    $x['bazar'] = $x['bazar']." ".$x['timing_sn'];
    $data['data'][] = $x;
}

// Remove date comparison
// Output the data as JSON
echo json_encode($data);
