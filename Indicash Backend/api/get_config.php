<?php
include "con.php";
extract($_REQUEST);


date_default_timezone_set("Asia/Kolkata");




$gatConfig = mysqli_query($con, "SELECT * FROM `settings` ");


 
//$gatConfig = query("select * from settings");

while($g = mysqli_fetch_array($gatConfig)){
    $data['data'][] = $g;
}


if(isset($mobile)){
  $data['msgs'] = mysqli_num_rows(mysqli_query($con,"select sn from admin_chats where seen='0' AND msg_to='$mobile'"));
}





  echo json_encode($data);
//echo "dfvd";
?>