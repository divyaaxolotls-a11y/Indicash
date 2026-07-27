<?php
include "con.php";

extract($_REQUEST);

$sx =mysqli_query($con,"SELECT * FROM `games_archive` where user='$mobile' order by created_at desc");
while($x = mysqli_fetch_array($sx))
{
    $x['date'] = date('d M Y',$x['created_at']);
    $data['data'][] = $x;
}

echo json_encode($data);