<?php
include "con.php";

extract($_REQUEST);

$sx = mysqli_query($con,"SELECT * FROM `gametime` where sn='1'");
$x = mysqli_fetch_array($sx);
$data = $x;

echo json_encode($data);