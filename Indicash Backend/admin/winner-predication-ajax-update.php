<?php include('config.php');
$sn = $_REQUEST['sn'];
$number = $_REQUEST['number'];

mysqli_query($con,"update games set number='$number' where sn='$sn'");