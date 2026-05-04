<?php
header("Content-Type: application/json");
include "con.php";

// Helper to get Jackpot Rate (Usually 1:90 for 00-99)
function getJackpotRate($con) {
    $q = mysqli_query($con, "SELECT rate FROM rates WHERE game_name='jackpot' LIMIT 1");
    $r = mysqli_fetch_assoc($q);
    return (float)($r['rate'] ?? 90); 
}
// function getJackpotRate($con) {
//     $q = mysqli_query($con, "SELECT jodi FROM rates LIMIT 1"); 
//     // or use correct column for jackpot (if exists)

//     $r = mysqli_fetch_assoc($q);

//     if(isset($r['jodi'])) {
//         $parts = explode("/", $r['jodi']); // 10/950
//         return (float)($parts[1] ?? 90);   // return 950
//     }

//     return 90;
// }

// --- 1. GET POST DATA ---
$input = json_decode(file_get_contents("php://input"), true);
$market_name = isset($input['market']) ? mysqli_real_escape_string($con, trim($input['market'])) : ''; 
$timing = isset($input['timing']) ? mysqli_real_escape_string($con, trim($input['timing'])) : '';      
$currentDate = date('d/m/Y');
$stamp = date('Y-m-d H:i:s');

if (empty($market_name) || empty($timing)) {
    echo json_encode(["status" => "error", "message" => "Market and timing required"]);
    exit;
}

// --- 2. FETCH MARKET INFO ---
$m_q = mysqli_query($con, "SELECT sn FROM jackpot_markets WHERE TRIM(name) = '$market_name' AND TRIM(close) LIKE '$timing%' LIMIT 1");
if (mysqli_num_rows($m_q) == 0) {
    echo json_encode(["status" => "error", "message" => "Jackpot Market not found"]);
    exit;
}
$m_data = mysqli_fetch_assoc($m_q);
$timing_sn = $m_data['sn'];

// --- 3. CHECK IF RESULT ALREADY EXISTS ---
$check = mysqli_query($con, "SELECT sn FROM jackpot_results WHERE market='$market_name' AND timing='$timing' AND date='$currentDate'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "Result already declared"]);
    exit;
}

// --- 4. 30% PAYOUT LOGIC ---
$pool_q = mysqli_query($con, "SELECT SUM(amount) as total FROM jackpot_games WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate'");
$pool_data = mysqli_fetch_assoc($pool_q);
$totalPool = (float)($pool_data['total'] ?? 0);
$targetPayout = $totalPool * 0.30; 

// Fetch current bets
$bets_q = mysqli_query($con, "SELECT number, amount, game FROM jackpot_games WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0'");
$bets = [];
$bettedNumbers = [];
while($b = mysqli_fetch_assoc($bets_q)) { 
    $bets[] = $b; 
    $formatted = str_pad($b['number'], 2, "0", STR_PAD_LEFT);
    $bettedNumbers[] = $formatted;
}

$jackpotRate = getJackpotRate($con);
$candidates = [];

// Check 00 to 99
for ($i = 0; $i <= 99; $i++) {
    $testNum = str_pad($i, 2, "0", STR_PAD_LEFT);
    $potPay = 0;
    foreach($bets as $bet) {
        if(str_pad($bet['number'], 2, "0", STR_PAD_LEFT) === $testNum) {
            $potPay += ($bet['amount'] * $jackpotRate);
        }
    }
    if($potPay > 0 && $potPay <= $targetPayout) {
        $candidates[] = ['number' => $testNum, 'payout' => $potPay];
    }
}

// Result Selection
if(!empty($candidates)) {
    usort($candidates, function($a, $b) { return $b['payout'] <=> $a['payout']; });
    $winNumber = $candidates[0]['number'];
    $finalPayout = $candidates[0]['payout'];
} else {
    // Pick zero-bet number
    $available = [];
    for($j=0; $j<=99; $j++){
        $check = str_pad($j, 2, "0", STR_PAD_LEFT);
        if(!in_array($check, $bettedNumbers)) { $available[] = $check; }
    }
    $winNumber = !empty($available) ? $available[array_rand($available)] : str_pad(rand(0,99), 2, "0", STR_PAD_LEFT);
    $finalPayout = 0;
}

// --- 5. EXECUTE UPDATES ---
$batch_id = md5($stamp . $market_name . $winNumber . rand());

// 1. Insert Result
$res_insert = mysqli_query($con, "INSERT INTO `jackpot_results`(`market`, `timing`, `panna`, `number`, `date`, `created_at`) VALUES ('$market_name','$timing','','$winNumber','$currentDate','$stamp')");

if($res_insert) {

    // 2. INSERT INTO MANUAL_BATCH (The part we missed)
    $b_market = $market_name.'~'.$timing;
    mysqli_query($con, "INSERT INTO manual_batch (market, result, revert, created_at, batch_id, date) 
                        VALUES ('$b_market', '$winNumber', '0', '$stamp', '$batch_id', '$currentDate')");

    // 3. Pay Winners
    $win_games = mysqli_query($con, "SELECT * FROM `jackpot_games` WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0'");
    while($w = mysqli_fetch_assoc($win_games)) {
        if(str_pad($w['number'], 2, "0", STR_PAD_LEFT) === $winNumber) {
            $amt = $w['amount'] * $jackpotRate;
            $user = $w['user'];
            
            mysqli_query($con, "UPDATE jackpot_games SET status='1', win_amount='$amt' WHERE sn='".$w['sn']."'");
            mysqli_query($con, "UPDATE users SET wallet=wallet+'$amt' WHERE mobile='$user'");
            mysqli_query($con, "INSERT INTO transactions (user, amount, type, remark, created_at, batch_id, game_id) 
                                VALUES ('$user', '$amt', '1', 'Jackpot Win: $winNumber', '$stamp', '$batch_id', '".$w['sn']."')");
        }
    }

    // 4. Mark Losers
    mysqli_query($con, "UPDATE jackpot_games SET status='2' WHERE bazar='$market_name' AND timing_sn='$timing_sn' AND date='$currentDate' AND status='0'");

    echo json_encode([
        "status" => "success",
        "market" => $market_name,
        "declared_number" => $winNumber,
        "batch_id" => $batch_id,
        "payout" => $finalPayout
    ]);
}