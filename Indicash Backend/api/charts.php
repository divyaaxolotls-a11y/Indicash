<?php
include "con.php";
header('Content-Type: application/json');

// Initialize the 3 separate arrays
$data = [
    'normal_market'  => [],
    'starline_market' => [],
    'jackpot_market'  => []
];

// 1. Fetch Normal Games (gametime_manual)
$get_normal = mysqli_query($con, "SELECT * FROM gametime_manual WHERE active=1");
while($row = mysqli_fetch_assoc($get_normal)) { 
   $data['normal_market'][] = $row;	
}

// 2. Fetch Starline Games (starline_timings)
// Based on your screenshot, columns are sn, name, market, open, close, active
$get_starline = mysqli_query($con, "SELECT * FROM starline_timings WHERE active=1");
while($row = mysqli_fetch_assoc($get_starline)) {
    $data['starline_market'][] = $row;
}

// 3. Fetch Jackpot Games (jackpot_markets)
// Based on your screenshot, columns are sn, name, close, is_active
$get_jackpot = mysqli_query($con, "SELECT * FROM jackpot_markets WHERE is_active=1");
while($row = mysqli_fetch_assoc($get_jackpot)) {
    $data['jackpot_market'][] = $row;
}

echo json_encode($data);
?>