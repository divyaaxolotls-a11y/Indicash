<?php
  include "con.php";
  date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)

  extract($_REQUEST);
  
  $con = mysqli_connect($servername, $username, $password,$dbname);
  $sql = "SELECT * FROM users WHERE mobile='". $mobile ."' &&  pin='". $pin ."'";
  
  $result = $con->query($sql);
  $row = $result->fetch_assoc();
  //var_dump($row);die();
  if($row!= NULL){
     mysqli_query($con,"update users set session='$session' where mobile='$mobile'");
     $data['active'] = "1";
     $data['code'] = "1";
     $data['msg'] = "User Verified";
  }
  else
  {
         $data['code'] = "0";
         $data['msg'] = "Invalid Pin";
  
  }
 
  echo json_encode($data);