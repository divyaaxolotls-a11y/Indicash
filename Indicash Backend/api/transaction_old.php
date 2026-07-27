<?php
include "con.php";

extract($_REQUEST);

$sx = mysqli_query($con,"SELECT * FROM `transactions_archive` where user='$mobile' order by created_at desc");
while($x = mysqli_fetch_array($sx))
{
    if($x['type'] == "0")
    {
        $x['amount'] = '-'.$x['amount'];
    }
    $x['date'] = date('d/m/y',$x['created_at']);
    $data['data'][] = $x;
}

echo json_encode($data);