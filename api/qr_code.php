<?php
   include "con.php";
   date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
 
   extract($_REQUEST);
   
   $con = mysqli_connect($servername, $username, $password,$dbname);
   $sql = "SELECT * FROM qr_code";
   $result = $con->query($sql);
   $row = $result->fetch_assoc();
   
   $data['upi_id'] = $row['upi_id'];
      $data['qr_image'] = $row['qr_image'];

   echo json_encode($data);
  ?>