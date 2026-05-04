<?php
include "con.php";

extract($_REQUEST);
if(mysqli_num_rows(mysqli_query($con,"select sn from users where mobile='$user' and session ='$session'")) == 0){
    $data['msg'] = "You are not authrized to use this";
   
    $data['active'] = "0";
    
    echo json_encode($data);
    return;
} else {
          
    $dd = mysqli_query($con,"select active from users where mobile='$user'");
    $d = mysqli_fetch_array($dd);
    $data['active'] = "1";
}



if(mysqli_num_rows(mysqli_query($con,"select sn from users where mobile='$mobile'"))>0){
  
  $check = mysqli_query($con,"select wallet from users where mobile='$user' AND wallet >= $amount");

  if(mysqli_num_rows($check) > 0){
    
    mysqli_query($con,"UPDATE users set wallet=wallet-$amount where mobile='$user'");
    mysqli_query($con,"UPDATE users set wallet=wallet+$amount where mobile='$mobile'");
    mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$user','$amount','0','Transfer to $mobile','user','$stamp','0','0')");
    mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$mobile','$amount','0','Received from $user','user','$stamp','0','0')");
     $data['success']  = "1";
 $data['msg']  = "Transfer complete";
      
    $get_bal = mysqli_fetch_array(mysqli_query($con,"select wallet from users where mobile='$mobile'"));
    
    $data['wallet'] = $get_bal['wallet'];
  } else {
    
    $data['success']  = "0";
 $data['msg']  = "You don't have enough wallet balance"; 
  }
  
} else {
 $data['success']  = "0";
 $data['msg']  = "Mobile number is not registered with us";
  
}

echo json_encode($data);