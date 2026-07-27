<?php
include "con.php";

extract($_REQUEST);
$date = date('d/m/Y');
$stamp = time(); // current timestamp
$time = date("H:i", $stamp);
$day = strtoupper(date("l", $stamp));

// Validate minimum total bet amount
if ((int)$total < 10) {
    echo json_encode([
        'success' => "0",
        'msg' => "Minimum bet amount should be 10 INR"
    ]);
    return;
}

// 1. Fetch current wallet balance BEFORE deduction
$user_res = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' LIMIT 1");
$user_data = mysqli_fetch_assoc($user_res);

if (!$user_data || $user_data['wallet'] < $total) {
    echo json_encode([
        'success' => "0",
        'msg' => "You don't have enough wallet balance"
    ]);
    return;
}

$initial_wallet = (float)$user_data['wallet'];

// 2. Deduct total amount once from users table
$final_wallet = $initial_wallet - $total;
mysqli_query($con, "UPDATE users SET wallet = '$final_wallet' WHERE mobile='$mobile'");

// Process multiple bets
$numbers = explode(",", $number);
$amounts = explode(",", $amount);
$bazar_clean = str_replace(" ", "_", $bazar);

if (count($numbers) != count($amounts)) {
    echo json_encode([
        'success' => "0",
        'msg' => "Mismatch between numbers and amounts count"
    ]);
    return;
}

// 3. Track running balance for history accuracy
$running_balance = $initial_wallet;

for ($i = 0; $i < count($numbers); $i++) {
    $num = trim($numbers[$i]);
    $amt = (float)trim($amounts[$i]);

    $wallet_before = $running_balance;
    $wallet_after  = $running_balance - $amt;

    // Insert into games table with wallet_before and wallet_after
    $game_query = "INSERT INTO `games`(`user`, `game`, `bazar`, `date`, `game_type`, `number`, `amount`, `created_at`, `wallet_before`, `wallet_after`) 
                   VALUES ('$mobile', '$game', '$bazar_clean', '$date', '$game_type', '$num', '$amt', '$stamp', '$wallet_before', '$wallet_after')";
    mysqli_query($con, $game_query);

    // Insert into transactions table (Assuming columns exist there too)
    $remark = "Bet Placed on $game | Market: $bazar_clean | Num: $num";
    $transaction_query = "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`, `owner`) 
                          VALUES ('$mobile', '$amt', '$wallet_before', '$wallet_after', '0', '$remark', '$stamp', '$mobile')";

    mysqli_query($con, $transaction_query);

    // Update running balance for the next item in the loop
    $running_balance = $wallet_after;
}

echo json_encode([
    'success' => "1",
    'msg' => "Bets placed successfully"
]);
?>