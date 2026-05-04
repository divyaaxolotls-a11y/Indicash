<?php
include "con.php";

$market = isset($_GET['market']) ? mysqli_real_escape_string($con, $_GET['market']) : 'TIME BAZAR';

$sx = mysqli_query($con, "SELECT 
    manual_market_results.open,
    manual_market_results.close,
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
    $data[$x['date']] = [
        'open' => $x['open'],
        'close' => $x['close']
    ];
}

// Logic to determine which days of the week are actually open
$daysOfWeek = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
$openDays = [];

foreach ($daysOfWeek as $day) {
    // If the day is NOT marked as (CLOSED) in the string, consider it an open day
    if (stripos($scheduleStr, $day) !== false && stripos($scheduleStr, $day . "(CLOSED)") === false) {
        $openDays[] = $day;
    }
}

echo json_encode([
    "openDays" => $openDays,
    "results" => $data
]);
?>