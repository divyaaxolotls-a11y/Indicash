<?php
include "con.php";

$market = isset($_GET['market']) ? mysqli_real_escape_string($con, $_GET['market']) : 'TIME BAZAR';

// 1. Updated SELECT to include open_panna and close_panna
$sx = mysqli_query($con, "SELECT 
    manual_market_results.open_panna,
    manual_market_results.open,
    manual_market_results.close,
    manual_market_results.close_panna,
    manual_market_results.date,
    gametime_manual.days
FROM manual_market_results
INNER JOIN gametime_manual ON manual_market_results.market = gametime_manual.market 
WHERE manual_market_results.market = '$market' 
AND STR_TO_DATE(manual_market_results.date, '%d/%m/%Y') >= '2024-01-01'
ORDER BY STR_TO_DATE(manual_market_results.date, '%d/%m/%Y') ASC;");

$data = array();
$scheduleStr = "";

while ($x = mysqli_fetch_array($sx)) {
    $scheduleStr = $x['days']; 
    // 2. Added panna values to the result array
    $data[$x['date']] = [
        'open_panna'  => $x['open_panna'],
        'open'        => $x['open'],
        'close'       => $x['close'],
        'close_panna' => $x['close_panna']
    ];
}

// Logic to determine which days of the week are actually open
$daysOfWeek = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
$openDays = [];

foreach ($daysOfWeek as $day) {
    if (stripos($scheduleStr, $day) !== false && stripos($scheduleStr, $day . "(CLOSED)") === false) {
        $openDays[] = $day;
    }
}

header('Content-Type: application/json');
echo json_encode([
    "openDays" => $openDays,
    "results" => $data
]);
?>