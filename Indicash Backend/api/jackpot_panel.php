<?php
header("Content-Type: application/json");
include "con.php";

$market = mysqli_real_escape_string($con, $_GET['market'] ?? '');

$q = mysqli_query($con,"
    SELECT number,date
    FROM jackpot_results
    WHERE market='$market'
    ORDER BY STR_TO_DATE(date,'%d/%m/%Y') ASC
");

$data = [];

while($r = mysqli_fetch_assoc($q))
{
    $data[] = [
        "number" => str_pad($r['number'],2,'0',STR_PAD_LEFT),
        "date"   => $r['date']
    ];
}

echo json_encode($data);