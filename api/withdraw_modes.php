<?php
include "con.php";

extract($_REQUEST);

$sx = mysqli_query($con,"SELECT * FROM `withdraw_options` where active='1' order by name");
while($x = mysqli_fetch_array($sx))
{
    $data['data'][] = $x;
}

echo json_encode($data);