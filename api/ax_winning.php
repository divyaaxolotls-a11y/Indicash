<?php
include "con.php";
date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)

extract($_REQUEST);

// $sx = mysqli_query($con,"SELECT * FROM `transactions` where user='$mobile' AND remark like '%Winning%' OR user='$mobile' AND remark='bet' order by created_at desc");

$sx = mysqli_query($con,"SELECT transactions.user,transactions.amount as win_amt,transactions.remark,transactions.created_at,transactions.dated_on,transactions.game_id,transactions.type,transactions.batch_id,games.number,games.bazar,games.amount as bet_amount
FROM transactions
JOIN games ON transactions.game_id=games.sn where transactions.user='$mobile' AND transactions.remark like '%Winning%' OR transactions.user='$mobile' AND transactions.remark='bet' order by transactions.created_at desc");

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