<?php
header("Content-Type: application/json");
include('config.php');
include "con.php";

// Helper Functions
function getDigit($panna) {
    $sum = array_sum(str_split($panna));
    return $sum % 10;
}

function getStarlineRate($con, $game_type) {
    // Fetches rate from your rates table
    $q = mysqli_query($con, "SELECT rate FROM rates WHERE game_name='$game_type' LIMIT 1");
    $r = mysqli_fetch_assoc($q);
    return (float)($r['rate'] ?? 95);
}
// function getStarlineRate($con, $game_type) {
//     $q = mysqli_query($con, "SELECT rate FROM rates WHERE game_name='$game_type' LIMIT 1");
//     $r = mysqli_fetch_assoc($q);

//     if(isset($r['rate'])) {
//         $parts = explode('/', $r['rate']); // [10, 950]

//         if(count($parts) == 2 && $parts[0] != 0){
//             return (float)$parts[1] / (float)$parts[0]; // 950/10 = 95
//         }
//     }

//     return 95;
// }

// --- 1. GET POST DATA ---
$input = json_decode(file_get_contents("php://input"), true);
$market_name = $input['market'] ?? ''; // e.g. "Test starline"
$timing = $input['timing'] ?? '';      // e.g. "20:10"
$currentDate = date('d/m/Y');
$stamp = date('Y-m-d H:i:s');

if (empty($market_name) || empty($timing)) {
    echo json_encode(["status" => "error", "message" => "Market name and timing are required in JSON body"]);
    exit;
}

//--- 2. FETCH MARKET INFO ---
$m_q = mysqli_query($con, "SELECT sn FROM starline_timings 
              WHERE (TRIM(name) = '$market_name' OR TRIM(market) = '$market_name') 
              AND TRIM(close) LIKE '$timing%' 
              LIMIT 1");
// $sql_market = "SELECT * FROM `starline_timings` where sn = 128";

// $m_q = mysqli_query($con, $sql_market);
// echo $sql_market;
// echo mysqli_num_rows($m_q);

if (mysqli_num_rows($m_q) == 0) {
    echo json_encode(["status" => "error", "message" => "Market '$market_name' at '$timing' not found in starline_timings"]);
    exit;
}
$m_data = mysqli_fetch_assoc($m_q);
$timing_sn = $m_data['sn'];

// --- 3. CHECK IF RESULT ALREADY EXISTS ---
$check = mysqli_query($con, "SELECT sn FROM starline_results WHERE market='$market_name' AND timing='$timing' AND date='$currentDate'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "Result already declared for this time today"]);
    exit;
}

// --- 4. 30% PAYOUT LOGIC ---
$pool_q = mysqli_query($con, "SELECT SUM(amount) as total FROM starline_games WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate'");
$pool_data = mysqli_fetch_assoc($pool_q);
$totalPool = (float)($pool_data['total'] ?? 0);
$targetPayout = $totalPool * 0.30; 

// Fetch current bets
$bets_q = mysqli_query($con, "SELECT number, amount, game FROM starline_games WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0'");
$bets = [];
$bettedNumbers = [];
while($b = mysqli_fetch_assoc($bets_q)) { 
    $bets[] = $b; 
    $bettedNumbers[] = $b['number'];
}

// Find candidates from full_sangam
$allPannas = mysqli_query($con, "SELECT close_ank FROM full_sangam");
$candidates = [];

while($pRow = mysqli_fetch_assoc($allPannas)) {
    $testPanna = $pRow['close_ank'];
    $testDigit = (string)getDigit($testPanna);
    $potPay = 0;

    foreach($bets as $bet) {
        if($bet['number'] == $testPanna || $bet['number'] == $testDigit) {
            $potPay += ($bet['amount'] * getStarlineRate($con, $bet['game']));
        }
    }
    
    // Valid if payout is <= 30% of pool
    if($potPay > 0 && $potPay <= $targetPayout) {
        $candidates[] = ['panna' => $testPanna, 'digit' => $testDigit, 'payout' => $potPay];
    }
}

// Selection logic
if(!empty($candidates)) {
    usort($candidates, function($a, $b) { return $b['payout'] <=> $a['payout']; });
    $winPanna = $candidates[0]['panna'];
    $winDigit = $candidates[0]['digit'];
    $finalPayout = $candidates[0]['payout'];
} else {
    // If no candidate under 30%, pick a number with 0 bets
    $f_q = mysqli_query($con, "SELECT close_ank FROM full_sangam WHERE close_ank NOT IN ('".implode("','", $bettedNumbers)."') ORDER BY RAND() LIMIT 1");
    $f_r = mysqli_fetch_assoc($f_q);
    $winPanna = $f_r['close_ank'];
    $winDigit = (string)getDigit($winPanna);
    $finalPayout = 0;
}

// --- 5. EXECUTE UPDATES ---
$batch_id = md5(time() . $market_name . rand());

// Insert Result
mysqli_query($con, "INSERT INTO `starline_results`(`market`, `timing`, `panna`, `number`, `date`, `created_at`) VALUES ('$market_name','$timing','$winPanna','$winDigit','$currentDate','$stamp')");
// 2. INSERT INTO MANUAL_BATCH (Added Modification)
$b_market = $market_name . '~' . $timing;
$batch_result = $winPanna . '-' . $winDigit;
mysqli_query($con, "INSERT INTO manual_batch (market, result, revert, created_at, batch_id, date) 
                    VALUES ('$b_market', '$batch_result', '0', '$stamp', '$batch_id', '$currentDate')");
// Pay Winners
$win_games = mysqli_query($con, "SELECT * FROM `starline_games` WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0' AND (number='$winPanna' OR number='$winDigit')");
while($w = mysqli_fetch_assoc($win_games)) {
    $amt = $w['amount'] * getStarlineRate($con, $w['game']);
    $user = $w['user'];
    
    mysqli_query($con, "UPDATE starline_games SET status='1', win_amount='$amt' WHERE sn='".$w['sn']."'");
    mysqli_query($con, "UPDATE users SET wallet=wallet+'$amt' WHERE mobile='$user'");
    
    // Log Transaction
    mysqli_query($con, "INSERT INTO transactions (user, amount, type, remark, created_at, batch_id, game_id) VALUES ('$user', '$amt', '1', 'Auto Win: $winPanna-$winDigit', '$stamp', '$batch_id', '".$w['sn']."')");
}

// Mark Losers
mysqli_query($con, "UPDATE starline_games SET status='2' WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0'");

// --- 6. JSON RESPONSE ---
echo json_encode([
    "status" => "success",
    "market" => $market_name,
    "timing" => $timing,
    "result" => "$winPanna-$winDigit",
    "pool" => $totalPool,
    "payout" => $finalPayout
]);