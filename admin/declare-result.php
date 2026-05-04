<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('header.php'); 

// -------------------------------------------------------------------------
// 1. SAFEGUARD: Define log_action if missing
// -------------------------------------------------------------------------
if (!function_exists('log_action')) {
    function log_action($action) {
        global $con;
        $timestamp = date('Y-m-d H:i:s');
        $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0; 
        $ip = $_SERVER['REMOTE_ADDR'];
        $q = "INSERT INTO `admin_logs` (`admin_id`, `action`, `ip`, `created_at`) VALUES ('$admin_id', '$action', '$ip', '$timestamp')";
        @mysqli_query($con, $q); 
    }
}
function isGameOpenOnDate($days_string, $date_value) {
    // 1. Get the Day of the week for the selected date (e.g., "MONDAY")
    $day_of_week = strtoupper(date('l', strtotime($date_value)));
    
    // 2. Search for the pattern "DAYNAME(CLOSED)"
    $search_pattern = $day_of_week . "(CLOSED)";
    
    // 3. If "(CLOSED)" is NOT found for that day, the game is open
    if (strpos($days_string, $search_pattern) === false) {
        return true; 
    }
    return false;
}
// -------------------------------------------------------------------------
// 2. CONFIGURATION
// -------------------------------------------------------------------------
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// -------------------------------------------------------------------------
// 3. LOGIC: MANUAL SUBMISSION REDIRECT
// -------------------------------------------------------------------------
if(isset($_REQUEST['submit_manual2'])){
    extract($_REQUEST);
    echo "<script>window.location.href = 'winners.php?date=$date&session=$session&digit=$digit&panna=$panna&market=$market'</script>";
}

// -------------------------------------------------------------------------
// 4. LOGIC: BID REVERT & REFUND (Integrated from your file)
// -------------------------------------------------------------------------
// if(isset($_POST['cancel_game_refund'])){
    
//     // Security Check
//     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//         echo "<script>alert('Invalid CSRF token.'); window.location.href = 'declare-result.php';</script>"; exit;
//     }

//     $market = $_POST['market'];
//     $session = $_POST['session'];
//     $date = date('d/m/Y', strtotime($_POST['date']));
    
//     $bazar_query = "";
    
//     if($session == 'open'){
//         $target_bazar = str_replace(" ", "_", $market . " OPEN");
//         $bazar_query = "AND `bazar`='$target_bazar'";
//     } elseif($session == 'close'){
//         $target_bazar = str_replace(" ", "_", $market . " CLOSE");
//         $bazar_query = "AND `bazar`='$target_bazar'";
//     } else {
//         $m1 = str_replace(" ", "_", $market);
//         $m2 = str_replace(" ", "_", $market . ' OPEN');
//         $m3 = str_replace(" ", "_", $market . ' CLOSE');
//         $bazar_query = "AND (`bazar`='$m1' OR `bazar`='$m2' OR `bazar`='$m3')";
//     }

//     $select = mysqli_query($con, "SELECT * FROM `games` WHERE `date`='$date' $bazar_query");
//     $count = mysqli_num_rows($select);
//     $success = true;

//     if($count > 0){
//         while ($row = mysqli_fetch_array($select)) {
//             $bidTxId = $row['sn'];
//             $amount = $row['amount'];
//             $mobile = $row['user'];

//             $wallet = mysqli_query($con, "UPDATE users SET wallet = wallet + $amount WHERE mobile = '$mobile'");
//             $remark = "Bid revert refund ($market $session)";
//             $withdrawUpdate = mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$mobile', '$amount', '1', '$remark', 'admin@gmail.com', NOW(), '0', '0')");

//             if (!$wallet || !$withdrawUpdate) { $success = false; }

//             $removeBidHistory = mysqli_query($con, "DELETE FROM `games` WHERE `sn`='$bidTxId'");
//             if (!$removeBidHistory) { $success = false; }
//         }
        
//         if($success){
//             echo "<script>alert('✅ Success! $count bets refunded.'); window.location.href = 'declare-result.php';</script>";
//         } else {
//             echo "<script>alert('❌ Error: Some refunds failed.'); window.location.href = 'declare-result.php';</script>";
//         }
        
//     } else {
//         echo "<script>alert('⚠️ No bets found to refund for this session.'); window.location.href = 'declare-result.php';</script>";
//     }
// }

// -------------------------------------------------------------------------
// REVERT RESULT & RECOVER WINNINGS
// -------------------------------------------------------------------------
if(isset($_POST['cancel_game_refund'])){
    
    // Security Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token.'); window.location.href = 'declare-result.php';</script>"; exit;
    }

    $market = mysqli_real_escape_string($con, $_POST['market']);
    $session = mysqli_real_escape_string($con, $_POST['session']);
    $date = date('d/m/Y', strtotime($_POST['date']));
    
    // 1. Identify the Batch ID or the Results to delete
    $res_query = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$market' AND date='$date'");
    $existing_result = mysqli_fetch_array($res_query);

    if($existing_result){
        // 2. Find all 'Winning' transactions for this specific market and date
        // We look for "Winning" remarks linked to this market
        $search_remark = "Winning";
        $winning_tx = mysqli_query($con, "SELECT * FROM `transactions` WHERE `remark`='$search_remark' AND DATE(`created_at`) = CURDATE()"); 
        
        /* 
           Note: A better way is to use a batch_id, but based on your current DB structure, 
           we will target games associated with this market/date.
        */
        
        $bazar_query = "";
        // if($session == 'open'){
        //     $target_bazar = str_replace(" ", "_", $market . " OPEN");
        //     $bazar_query = "AND `bazar`='$target_bazar'";
        // } else {
        //     $target_bazar = str_replace(" ", "_", $market . " CLOSE");
        //     $bazar_query = "AND `bazar`='$target_bazar'";
        // }
        $m_open  = str_replace(" ", "_", $market . " OPEN");
        $m_close = str_replace(" ", "_", $market . " CLOSE");
        $m_jodi  = str_replace(" ", "_", $market);
        $bazar_query = "AND (`bazar`='$m_open' OR `bazar`='$m_close' OR `bazar`='$m_jodi')";

        // 3. GET ALL WINNERS AND DEDUCT MONEY
        $winners = mysqli_query($con, "SELECT * FROM `games` WHERE `date`='$date' $bazar_query AND `status`='1' AND `is_loss`='0'");
        while($w = mysqli_fetch_array($winners)){
            $mobile = $w['user'];
            $game_id = $w['sn'];
            
            // Calculate how much they won to take it back
            // We find the transaction related to this game_id
            // $tx_check = mysqli_query($con, "SELECT amount FROM transactions WHERE game_id='$game_id' AND remark='Winning' LIMIT 1");
            // if($tx_row = mysqli_fetch_array($tx_check)){
            //     $win_amount = $tx_row['amount'];
            //     // DEDUCT from wallet
            //     mysqli_query($con, "UPDATE users SET wallet = wallet - $win_amount WHERE mobile = '$mobile'");
            //     // Delete the transaction record
            //     mysqli_query($con, "DELETE FROM transactions WHERE game_id='$game_id' AND remark='Winning'");
            // }
            // 3. GET ALL WINNERS AND DEDUCT MONEY
            $winners = mysqli_query($con, "SELECT * FROM `games` WHERE `date`='$date' $bazar_query AND `status`='1' AND `is_loss`='0'");
            while($w = mysqli_fetch_array($winners)){
                $mobile = $w['user'];
                $game_id = $w['sn'];
                
                // FIXED: Search by game_id only, no remark filter
                // because remark format changed from 'Winning' to 'Result Win'
                $tx_check = mysqli_query($con, "SELECT * FROM transactions WHERE game_id='$game_id' AND type='1' LIMIT 1");
                if($tx_row = mysqli_fetch_array($tx_check)){
                    $win_amount = $tx_row['amount'];
                    
                    // Deduct from wallet
                    mysqli_query($con, "UPDATE users SET wallet = wallet - '$win_amount' WHERE mobile = '$mobile'");
                    
                    // Delete ALL transactions for this game_id (handles both old and new format)
                    mysqli_query($con, "DELETE FROM transactions WHERE game_id='$game_id' AND type='1'");
                }
            }
        }

        // 4. RESET GAMES STATUS (Set back to pending and remove loss status)
        mysqli_query($con, "UPDATE `games` SET `status`='0', `is_loss`='0' WHERE `date`='$date' $bazar_query");

        // 5. REMOVE THE DECLARED RESULT
        if($session == 'open'){
            // If reverting open, we usually clear open fields
            mysqli_query($con, "UPDATE manual_market_results SET open='', open_panna='' WHERE market='$market' AND date='$date'");
        } else {
            // If reverting close, clear close fields
            mysqli_query($con, "UPDATE manual_market_results SET close='', close_panna='' WHERE market='$market' AND date='$date'");
        }
        
        // Final cleanup: if both open and close are empty, delete the row
        mysqli_query($con, "DELETE FROM manual_market_results WHERE market='$market' AND date='$date' AND open='' AND close=''");

        log_action("Reverted Result for $market $session - Winnings Deducted");
        echo "<script>alert('✅ Result Reverted! Winnings deducted from users and buttons restored.'); window.location.href = 'declare-result.php';</script>";

    } else {
        echo "<script>alert('⚠️ No result found to revert.'); window.location.href = 'declare-result.php';</script>";
    }
}
// -------------------------------------------------------------------------
// 5. LOGIC: DECLARE RESULT (Main Functionality)
// -------------------------------------------------------------------------
if(isset($_REQUEST['submit_manual'])){
    
    if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token.'); window.location.href = 'declare-result.php';</script>"; exit; 
    }
    
    extract($_REQUEST);
    $date = date('d/m/Y',strtotime($_REQUEST['date']));
    
    if($session == 'open'){
        $open = $digit; $opanna = $panna;
        if($open == "" && $opanna == ""){ echo "<script>alert('Result cannot be empty'); window.location.href='declare-result.php';</script>"; exit(); }
        $close = ""; $cpanna = "";
    } else {
        $chk_query = mysqli_query($con, "select * from manual_market_results where market='$market' AND date='$date'");
        $chk_res = mysqli_fetch_array($chk_query);
        if(!$chk_res){ echo "<script>alert('Error: Please Declare Open Result First!'); window.location.href='declare-result.php';</script>"; exit(); }
        $open = $chk_res['open']; $opanna = $chk_res['open_panna'];
        $close = $digit; $cpanna = $panna;
    }
    
    $chk_query = mysqli_query($con, "select sn from manual_market_results where market='$market' AND date='$date'");
    
    if(mysqli_num_rows($chk_query) > 0){
        $chk_res = mysqli_fetch_array($chk_query); $sn = $chk_res['sn'];
        $q = "update manual_market_results set close='$close', close_panna='$cpanna' where sn='$sn'";
        if(mysqli_query($con, $q)){ log_action('Result Updated: '.$market); }
    } else {
        $q = "INSERT INTO `manual_market_results`(`market`, `date`, `open_panna`, `open`, `close`, `close_panna`, `created_at`) VALUES ('$market','$date','$opanna','$open','$close','$cpanna','$stamp')";
        if(mysqli_query($con, $q)){ log_action('Result Declared: '.$market); }
    }
    
    $batch_id = md5($stamp.$market.rand().$open.$close.$date.$day.$time);
    $batch_result = $opanna.'-'.$open.$close.'-'.$cpanna;
    mysqli_query($con, "INSERT INTO `manual_batch`( `market`, `result`, `revert`, `created_at`, `batch_id`,`date`) VALUES ('$market','$batch_result','0','$stamp','$batch_id','$date')");
    $xvm = mysqli_query($con, "select * from rate where sn='1'");
    $xv = mysqli_fetch_array($xvm);

    if($open != ""){
        $mrk = str_replace(" ","_",$market.' OPEN');
        $xx = mysqli_query($con, "select * from games where bazar='$mrk' AND game='single' AND date='$date' AND number='$open' AND status='0' AND is_loss='0'");
        while($x = mysqli_fetch_array($xx)){
            $sn = $x['sn']; $user = $x['user']; $amount = $x['amount']*$xv[$x['game']]; 
            mysqli_query($con, "update games set status='1' where sn='$sn'");
            // mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
            // $remrk = "Game:".$x['game']." | Market:".$market." | Number:".$open." | Result Win";
            // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1',$remrk,'$stamp','$batch_id','$sn')");
            // 1. Get current wallet
            $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user' LIMIT 1");
            $wallet_row = mysqli_fetch_assoc($wallet_q);
            $wallet_before = (float)$wallet_row['wallet'];
            $wallet_after  = $wallet_before + $amount;
            
            // 2. Update wallet (already exists in your code)
            mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
            
            // 3. Insert transaction with wallet_before and wallet_after
            $remrk = "Game:".$x['game']." | Market:".$market." | Session:Open | Number:".$open." | Bet:".$x['amount']." | Result Win ";
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) 
            VALUES ('$user','$amount','$wallet_before','$wallet_after','1','$remrk','$stamp','$batch_id','$sn')");
            sendNotification("Congratulations","You won $amount",$user);
        }
        mysqli_query($con, "UPDATE games set is_loss='1' , status='1' where bazar='$mrk' AND game='single' AND date='$date' AND number!='$open' AND is_loss='0'");
    }
    
    if($opanna != ""){
        $bazar = str_replace(" ","_",$market.' OPEN');
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0'");
        while($x = mysqli_fetch_array($xx)){
            $sn = $x['sn']; $user = $x['user']; $amount = $x['amount']*$xv[$x['game']]; 
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
                // mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                // $remrk = "Game:".$x['game']." | Market:".$market." | Number:".$open." | Result Win";
                // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1',$remrk,'$stamp','$batch_id','$sn')");
                // 1. Get current wallet
                $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user' LIMIT 1");
                $wallet_row = mysqli_fetch_assoc($wallet_q);
                $wallet_before = (float)$wallet_row['wallet'];
                $wallet_after  = $wallet_before + $amount;
                
                // 2. Update wallet (already exists in your code)
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                // 3. Insert transaction with wallet_before and wallet_after
                $remrk = "Game:".$x['game']." | Market:".$market." | Session:Open | Number:".$opanna." | Bet:".$x['amount']." | Result Win";
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) 
                VALUES ('$user','$amount','$wallet_before','$wallet_after','1','$remrk','$stamp','$batch_id','$sn')");
                sendNotification("Congratulations","You won $amount",$user);
            }
        }
        mysqli_query($con, "UPDATE games set is_loss='1' , status='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
    }

    if($close != ""){
        $bazar = str_replace(" ","_",$market.' CLOSE');
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND game='single' AND date='$date' AND number='$close' AND status='0' AND is_loss='0'");
        while($x = mysqli_fetch_array($xx)){
            $sn = $x['sn']; $user = $x['user']; $amount = $x['amount']*$xv[$x['game']]; 
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
                // mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                // $remrk = "Game:".$x['game']." | Market:".$market." | Number:".$open." | Result Win";
                // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1',$remrk,'$stamp','$batch_id','$sn')");
                // 1. Get current wallet
                $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user' LIMIT 1");
                $wallet_row = mysqli_fetch_assoc($wallet_q);
                $wallet_before = (float)$wallet_row['wallet'];
                $wallet_after  = $wallet_before + $amount;
                
                // 2. Update wallet (already exists in your code)
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                // 3. Insert transaction with wallet_before and wallet_after
                $remrk = "Game:".$x['game']." | Market:".$market."| Session:Close | Number:".$close." | Bet:".$x['amount']." | Result Win";
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) 
                VALUES ('$user','$amount','$wallet_before','$wallet_after','1','$remrk','$stamp','$batch_id','$sn')");
                sendNotification("Congratulations","You won $amount",$user);
            }
        }
        mysqli_query($con, "UPDATE games set is_loss='1' , status='1' where bazar='$bazar' AND game='single' AND date='$date' AND number!='$close' AND status='0' AND is_loss='0'");
    }

    if($cpanna != ""){
        $bazar = str_replace(" ","_",$market.' CLOSE');
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        while($x = mysqli_fetch_array($xx)){
            $sn = $x['sn']; $user = $x['user']; $amount = $x['amount']*$xv[$x['game']]; 
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
                // mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                // $remrk = "Game:".$x['game']." | Market:".$market." | Number:".$open." | Result Win";
                // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1',$remrk,'$stamp','$batch_id','$sn')");
                // 1. Get current wallet
                $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user' LIMIT 1");
                $wallet_row = mysqli_fetch_assoc($wallet_q);
                $wallet_before = (float)$wallet_row['wallet'];
                $wallet_after  = $wallet_before + $amount;
                
                // 2. Update wallet (already exists in your code)
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                // 3. Insert transaction with wallet_before and wallet_after
                $remrk = "Game:".$x['game']." | Market:".$market." | Session:Close | Number:".$cpanna." | Bet:".$x['amount']." |Result Win";
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) 
                VALUES ('$user','$amount','$wallet_before','$wallet_after','1','$remrk','$stamp','$batch_id','$sn')");
                sendNotification("Congratulations","You won $amount",$user);
            }
        }
        mysqli_query($con, "UPDATE games set is_loss='1' , status='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
    }

    if($open != "" && $close != ""){
        $full_num = $open.$close;
        $bazar = str_replace(" ","_",$market);
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND game='jodi' AND date='$date' AND number='$full_num' AND status='0' AND is_loss='0'");
        while($x = mysqli_fetch_array($xx)){
            $sn = $x['sn']; $user = $x['user']; $amount = $x['amount']*$xv[$x['game']]; 
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
                // mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                // $remrk = "Game:".$x['game']." | Market:".$market." | Number:".$open." | Result Win";
                // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1',$remrk,'$stamp','$batch_id','$sn')");
                // 1. Get current wallet
                $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user' LIMIT 1");
                $wallet_row = mysqli_fetch_assoc($wallet_q);
                $wallet_before = (float)$wallet_row['wallet'];
                $wallet_after  = $wallet_before + $amount;
                
                // 2. Update wallet (already exists in your code)
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                // 3. Insert transaction with wallet_before and wallet_after
                $remrk = "Game:".$x['game']." | Market:".$market." | Session:Open-Close | Number:".$full_num." | Bet:".$x['amount']." | Result Win";
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) 
                VALUES ('$user','$amount','$wallet_before','$wallet_after','1','$remrk','$stamp','$batch_id','$sn')");
                sendNotification("Congratulations","You won $amount",$user);
            }
        }
        mysqli_query($con, "UPDATE games set is_loss='1' , status='1' where bazar='$bazar' AND game='jodi' AND date='$date' AND number!='$full_num' AND status='0' AND is_loss='0'");
    }

    echo "<script>alert('Result Declared Successfully!'); window.location.href = 'declare-result.php';</script>";
}
?>

<style>
    /* ===== BASE ===== */
    body { background-color: #f1f1f1; }

    .content-wrapper {
        overflow-x: hidden;
    }

    /* Remove default container padding on mobile */
    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
        .container-fluid { padding-left: 4px !important; padding-right: 4px !important; }
    }

    /* ===== GAME LIST WRAPPER ===== */
    .game-list-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        padding: 0 8px;
    }

    /* ===== DATE FILTER ===== */
    .date-filter-box {
        border-radius: 20px;
        padding: 8px 16px;
        border: 1px solid #ddd;
        margin-bottom: 16px;
        text-align: center;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .date-filter-box input {
        border: none;
        background: transparent;
        font-weight: bold;
        color: #555;
        outline: none;
        text-align: center;
        font-size: 15px;
        width: 100%;
    }

    /* ===== GAME BUTTON HEADER ===== */
    .game-button-header {
        background: linear-gradient(180deg, #ffc107 0%, #ff9800 100%);
        border-radius: 8px;
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 6px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        cursor: pointer;
        display: block;
        color: #212529;
        text-decoration: none !important;
    }

    .game-button-header:hover { text-decoration: none !important; color: #212529; }

    .game-name {
        font-weight: 800;
        font-size: 1rem;
        text-transform: uppercase;
        color: #000;
    }

    .game-date {
        font-size: 0.85rem;
        font-weight: 600;
        color: #333;
    }

    /* ===== COLLAPSE DETAILS CARD ===== */
    .game-details-card {
        background: #fff;
        border-radius: 0 0 8px 8px;
        margin-top: -6px;
        margin-bottom: 14px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
        border-top: none;
        overflow: hidden;
    }

    .details-header-row {
        background-color: #343a40;
        color: #fff;
        font-weight: bold;
        padding: 8px 0;
        font-size: 14px;
    }

    .time-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }

    .vertical-divider { border-right: 2px solid #dee2e6; }

    /* ===== ACTION BUTTONS INSIDE CARD ===== */
    .btn-custom {
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 6px 10px;
        width: 92%;
        margin-bottom: 6px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-add     { background-color: #17a2b8; color: white; border: none; }
    .btn-report  { background-color: #28a745; color: white; border: none; }

    .btn-revert {
        border-radius: 20px;
        width: 92%;
        font-size: 0.8rem;
        display: block;
        margin: 4px auto 0;
        padding: 5px 10px;
    }
.custom-popup-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.6);
    align-items: center;
    justify-content: center;
}
.custom-popup-content {
    background-color: #fff;
    width: 90%;
    max-width: 340px;
    padding: 30px 20px;
    border-radius: 12px;
    text-align: center;
    font-family: 'Arial', sans-serif;
}
.popup-title { font-size: 18px; color: #666; margin-bottom: 2px; }
.popup-market { font-size: 20px; font-weight: 500; margin-bottom: 2px; color: #333; }
.popup-date { font-size: 18px; margin-bottom: 5px; color: #333; }
.popup-label { font-size: 18px; margin-bottom: 10px; color: #333; }

.custom-popup-input {
    width: 100%;
    padding: 10px;
    margin: 15px 0;
    border: 2px solid #99ccff; /* Light blue border from your screenshot */
    border-radius: 6px;
    font-size: 22px;
    text-align: center;
    outline: none;
}
.popup-btn-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 15px;
}
.btn-ok {
    background-color: #7d67cf; /* Purple from screenshot */
    color: white; border: none;
    padding: 10px 0; border-radius: 6px;
    font-weight: bold; flex: 1; font-size: 16px;
}
.btn-cancel {
    background-color: #7a7a7a; /* Gray from screenshot */
    color: white; border: none;
    padding: 10px 0; border-radius: 6px;
    font-weight: bold; flex: 1; font-size: 16px;
}
    /* ===== MOBILE TWEAKS ===== */
    @media (max-width: 480px) {
        .game-list-container { padding: 0 2px; }

        .game-button-header { padding: 10px 12px; }

        .game-name  { font-size: 0.9rem; }
        .game-date  { font-size: 0.78rem; }

        .details-header-row { font-size: 13px; }
        .time-text  { font-size: 0.85rem; }

        .btn-custom { font-size: 0.78rem; padding: 5px 8px; width: 96%; }
        .btn-revert { font-size: 0.75rem; width: 96%; }

        /* Equal column padding on very small screens */
        .game-details-card .col-6 { padding-left: 6px; padding-right: 6px; }
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="game-list-container">

            <!-- Date Filter -->
            <form method="get">
                <div class="date-filter-box">
                    <input type="date" name="date"
                           value="<?php echo isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d'); ?>"
                           onchange="this.form.submit()">
                </div>
            </form>

            <?php
            $selectedDate = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
            $formattedDate = date('d/m/Y', strtotime($selectedDate));

            $all_games = [];
            $q1 = mysqli_query($con, "SELECT * FROM `gametime_new` ORDER BY str_to_date(open, '%H:%i')");
            while($r = mysqli_fetch_assoc($q1)) { $all_games[] = $r; }
            $q2 = mysqli_query($con, "SELECT * FROM `gametime_manual` ORDER BY str_to_date(open, '%H:%i')");
            while($r = mysqli_fetch_assoc($q2)) { $all_games[] = $r; }

            $uniqueId = 0;
            foreach($all_games as $game_row){
                
                if(isset($game_row['active']) && $game_row['active'] == 0) {
                    continue; // Skip this game
                }

                // 2. Check if the game is "CLOSED" on this specific day of the week
                if (!isGameOpenOnDate($game_row['days'], $selectedDate)) {
                    continue; // Skip this game (it's closed today)
                }
                $uniqueId++;
                $marketName = $game_row['market'];
                $xc = getOpenCloseTiming($game_row); 
                $res_chk = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$marketName' AND date='$formattedDate'");
                $existing_result = mysqli_fetch_array($res_chk);
                
                $open_res  = (isset($existing_result['open'])  && $existing_result['open']  != "") ? $existing_result['open_panna']."-".$existing_result['open']  : "";
                $close_res = (isset($existing_result['close']) && $existing_result['close'] != "") ? $existing_result['close']."-".$existing_result['close_panna'] : "";
            ?>

               <div class="game-button-header">
                    <div class="game-name"><?php echo $marketName; ?></div>
                    <div class="game-date"><?php echo $formattedDate; ?></div>
                </div>
                
                <!-- 2. Added "show" class to make the details visible by default -->
                <div class="collapse show" id="gameCollapse<?php echo $uniqueId; ?>">
                    <div class="game-details-card">
                        <div class="row m-0 details-header-row text-center">
                            <div class="col-6 border-right border-secondary">Open</div>
                            <div class="col-6">Close</div>
                        </div>
                        
                        <!-- ... the rest of the result/button code inside stays the same ... -->
                        <div class="row m-0 text-center py-3">
                            <!-- OPEN column -->
                            <!--<div class="col-6 vertical-divider">-->
                            <!--    <span class="time-text"><?php echo date('h:i A', strtotime($xc['open'])); ?></span>-->
                            <!--    <?php if($open_res != "") { ?>-->
                            <!--        <div class="btn btn-primary btn-custom mb-2"><?php echo $open_res; ?></div>-->
                            <!--        <button type="button" class="btn btn-report btn-custom"-->
                            <!--                onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Open')">Open Report</button>-->
                            <!--    <?php } else { ?>-->
                            <!--        <button type="button" class="btn btn-add btn-custom open-modal-btn"-->
                            <!--                data-market="<?php echo $marketName; ?>"-->
                            <!--                data-session="open"-->
                            <!--                data-date="<?php echo $selectedDate; ?>">Add Open Result</button>-->
                            <!--        <button type="button" class="btn btn-report btn-custom"-->
                            <!--                onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Open')">Open Report</button>-->
                            <!--    <?php } ?>-->
                            <!--    <button type="button" class="btn btn-danger btn-sm btn-revert open-refund-modal"-->
                            <!--            data-market="<?php echo $marketName; ?>"-->
                            <!--            data-session="open"-->
                            <!--            data-date="<?php echo $selectedDate; ?>">Revert Open Bid</button>-->
                            <!--</div>-->
                
                            <!-- CLOSE column -->
                            <!--<div class="col-6">-->
                            <!--    <span class="time-text"><?php echo date('h:i A', strtotime($xc['close'])); ?></span>-->
                            <!--    <?php if($close_res != "") { ?>-->
                            <!--        <div class="btn btn-primary btn-custom mb-2"><?php echo $close_res; ?></div>-->
                            <!--        <button type="button" class="btn btn-report btn-custom"-->
                            <!--                onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Close')">Close Report</button>-->
                            <!--    <?php } else { ?>-->
                            <!--        <button type="button" class="btn btn-add btn-custom open-modal-btn"-->
                            <!--                data-market="<?php echo $marketName; ?>"-->
                            <!--                data-session="close"-->
                            <!--                data-date="<?php echo $selectedDate; ?>">Add Close Result</button>-->
                            <!--        <button type="button" class="btn btn-report btn-custom"-->
                            <!--                onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Close')">Close Report</button>-->
                            <!--    <?php } ?>-->
                            <!--    <button type="button" class="btn btn-danger btn-sm btn-revert open-refund-modal"-->
                            <!--            data-market="<?php echo $marketName; ?>"-->
                            <!--            data-session="close"-->
                            <!--            data-date="<?php echo $selectedDate; ?>">Revert Close Bid</button>-->
                            <!--</div>-->
                            
                            <!-- OPEN column -->
                            <div class="col-6 vertical-divider">
                                <span class="time-text"><?php echo date('h:i A', strtotime($xc['open'])); ?></span>
                                
                                <?php if($open_res != "") { ?>
                                    <!-- Result is Declared: Show Result and Revert Button -->
                                    <div class="btn btn-primary btn-custom mb-2"><?php echo $open_res; ?></div>
                                    
                                    <button type="button" class="btn btn-report btn-custom"
                                            onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Open')">Open Report</button>
                                    
                                    <!-- Revert button only shows here now -->
                                    <button type="button" class="btn btn-danger btn-sm btn-revert open-refund-modal"
                                            data-market="<?php echo $marketName; ?>"
                                            data-session="open"
                                            data-date="<?php echo $selectedDate; ?>">Revert Open Bid</button>
                                            
                                <?php } else { ?>
                                    <!-- No Result: Show Add Result and Report, but NO Revert -->
                                    <button type="button" class="btn btn-add btn-custom open-modal-btn"
                                            data-market="<?php echo $marketName; ?>"
                                            data-session="open"
                                            data-date="<?php echo $selectedDate; ?>">Add Open Result</button>
                                            
                                    <button type="button" class="btn btn-report btn-custom"
                                            onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Open')">Open Report</button>
                                <?php } ?>
                            </div>
                            
                            <!-- CLOSE column -->
                            <div class="col-6">
                                <span class="time-text"><?php echo date('h:i A', strtotime($xc['close'])); ?></span>
                                
                                <?php if($close_res != "") { ?>
                                    <!-- Result is Declared: Show Result and Revert Button -->
                                    <div class="btn btn-primary btn-custom mb-2"><?php echo $close_res; ?></div>
                                    
                                    <button type="button" class="btn btn-report btn-custom"
                                            onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Close')">Close Report</button>
                                    
                                    <!-- Revert button only shows here now -->
                                    <button type="button" class="btn btn-danger btn-sm btn-revert open-refund-modal"
                                            data-market="<?php echo $marketName; ?>"
                                            data-session="close"
                                            data-date="<?php echo $selectedDate; ?>">Revert Close Bid</button>
                                            
                                <?php } else { ?>
                                    <!-- No Result: Show Add Result and Report, but NO Revert -->
                                    <button type="button" class="btn btn-add btn-custom open-modal-btn"
                                            data-market="<?php echo $marketName; ?>"
                                            data-session="close"
                                            data-date="<?php echo $selectedDate; ?>">Add Close Result</button>
                                            
                                    <button type="button" class="btn btn-report btn-custom"
                                            onclick="showBetReport('<?php echo $marketName; ?>', '<?php echo $selectedDate; ?>', 'Close')">Close Report</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>
        </div>
    </div>
</section>

<!-- Declare Result Modal -->
<!--<div class="modal fade" id="declareResultModal" tabindex="-1" role="dialog">-->
<!--  <div class="modal-dialog modal-dialog-centered" role="document">-->
<!--    <div class="modal-content">-->
<!--      <div class="modal-header bg-warning">-->
<!--        <h5 class="modal-title">Declare Result</h5>-->
<!--        <button type="button" class="close" data-dismiss="modal">&times;</button>-->
<!--      </div>-->
<!--      <div class="modal-body">-->
<!--        <form method="post" action="">-->
<!--            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">-->
<!--            <input type="hidden" name="date"    id="modal_date">-->
<!--            <input type="hidden" name="market"  id="modal_market">-->
<!--            <input type="hidden" name="session" id="modal_session">-->
<!--            <div class="text-center mb-3">-->
<!--                <h4 id="modalGameName" class="text-warning font-weight-bold"></h4>-->
<!--                <span class="badge badge-dark" id="modalSessionDisplay"></span>-->
<!--            </div>-->
<!--            <div class="form-group">-->
<!--                <label>Pana</label>-->
<!--                <select id="modal_pana" name="panna" class="form-control select2bs4" style="width:100%;" required>-->
<!--                    <option value="" selected disabled>Select Pana</option>-->
<!--                    <?php-->
<!--                        $panna_numbers = getPatti();-->
<!--                        foreach($panna_numbers as $pn){ echo '<option value="'.$pn.'">'.$pn.'</option>'; }-->
<!--                    ?>-->
<!--                </select>-->
<!--            </div>-->
<!--            <div class="form-group">-->
<!--                <label>Digit</label>-->
<!--                <input name="digit" type="number" id="modal_digit" class="form-control" readonly />-->
<!--            </div>-->
<!--            <div class="mt-4">-->
<!--                <button name="submit_manual" type="submit" class="btn btn-primary btn-block font-weight-bold">DECLARE RESULT</button>-->
<!--            </div>-->
<!--        </form>-->
<!--      </div>-->
<!--    </div>-->
<!--  </div>-->
<!--</div>-->
<div id="resultPopup" class="custom-popup-overlay">
    <div class="custom-popup-content">
        <div class="popup-title">Post Result</div>
        <div id="disp_market_name" class="popup-market"></div>
        <div id="disp_date" class="popup-date"></div>
        <!--<div class="popup-label">Ank :</div>-->
        <div class="popup-label">Ank : <span id="ank_val_display" style="font-weight:bold;"></span></div>
        <form id="popupForm" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="market"  id="hidden_market">
            <input type="hidden" name="session" id="hidden_session">
            <input type="hidden" name="date"    id="hidden_date">
            <!-- This hidden input sends the calculated Digit (Ank) to your PHP -->
            <input type="hidden" name="digit"   id="hidden_digit">
            
            <input type="number" name="panna" id="main_input" class="custom-popup-input" required autofocus>
            
            <div class="popup-btn-container">
                <button type="submit" name="submit_manual" class="btn-ok">OK</button>
                <button type="button" class="btn-cancel" onclick="closePopup()">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">⚠ Confirm Game Refund</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="date"    id="refund_date">
            <input type="hidden" name="market"  id="refund_market">
            <input type="hidden" name="session" id="refund_session">
            <div class="text-center">
                <p>Are you sure you want to <strong>CANCEL</strong> the game and <strong>REFUND</strong> all bets?</p>
                <h4 id="refundGameName" class="font-weight-bold text-danger"></h4>
                <span class="badge badge-secondary" id="refundSessionDisplay"></span>
                <p class="text-muted mt-2"><small>This action will delete all bets for this session and credit money back to user wallets.</small></p>
            </div>
            <div class="mt-4">
                <button name="cancel_game_refund" type="submit" class="btn btn-danger btn-block font-weight-bold">YES, REFUND ALL</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bet Details Modal -->
<div class="modal fade" id="betDetailsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Bet Records: <span id="reportGameName"></span></h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-striped mb-0">
          <thead class="bg-light">
            <tr>
              <th>User (Mobile)</th>
              <th>Game Type</th>
              <th>Number</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody id="bet_report_body"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function() {
    // 1. Initialize Select2
    if ($.fn.select2) {
        $('.select2bs4').select2({ 
            theme: 'bootstrap4', 
            dropdownParent: $('#declareResultModal') 
        });
    }

    // 2. Handle Declare Result Modal
    $('.open-modal-btn').click(function() {
        var market  = $(this).data('market');
        var session = $(this).data('session');
        var date    = $(this).data('date');
        
        $('#modal_market').val(market);
        $('#modal_session').val(session);
        $('#modal_date').val(date);
        $('#modalGameName').text(market);
        $('#modalSessionDisplay').text(session.toUpperCase());
        
        $('#modal_pana').val('').trigger('change');
        $('#modal_digit').val('');
        $('#declareResultModal').modal('show');
    });

    // 3. Handle Refund Modal
    $('.open-refund-modal').click(function() {
        var market  = $(this).data('market');
        var session = $(this).data('session');
        var date    = $(this).data('date');
        
        $('#refund_market').val(market);
        $('#refund_session').val(session);
        $('#refund_date').val(date);
        $('#refundGameName').text(market);
        $('#refundSessionDisplay').text(session.toUpperCase() + " SESSION");
        $('#refundModal').modal('show');
    });

    // 4. Auto Calculate Digit from Panna
    $('#modal_pana').change(function(){
        var pana = $(this).val();
        if(pana) {
            var dsum = 0;
            for (var i = 0; i < pana.length; i++) {
                if (/[0-9]/.test(pana[i])) dsum += parseInt(pana[i]);
            }
            var dd = dsum.toString();
            $('#modal_digit').val(dd.charAt(dd.length-1));
        }
    });
});

function showBetReport(market, date, session) {
    var url = "report.php?market=" + encodeURIComponent(market) + 
              "&date=" + date + 
              "&session=" + session;
    window.location.href = url;
}
</script>
<script>
$(document).ready(function() {
    $('.open-modal-btn').click(function() {
        var market  = $(this).data('market');
        var session = $(this).data('session');
        var dateVal = $(this).data('date'); 

        // Format date for display: YYYY-MM-DD to DD-MM-YYYY
        var d = new Date(dateVal);
        var displayDate = ("0" + d.getDate()).slice(-2) + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + d.getFullYear();

        $('#disp_market_name').text(market);
        $('#disp_date').text(displayDate);
        $('#hidden_market').val(market);
        $('#hidden_session').val(session);
        $('#hidden_date').val(dateVal);
        
        $('#main_input').val('');
        $('#resultPopup').css('display', 'flex');
        $('#main_input').focus();
    });

    // This part does the "Ank" calculation before submitting
    $('#popupForm').submit(function() {
        var val = $('#main_input').val();
        if(val.length > 0) {
            // Calculate sum of digits for the Ank (e.g. 123 = 6)
            var sum = 0;
            for (var i = 0; i < val.length; i++) {
                sum += parseInt(val[i]);
            }
            var ank = sum.toString().slice(-1); // Take last digit of sum
            $('#hidden_digit').val(ank); 
        }
        return true; 
    });
});

function closePopup() {
    $('#resultPopup').hide();
}

// $(document).ready(function() {
//     $('.open-modal-btn').click(function() {
//         var market  = $(this).data('market');
//         var session = $(this).data('session');
//         var dateVal = $(this).data('date'); 

//         var d = new Date(dateVal);
//         var displayDate = ("0" + d.getDate()).slice(-2) + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + d.getFullYear();

//         $('#disp_market_name').text(market);
//         $('#disp_date').text(displayDate);
//         $('#hidden_market').val(market);
//         $('#hidden_session').val(session);
//         $('#hidden_date').val(dateVal);
        
//         $('#main_input').val('');
//         $('#ank_val_display').text(''); // Clear previous Ank
//         $('#resultPopup').css('display', 'flex');
//         $('#main_input').focus();
//     });

//     // REAL-TIME ANK CALCULATION
//     $('#main_input').on('input', function() {
//         var val = $(this).val();
//         var sum = 0;
        
//          if (val.length > 3) {
//         $('#ank_val_display').text('Invalid').css('color', 'red');
//         $('#hidden_digit').val('');
//         return;
//     }
    
//         if(val.length > 0) {
//             for (var i = 0; i < val.length; i++) {
//                 sum += parseInt(val[i]);
//             }
//             // Check if sum is between 0 and 9
//             if (sum > 9 || sum < 0) {
//                 $('#ank_val_display').text('Invalid').css('color', 'red');
//                 $('#hidden_digit').val(''); 
//             } else {
//                 $('#ank_val_display').text(sum).css('color', 'green');
//                 $('#hidden_digit').val(sum);
//             }
//         } else {
//             $('#ank_val_display').text('');
//         }
//     });

//     $('#popupForm').submit(function() {
//         if($('#hidden_digit').val() === "" && $('#main_input').val() !== "") {
//             alert("Invalid Ank value. Please enter a valid Panna.");
//             return false;
//         }
//         return true; 
//     });
// });
</script>
<script>
$(document).ready(function() {
    // Comprehensive list of valid Pannas from the images (SP, DP, TP)
    const validPannas = [
        // 1
        "128", "137", "146", "236", "245", "489", "470", "380", "119", "100", "227", "245", "335", "344", "399", "588", "669", "777", "444", "111",
        // 2
        "129", "138", "147", "156", "237", "246", "345", "390", "480", "570", "589", "679", "110", "200", "228", "255", "336", "345", "444", "499", "660", "778", "888", "222",
        // 3
        "120", "139", "148", "157", "166", "238", "247", "256", "346", "490", "580", "670", "166", "229", "300", "337", "355", "445", "599", "779", "788", "333", "999", "111",
        // 4
        "130", "149", "158", "167", "176", "239", "248", "257", "347", "356", "400", "590", "680", "112", "220", "266", "338", "446", "455", "699", "770", "880", "444", "888",
        // 5
        "140", "159", "168", "177", "122", "230", "249", "258", "267", "348", "357", "456", "113", "122", "177", "221", "339", "366", "447", "500", "799", "889", "555",
        // 6
        "123", "150", "169", "178", "114", "240", "259", "268", "277", "349", "358", "367", "114", "222", "277", "330", "448", "466", "556", "600", "880", "899", "666", "222",
        // 7
        "124", "160", "179", "115", "133", "250", "269", "278", "340", "359", "368", "458", "115", "133", "188", "223", "377", "449", "557", "566", "700", "990", "777", "999",
        // 8
        "125", "134", "170", "189", "260", "279", "287", "350", "369", "378", "459", "468", "116", "224", "233", "288", "332", "440", "477", "558", "800", "990", "888", "666",
        // 9
        "126", "135", "180", "199", "270", "289", "234", "360", "379", "450", "469", "478", "117", "144", "199", "225", "333", "388", "441", "559", "577", "667", "900", "999", "333",
        // 0
        "127", "136", "145", "190", "235", "280", "370", "460", "569", "578", "118", "226", "244", "299", "334", "488", "550", "668", "677", "000"
    ];

    $('.open-modal-btn').click(function() {
        var market  = $(this).data('market');
        var session = $(this).data('session');
        var dateVal = $(this).data('date'); 

        var d = new Date(dateVal);
        var displayDate = ("0" + d.getDate()).slice(-2) + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + d.getFullYear();

        $('#disp_market_name').text(market);
        $('#disp_date').text(displayDate);
        $('#hidden_market').val(market);
        $('#hidden_session').val(session);
        $('#hidden_date').val(dateVal);
        
        $('#main_input').val('');
        $('#ank_val_display').text(''); 
        $('#resultPopup').css('display', 'flex');
        $('#main_input').focus();
    });

    // REAL-TIME VALIDATION AND ANK CALCULATION
    $('#main_input').on('input', function() {
        var val = $(this).val();

        // Only process if 3 digits are entered
        if(val.length === 3) {

            // 1. Check if it is a valid Panna from the list
            if (validPannas.includes(val)) {

                // 2. Calculate sum of digits
                var sum = 0;
                for (var i = 0; i < val.length; i++) {
                    sum += parseInt(val[i]);
                }

                // 3. Get the Unit Place (Last digit of the sum)
                // Example: 517 -> 13 -> Ank is 3
                var ank = sum % 10; 
                
                $('#ank_val_display').text(ank).css('color', 'green');
                $('#hidden_digit').val(ank);
                $('.btn-ok').prop('disabled', false).css('opacity', '1');
            } else {
                // Not in the valid list
                $('#ank_val_display').text('Invalid Panna').css('color', 'red');
                $('#hidden_digit').val('');
                $('.btn-ok').prop('disabled', true).css('opacity', '0.5');
            }
        } else if (val.length > 3) {
            $('#ank_val_display').text('Max 3 Digits').css('color', 'red');
            $('#hidden_digit').val('');
            $('.btn-ok').prop('disabled', true).css('opacity', '0.5');
        } else {
            // Clearing or incomplete input
            $('#ank_val_display').text('Enter 3 Digits').css('color', 'orange');
            $('#hidden_digit').val('');
            $('.btn-ok').prop('disabled', true).css('opacity', '0.5');
        }
    });

    $('#popupForm').submit(function() {
        var val = $('#main_input').val();
        var ank = $('#hidden_digit').val();

        if (val === "" || ank === "") {
            alert("Please enter a valid 3-digit Panna from the chart.");
            return false;
        }
        return true; 
    });
});

function closePopup() {
    $('#resultPopup').hide();
}
</script>
