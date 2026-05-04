<?php
include "con.php";

// ─── HELPER ─────────────────────────────────────────
function safe($con, $val) {
    return mysqli_real_escape_string($con, trim($val));
}

// // ─── INPUT ──────────────────────────────────────────
// $mobile   = safe($con, $_POST['mobile']   ?? '');
// $session  = safe($con, $_POST['session']  ?? '');
// $number   = $_POST['number'] ?? '';
// $amount   = $_POST['amount'] ?? '';
// $game     = safe($con, $_POST['game']     ?? '');
// $bazar    = safe($con, $_POST['bazar']    ?? '');
// $timing   = safe($con, $_POST['timing']   ?? '');
// $total    = (float)($_POST['total'] ?? 0);
// ─── INPUT ──────────────────────────────────────────
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    $input = $_POST; // fallback for form-data
}

$mobile   = safe($con, $input['mobile']   ?? '');
$session  = safe($con, $input['session']  ?? '');
$number   = $input['number'] ?? '';
$amount   = $input['amount'] ?? '';
$game     = safe($con, $input['game']     ?? '');
$bazar    = safe($con, $input['bazar']    ?? '');
$timing   = safe($con, $input['timing']   ?? '');
$total    = (float)($input['total'] ?? 0);
$timing = date("H:i", strtotime($timing));
// ─── TIME ───────────────────────────────────────────
date_default_timezone_set('Asia/Calcutta');
$stamp = time();
$date  = date('d/m/Y');

// ─── SESSION CHECK ──────────────────────────────────
$auth = mysqli_query($con,
    "SELECT wallet FROM users WHERE mobile='$mobile' AND session='$session' LIMIT 1"
);

if (mysqli_num_rows($auth) == 0) {
    echo json_encode(['success'=>'0','msg'=>'Unauthorized']);
    exit;
}

$user_row = mysqli_fetch_assoc($auth);
$current_wallet = (float)$user_row['wallet'];

// ─── WALLET CHECK ───────────────────────────────────
if ($current_wallet < $total) {
    echo json_encode(['success'=>'0','msg'=>'Insufficient wallet balance']);
    exit;
}

// ─── CHECK STARLINE MARKET ──────────────────────────
$market_q = mysqli_query($con,
    "SELECT * FROM starline_markets 
     WHERE LOWER(TRIM(name)) = LOWER(TRIM('$bazar')) 
     LIMIT 1"
);

if (mysqli_num_rows($market_q) == 0) {
    echo json_encode(['success'=>'0','msg'=>'Invalid Starline Market']);
    exit;
}

// ─── CHECK TIMING ───────────────────────────────────
$timing_q = mysqli_query($con,
    "SELECT * FROM starline_timings
     WHERE name = '$bazar'
     AND close = '$timing'
     LIMIT 1"
);

if (mysqli_num_rows($timing_q) == 0) {
    echo json_encode(['success'=>'0','msg'=>'Invalid Market Timing']);
    exit;
}

$timing_row = mysqli_fetch_assoc($timing_q);
$timing_sn  = $timing_row['sn'];
$close_time = $timing_row['close'];

// Get current server time
$current_time = date("H:i");

// Check if market is still open
if ($current_time >= $close_time) {
    echo json_encode(['success'=>'0','msg'=>'Market Closed']);
    exit;
}
// ─── PARSE MULTIPLE BETS ────────────────────────────
$numbers = explode(",", $number);
$amounts = explode(",", $amount);

if (count($numbers) != count($amounts)) {
    echo json_encode(['success'=>'0','msg'=>'Invalid bet format']);
    exit;
}
$bet_details = [];
// ─── START LOOP ─────────────────────────────────────
for ($i = 0; $i < count($numbers); $i++) {

    $num = safe($con, $numbers[$i]);
    $amt = (float)$amounts[$i];
$bet_details[] = "Market - $bazar , Num-$num - {$amt}INR";
    if ($amt < 10) {
        echo json_encode(['success'=>'0','msg'=>'Minimum bet is 10']);
        exit;
    }

    // Atomic Wallet Deduction
    mysqli_query($con,
        "UPDATE users SET wallet = wallet - $amt
         WHERE mobile='$mobile' AND wallet >= $amt"
    );
    $wallet_after = $current_wallet - $amt;
    if (mysqli_affected_rows($con) == 0) {
        echo json_encode(['success'=>'0','msg'=>'Insufficient wallet (concurrent issue)']);
        exit;
    }

    // Insert Starline Game
    mysqli_query($con,
        "INSERT INTO starline_games
        (user, game, bazar, timing_sn, date, number, amount, status, created_at, win_amount, is_loss)
        VALUES
        ('$mobile','$game','$bazar','$timing_sn','$date','$num','$amt','0','$stamp','0','0')"
    );
    // $remark = "Type:Starline | Game:$game | Market:$bazar | Number:$num | Amount:$amt";
    $remark_text = "Game:$game | Market:$bazar | Session:$timing | Number:$num | Bet Placed";
    $remark = mysqli_real_escape_string($con, $remark_text);
    // echo $remark;
    // exit;
    // Insert Transaction
    mysqli_query($con,
        "INSERT INTO transactions
        (user, amount,wallet_before,wallet_after, type, remark, created_at, owner)
        VALUES
        ('$mobile','$amt','$current_wallet','$wallet_after','0',
         '$remark',
         '$stamp','$mobile')"
    );
}

// ─── NOTIFICATION ─────────────────────────────────────────────────
$all_bets = implode(" | ", $bet_details);

$msg = "New bets game - $game, user - $mobile, bets - ( $all_bets )";

$msg_safe = mysqli_real_escape_string($con, $msg);

mysqli_query($con,
    "INSERT INTO notifications (msg, created_at)
     VALUES ('$msg_safe','$stamp')"
);

// ─── LOGIN LOG ───────────────────────────────────────────────────
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = mysqli_real_escape_string($con, $_SERVER['HTTP_USER_AGENT']);
$log_remark = mysqli_real_escape_string($con,
    "Starline Bet Placed on game - $game | Number: $number | Amount: $amount"
);

$stmt = $con->prepare(
    "INSERT INTO login_logs (user_email, ip_address, user_agent, remark)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $mobile, $ip_address, $user_agent, $log_remark);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => '1',
    'msg'     => 'Starline Bet Placed Successfully'
]);