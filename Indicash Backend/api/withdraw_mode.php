<?php
include "con.php";

extract($_REQUEST);

if(mysqli_num_rows(mysqli_query($con,"select sn from withdraw_details where user='$mobile'")) == 0){
    
    mysqli_query($con,"INSERT INTO `withdraw_details`(`user`, `prefered`, `upi`, `acno`, `name`, `ifsc`,`bank`,`paytm`,`phonepe`,`gpay`) VALUES ('$mobile','','','$acno','$name','$ifsc','$bank','$paytm','$phonepe','$gpay')");

} else if($mode == "bank") {
    
    mysqli_query($con,"update withdraw_details set acno='$acno', name='$name', ifsc='$ifsc', bank='bank' where user='$mobile'");
    
} else if($mode == "gpay") {
    
    mysqli_query($con,"update withdraw_details set gpay='$gpay' where user='$mobile'");
    
} else if($mode == "phonepe") {
    
    mysqli_query($con,"update withdraw_details set phonepe='$phonepe' where user='$mobile'");
    
    
} else if($mode == "paytm") {
    
    mysqli_query($con,"update withdraw_details set paytm='$paytm' where user='$mobile'");
    
}

$data['success'] = "1";
$data['msg'] = "Withdraw details updated";

echo json_encode($data);