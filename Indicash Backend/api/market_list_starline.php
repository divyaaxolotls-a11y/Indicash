<?php
include "con.php";

extract($_REQUEST);


$gatConfig = mysqli_query($con,"select * from starline_markets where active='1'");

while($g = mysqli_fetch_array($gatConfig)){
    $data['data'][] = $g;
}


echo json_encode($data);