<?php
$date = date('d/m/Y', strtotime($_POST['date']));
$gameID = $_POST['gameID'];

include('config.php');

$market = str_replace(" ", "_", $gameID);
$market_1 = str_replace(" ", "_", $gameID . ' OPEN');
$market_2 = str_replace(" ", "_", $gameID . ' CLOSE');

$select = mysqli_query($con, "SELECT * FROM `games` WHERE `date`='$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2')");

$success = true;

while ($row = mysqli_fetch_array($select)) {
    $bidTxId = $row['sn'];
    $amount = $row['amount'];
    $mobile = $row['user'];

    $wallet = mysqli_query($con, "UPDATE users SET wallet = wallet + $amount WHERE mobile = '$mobile'");
    $withdrawUpdate = mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$mobile', '$amount', '1', 'Bid revert refund', 'admin@gmail.com', NOW(), '0', '0')");

    if (!$wallet || !$withdrawUpdate) {
        $success = false;
        break;
    }

    $removeBidHistory = mysqli_query($con, "DELETE FROM `games` WHERE `sn`='$bidTxId'");

    if (!$removeBidHistory) {
        $success = false;
        break;
    }
}

echo $success ? '1' : '0';
?>
