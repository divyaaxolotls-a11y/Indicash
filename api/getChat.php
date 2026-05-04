<?php
include "con.php";

extract($_REQUEST);

$gateway = mysqli_query($con,"select * from admin_chats where user='$mobile' OR msg_to='$mobile'");

mysqli_query($con,"update admin_chats set seen='1' where user='$mobile' OR msg_to='$mobile'");

while($g = mysqli_fetch_array($gateway)){
  $g['time'] = date('h:i A d/m/y', $g['created_at']);
    $data['data'][] = $g;
}

echo json_encode($data);