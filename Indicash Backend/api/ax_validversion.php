<?php
   include "con.php";
   date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
 
   extract($_REQUEST);
   
   $con = mysqli_connect($servername, $username, $password,$dbname);
   $sql = "SELECT * FROM ax_version";
   $result = $con->query($sql);
   $row = $result->fetch_assoc();
   
   $data['version'] = $row['version'];
   echo json_encode($data);
  ?>