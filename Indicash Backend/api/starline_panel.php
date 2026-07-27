<?php
header("Content-Type: application/json");
include "con.php";

$market = mysqli_real_escape_string($con, $_GET['market'] ?? '');

$q = mysqli_query($con,"
    SELECT panna, number, date
    FROM starline_results
    WHERE market='$market'
    ORDER BY STR_TO_DATE(date,'%d/%m/%Y') ASC
");

$data = [];

while($r = mysqli_fetch_assoc($q))
{
    $data[] = [
        "open_panna"  => $r['panna'],
        "open"        => $r['number'],
        "close"       => "",
        "close_panna" => "",
        "date"        => $r['date']
    ];
}

echo json_encode($data);