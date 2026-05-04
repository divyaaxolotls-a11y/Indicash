<?php
include "con.php";

function safe($con,$val){
    return mysqli_real_escape_string($con,trim($val));
}

/* INPUT */
$input = json_decode(file_get_contents("php://input"),true);
if(!$input){ $input=$_POST; }

$mobile   = safe($con,$input['mobile'] ?? '');
$session  = safe($con,$input['session'] ?? '');
$number   = $input['number'] ?? '';
$amount   = $input['amount'] ?? '';
$game     = safe($con,$input['game'] ?? '');
$bazar    = safe($con,$input['bazar'] ?? '');
$timing   = safe($con,$input['timing'] ?? '');

$timing = date("H:i",strtotime($timing));

date_default_timezone_set('Asia/Calcutta');

$stamp = time();
$date  = date("d/m/Y");


/* SESSION CHECK */

$auth = mysqli_query($con,
"SELECT wallet FROM users WHERE mobile='$mobile' AND session='$session' LIMIT 1");

if(mysqli_num_rows($auth)==0){

echo json_encode(['success'=>'0','msg'=>'Unauthorized']);
exit;

}

$user_row = mysqli_fetch_assoc($auth);
$current_wallet = (float)$user_row['wallet'];


/* CHECK JACKPOT MARKET */

$market_q = mysqli_query($con,"
SELECT * FROM jackpot_markets
WHERE LOWER(TRIM(name)) = LOWER(TRIM('$bazar'))
AND close='$timing'
AND is_active='1'
LIMIT 1
");

if(mysqli_num_rows($market_q)==0){

echo json_encode(['success'=>'0','msg'=>'Invalid Jackpot Market']);
exit;

}

$market_row = mysqli_fetch_assoc($market_q);

$timing_sn  = $market_row['sn'];
$close_time = $market_row['close'];


/* MARKET CLOSE CHECK */

$current_time = date("H:i");

if($current_time >= $close_time){

echo json_encode(['success'=>'0','msg'=>'Market Closed']);
exit;

}


/* MULTIPLE BETS */

$numbers = explode(",",$number);
$amounts = explode(",",$amount);

if(count($numbers)!=count($amounts)){

echo json_encode(['success'=>'0','msg'=>'Invalid bet format']);
exit;

}


$bet_details=[];
$total_bet=0;

for($i=0;$i<count($numbers);$i++){

$num = safe($con,$numbers[$i]);
$amt = (float)$amounts[$i];

if($amt < 10){

echo json_encode(['success'=>'0','msg'=>'Minimum bet is 10']);
exit;

}

$total_bet += $amt;

$bet_details[]="Market - $bazar , Num-$num - {$amt}INR";

}


/* WALLET CHECK */

if($current_wallet < $total_bet){

echo json_encode(['success'=>'0','msg'=>'Insufficient wallet balance']);
exit;

}


/* START LOOP */

for($i=0;$i<count($numbers);$i++){

$num = safe($con,$numbers[$i]);
$amt = (float)$amounts[$i];

/* WALLET DEDUCT */

mysqli_query($con,"
UPDATE users SET wallet = wallet - $amt
WHERE mobile='$mobile' AND wallet >= $amt
");
$wallet_after = $current_wallet - $amt;

if(mysqli_affected_rows($con)==0){

echo json_encode(['success'=>'0','msg'=>'Insufficient wallet']);
exit;

}


/* INSERT GAME */

mysqli_query($con,"
INSERT INTO jackpot_games
(user,game,bazar,timing_sn,date,number,amount,status,created_at,win_amount,is_loss)
VALUES
('$mobile','$game','$bazar','$timing_sn','$date','$num','$amt','0','$stamp','0','0')
");


/* TRANSACTION */
// $remark = "Type:Bet | Game:$game | Market:$bazar | Number:$num | Amount:$amt";
$remark_text = "Game:$game | Market:$bazar | Session:$timing | Number:$num | Bet Placed";
$remark = mysqli_real_escape_string($con, $remark_text);


mysqli_query($con,"
INSERT INTO transactions
(user,amount,wallet_before,wallet_after,type,remark,created_at,owner)
VALUES
('$mobile','$amt','$current_wallet','$wallet_after','0',
'$remark',
'$stamp','$mobile')
");

}


/* NOTIFICATION */

$all_bets = implode(" | ",$bet_details);

$msg="New Jackpot bets game - $game, user - $mobile, bets - ( $all_bets )";

$msg_safe=mysqli_real_escape_string($con,$msg);

mysqli_query($con,"
INSERT INTO notifications (msg,created_at)
VALUES ('$msg_safe','$stamp')
");


/* LOGIN LOG */

$ip_address=$_SERVER['REMOTE_ADDR'];
$user_agent=mysqli_real_escape_string($con,$_SERVER['HTTP_USER_AGENT']);

$log_remark=mysqli_real_escape_string($con,
"Jackpot Bet Placed on game - $game | Number: $number | Amount: $amount"
);

$stmt=$con->prepare(
"INSERT INTO login_logs (user_email,ip_address,user_agent,remark)
VALUES (?,?,?,?)"
);

$stmt->bind_param("ssss",$mobile,$ip_address,$user_agent,$log_remark);
$stmt->execute();
$stmt->close();


echo json_encode([
'success'=>'1',
'msg'=>'Jackpot Bet Placed Successfully'
]);
?>