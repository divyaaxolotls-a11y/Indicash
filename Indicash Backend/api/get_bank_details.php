<?php
include "con.php";

extract($_REQUEST);

if(mysqli_num_rows(mysqli_query($con,"select sn from withdraw_details where user='$mobile'")) == 0){
    
    
$data['success'] = "0";
} else {
    
    $data = mysqli_fetch_array(mysqli_query($con,"select * from withdraw_details where user='$mobile'"));
    
$data['success'] = "1";
}

echo json_encode($data);