<?php
include "con.php";

extract($_REQUEST);

$gateway = mysqli_query($con,"select name from gateway_config where active='1'");

while($g = mysqli_fetch_array($gateway)){
    $data['data'][] = $g;
}

echo json_encode($data);