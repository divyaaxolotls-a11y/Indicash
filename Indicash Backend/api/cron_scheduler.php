<?php
include "con.php";

date_default_timezone_set('Asia/Kolkata');
$current_time = date("H:i");
$current_date = date("d/m/Y");

// --- 1. PROCESS STARLINE MARKETS ---
$starline_q = mysqli_query($con, "SELECT name, close FROM starline_timings WHERE active = '1' AND close <= '$current_time'");
while ($row = mysqli_fetch_assoc($starline_q)) {
    $m_name = $row['name'];
    $timing = $row['close'];

    // Check if result exists
    $check = mysqli_query($con, "SELECT sn FROM starline_results WHERE market = '$m_name' AND timing = '$timing' AND date = '$current_date' LIMIT 1");
    if (mysqli_num_rows($check) == 0) {
        triggerAPI("https://test.apluscrm.in/api/api-starline-auto.php", $m_name, $timing);
    }
}

// --- 2. PROCESS JACKPOT MARKETS ---
$jackpot_q = mysqli_query($con, "SELECT name, close FROM jackpot_markets WHERE is_active = '1' AND close <= '$current_time'");
while ($row = mysqli_fetch_assoc($jackpot_q)) {
    $m_name = $row['name'];
    $timing = $row['close'];

    // Check if result exists
    $check = mysqli_query($con, "SELECT sn FROM jackpot_results WHERE market = '$m_name' AND timing = '$timing' AND date = '$current_date' LIMIT 1");
    if (mysqli_num_rows($check) == 0) {
        triggerAPI("https://test.apluscrm.in/api/api-jackpot-auto.php", $m_name, $timing);
    }
}

/**
 * Helper function to hit the result declaration APIs
 */
function triggerAPI($url, $market, $timing) {
    $data = json_encode([
        "market" => $market,
        "timing" => $timing
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Prevent cron from hanging
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    // Log the output for debugging
    $log_msg = "[" . date("Y-m-d H:i:s") . "] Triggered $market ($timing). Response: " . ($err ? "CURL Error: $err" : $response) . PHP_EOL;
    file_put_contents("cron_debug.log", $log_msg, FILE_APPEND);
}
?>