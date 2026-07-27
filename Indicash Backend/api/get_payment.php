<?php
include "con.php";

extract($_REQUEST);

$dd = mysqli_query($con,"select session,active from users where mobile='$mobile'");
$d = mysqli_fetch_array($dd);

if($d['session'] != $_REQUEST['session']){
    $data['success'] = "0";
    $data['msg'] = "You are not authorized to use this";
    $data['session0'] = $d['session'];
    $data['session1'] = $_REQUEST['session'];

    echo json_encode($data);
    return;
 }

$order_id = md5($mobile.$amount.$d['session']);

 $hash = openssl_encrypt($order_id, "AES-128-ECB", $_REQUEST['hash_key']);

$get_data = mysqli_fetch_array(mysqli_query($con,"select * from gateway_temp where hash='$hash' AND user='$mobile'"));

 //$data['qyery'] = "select * from gateway_temp where hash='$hash' AND user='$mobile'";

$amount = $get_data['amount'];

if(mysqli_num_rows(mysqli_query($con,"select sn from settings where data_key='verify_upi_payment' AND data='0'")) > 0){
mysqli_query($con,"update users set wallet=wallet+'$amount' where mobile='$mobile'");

mysqli_query($con,"INSERT INTO `transactions`( `user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$amount','1','Deposit','user','$stamp')");
  
    mysqli_query($con,"INSERT INTO `auto_deposits`( `mobile`, `amount`, `method`, `pay_id`, `created_at`) VALUES ('$mobile','$amount','".$get_data['type']."','$hash','$stamp')");
    $check_refer = mysqli_query($con,"select code from refers where user='$mobile' AND status='0'");
  if(mysqli_num_rows($check_refer) > 0){
    $refer = mysqli_fetch_array($check_refer);
    
    $code = $refer['code'];
    
    $get_refer_mobile = mysqli_fetch_array(mysqli_query($con,"select mobile from users where code='$code'"));
    $refer_mobile = $get_refer_mobile['mobile'];
    
    
    $amount = $amount*10/100;
    mysqli_query($con,"update refers set status='1', amount='$amount' where user='$mobile' AND code='$code'");
	mysqli_query($con,"update users set wallet=wallet+'$amount' where mobile='$refer_mobile'");

	mysqli_query($con,"INSERT INTO `transactions`( `user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$refer_mobile','$amount','1','Refer earning','user','$stamp')");

    
    
$data['success'] = "1";
} } else {
    
    mysqli_query($con,"INSERT INTO `upi_verification`( `user`, `amount`, `created_at`) VALUES  ('$mobile','$amount','$stamp')");

$data['success'] = "0";
    
  }


echo json_encode($data);