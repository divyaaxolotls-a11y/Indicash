<?php
   $servername = "localhost";
   $username = "playboss777_db";
   $password = "oZoC4*9#!s&l";
   $dbname = "playboss777_db";
   $con = mysqli_connect($servername, $username, $password,$dbname);
   $sql = "SELECT * FROM ax_version";
   $result = $con->query($sql);
   $row = $result->fetch_assoc();
   
   $data['version'] = $row['version'];
   echo json_encode($data);
  ?>