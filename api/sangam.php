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

// Check if user has enough wallet balance
$check = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' AND wallet >= '$total'");
if (mysqli_num_rows($check) == 0) {
    echo json_encode([
        'success' => "0",
        'msg' => "You don't have enough wallet balance"
    ]);
    return;
}

// Deduct total amount once from wallet
mysqli_query($con, "UPDATE users SET wallet = wallet - '$total' WHERE mobile='$mobile'");

// Process multiple bets
$numbers = explode(",", $number);
$amounts = explode(",", $amount);

// Clean bazar string
$bazar_clean = str_replace(" ", "_", $bazar);

if (count($numbers) != count($amounts)) {
    echo json_encode([
        'success' => "0",
        'msg' => "Mismatch between numbers and amounts count"
    ]);
    return;
}

for ($i = 0; $i < count($numbers); $i++) {
    $num = trim($numbers[$i]);
    $amt = trim($amounts[$i]);

    // Insert into games table
    mysqli_query($con, "INSERT INTO `games`(`user`, `game`, `bazar`, `date`, `game_type`, `number`, `amount`, `created_at`) 
        VALUES ('$mobile', '$game', '$bazar_clean', '$date', '$game_type', '$num', '$amt', '$stamp')");

    // Insert into transactions table
    $remark = "Bet Placed on $game market name $bazar_clean on number $num";
    $transaction_query = "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`, `owner`) 
                          VALUES ('$mobile', '$amt', '0', '$remark', '$stamp', '$mobile')";

    if (!mysqli_query($con, $transaction_query)) {
        // Log or handle transaction error if needed
        error_log("Transaction insert error: " . mysqli_error($con));
    }
}

echo json_encode([
    'success' => "1",
    'msg' => "Bets placed successfully"
]);
?>
