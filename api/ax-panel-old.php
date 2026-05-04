<?php
include "con.php";

$date = date('d/m/Y');
extract($_GET);
$market = isset($_GET['market']) ? mysqli_real_escape_string($con, $_GET['market']) : 'TIME BAZAR';

$sx = mysqli_query($con, "SELECT 
    manual_market_results.market AS market_name,
    manual_market_results.date AS market_time,
    manual_market_results.open_panna,
    manual_market_results.open,
    manual_market_results.close,
    manual_market_results.close_panna,
    manual_market_results.date,
    gametime_manual.market,
    gametime_manual.open as gametime_open,
    gametime_manual.close as gametime_close,
    gametime_manual.active
FROM
    manual_market_results
INNER JOIN
    gametime_manual
ON
    manual_market_results.market = gametime_manual.market 
WHERE
    manual_market_results.market = '$market' AND
    STR_TO_DATE(manual_market_results.date, '%d/%m/%Y') >= '2024-01-01'
ORDER BY
    STR_TO_DATE(manual_market_results.date, '%d/%m/%Y') ASC;
");
$data = array();

while ($x = mysqli_fetch_array($sx)) {
    $result = $x['result'];

    // Check if the result matches the desired pattern
    // if (preg_match($pattern, $result)) {
    $data[] = array(
        'sn' => $x['sn'],
        'market' => $x['market'],
        'open_panna' =>  $x['open_panna'],
        'open' =>  $x['open'],
        'close' => $x['close'],
        'close_panna' => $x['close_panna'],
        'gametime_open' => $x['gametime_open'],
        'gametime_close' => $x['gametime_close'],
        'date' => $x['date'],
    );
    // }
}

echo json_encode($data);
?>
