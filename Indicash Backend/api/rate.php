<?php
include "con.php";

// Set header for proper JSON response
header('Content-Type: application/json');
extract($_REQUEST);

$response = array();

// 1. Fetch Normal Market Rates
// $normal_query = mysqli_query($con, "SELECT * FROM `rates` LIMIT 1");
// if ($normal_query && mysqli_num_rows($normal_query) > 0) {
//     $response['normal_rates'] = mysqli_fetch_assoc($normal_query);
// } else {
//     $response['normal_rates'] = null;
// }
$normal_query = mysqli_query($con, "SELECT single, jodi, singlepatti, doublepatti, triplepatti, halfsangam, fullsangam FROM `rates` LIMIT 1");

if ($normal_query && mysqli_num_rows($normal_query) > 0) {
    $response['normal_rates'] = mysqli_fetch_assoc($normal_query);
} else {
    $response['normal_rates'] = null;
}
// 2. Fetch Starline Market Rates
// Make sure you have a table named 'starline_rates' in your database!
$starline_query = mysqli_query($con, "SELECT * FROM `starline_rates` LIMIT 1");
if ($starline_query && mysqli_num_rows($starline_query) > 0) {
    $response['starline_rates'] = mysqli_fetch_assoc($starline_query);
} else {
    $response['starline_rates'] = null;
}

// 3. Fetch Jackpot Market Rates
// Make sure you have a table named 'jackpot_rates' in your database!
$jackpot_query = mysqli_query($con, "SELECT * FROM `jackpot_rates` LIMIT 1");
if ($jackpot_query && mysqli_num_rows($jackpot_query) > 0) {
    $response['jackpot_rates'] = mysqli_fetch_assoc($jackpot_query);
} else {
    $response['jackpot_rates'] = null;
}

// Send final JSON response
echo json_encode($response);
?>