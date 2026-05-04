<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include "con.php";
date_default_timezone_set('Asia/Kolkata');

// Step 1: Select market dynamically
$currentTime = date('H:i');
// $query = "
//     SELECT * FROM `gametime_manual`
//     WHERE `active` = 1
//     AND (
//         STR_TO_DATE(open, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i') OR 
//         STR_TO_DATE(close, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i')
//     )
//     ORDER BY STR_TO_DATE(open, '%H:%i') DESC
// ";
$query = "
    SELECT * FROM `gametime_manual`
    WHERE `active` = 1
    ORDER BY STR_TO_DATE(open, '%H:%i') DESC
";


$result = mysqli_query($con, $query);

// Step 2: Fetch the records
$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[] = $row;
}

// Step 3: Randomly select one record
$selectedRecord = null;
if (count($records) > 0) {
    shuffle($records);  // Shuffle the records to randomize order
    $selectedRecord = $records[0];  // Select the first record from shuffled array
}

// Step 4: Output the selected record (if any)
if ($selectedRecord) {
    echo json_encode($selectedRecord);

    // Get market name from the selected record
    $market_name = $selectedRecord['market'];
} else {
    echo json_encode(['success' => '0', 'msg' => 'No valid market found.']);
}

// Step 2: Call HMAC API
$apiKey    = "QORDHM3PLG21";
$apiSecret = "65f1cb48-0fde-4568-97a6-349c1a499f45";
$baseUrl   = "https://dpbossresultapi.com";
$path      = "/api/data";
$ts        = gmdate("Y-m-d\TH:i:s\Z");
$msg       = $apiKey . "GET" . $path . $ts;
$sig       = hash_hmac('sha256', $msg, $apiSecret);

$ch = curl_init("$baseUrl$path");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $apiKey",
        "X-Timestamp: $ts",
        "X-Signature: $sig",
    ],
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo '<pre>';
print_r($data['data']['all_result']);
echo '</pre>';

if (empty($data['success'])) {
    echo "API Error or invalid response.";
    exit;
}

// Step 3: Find matching market
$sel = null;
$today = date('d-m-Y'); // Format like 25-07-2025
foreach ($data['data']['all_result'] as $entry) {
    if (
        strcasecmp($entry['name'], $market_name) === 0 &&
        isset($entry['updated_date']) &&
        $entry['updated_date'] === $today
    ) {
        $sel = $entry;
        break;
    }
}
if (!$sel) {
    echo "Market '$market_name' not found or not updated today.";
    exit;
}


// Step 4: Parse result - supports both XYZ-AB-CDE and XYZ-AB or XYZ
$parts = explode('-', $sel['result']);

$opanna = isset($parts[0]) ? $parts[0] : null;
$jodi   = isset($parts[1]) ? $parts[1] : null;
$cpanna = isset($parts[2]) ? $parts[2] : null;

// Validate opanna and cpanna length if present
if (!preg_match('/^\d{3}$/', $opanna)) {
    echo "Invalid opanna format: " . htmlspecialchars($opanna);
    exit;
}
if ($cpanna !== null && !preg_match('/^\d{3}$/', $cpanna)) {
    echo "Invalid cpanna format: " . htmlspecialchars($cpanna);
    exit;
}

$open  = array_sum(str_split($opanna)) % 10;
$close = $cpanna ? (array_sum(str_split($cpanna)) % 10) : null;
$market = $sel['name'];

// Step 5: Output
echo "<h3>Market Result:</h3>";
echo "Market: {$market}<br>";
echo "Result: {$sel['result']}<br>";
echo "opanna: $opanna<br>";
echo "jodi: " . ($jodi ?? 'N/A') . "<br>";
echo "cpanna: " . ($cpanna ?? 'N/A') . "<br>";
echo "open: $open<br>";
echo "close: " . ($close !== null ? $close : 'N/A') . "<br>";


// Step 6: Session & result validation logic
$session = 'open';
$stamp   = time();
$time    = date("H:i", $stamp);
$day     = strtoupper(date("l", $stamp));
$date  = date('d/m/Y');

echo "<br>Session: $session<br>";
echo "Time: $time | Day: $day | Date: $date<br>";

if ($session === 'open') {
    if ($open === "" && $opanna === "") {
        echo "not valid result";
        exit;
    }
} else {
    $chk = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$sel[name]' AND date='$today'");
    $r = mysqli_fetch_assoc($chk);
    $open   = $r['open'];
    $opanna = $r['open_panna'];
    if ($open === "" && $opanna === "") {
        echo "not valid result";
        exit;
    }
}
    



    // Check if a record already exists for the given market and date
$chk_if_query = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$market' AND date='$date'");

if (mysqli_num_rows($chk_if_query) > 0) {
    // Existing record found
    $chk_if_updated = mysqli_fetch_assoc($chk_if_query);
    $sn = $chk_if_updated['sn'];
    $existing_close = $chk_if_updated['close'];
    $existing_cpanna = $chk_if_updated['close_panna'];

    // Check if we need to update and notify
    $shouldUpdate = false;
    $shouldNotify = false;

    // Check if existing values are empty/null
 // Check if existing values are empty/null and new values provided
$existing_open = $chk_if_updated['open'];
$existing_opanna = $chk_if_updated['open_panna'];

if (
    (empty($existing_close) && $close != "") || 
    (empty($existing_cpanna) && $cpanna != "") ||
    (empty($existing_open) && $open != "") ||
    (empty($existing_opanna) && $opanna != "")
) {
    $shouldUpdate = true;
    $shouldNotify = true;
} 
elseif (
    ($close != "" && $close != $existing_close) ||
    ($cpanna != "" && $cpanna != $existing_cpanna) ||
    ($open != "" && $open != $existing_open) ||
    ($opanna != "" && $opanna != $existing_opanna)
) {
    $shouldUpdate = true;
    $shouldNotify = true;
}


if ($shouldUpdate) {
    $updateQuery = mysqli_query($con, "UPDATE manual_market_results SET close='$close', close_panna='$cpanna', status='1' WHERE sn='$sn'");
    
    if ($updateQuery) {
        // Update gametime_manual
             echo "UPDATE";
        mysqli_query($con, "UPDATE gametime_manual SET close_status='1' WHERE market='$market'");

        // Send notification only if data changed
        if ($shouldNotify) {
            // Fix: Use DB values if in-memory values are empty
            $final_opanna = !empty($opanna) ? $opanna : $chk_if_updated['open_panna'];
            $final_open   = !empty($open)   ? $open   : $chk_if_updated['open'];

            $result = formatResult($final_opanna, $final_open, $close, $cpanna);
            $body = str_replace("_", " ", $market) . ' result update';
            sendNotification($body, $result, "result");
        }
    }
}

} else {
    // New record (no existing entry)
    $close_status = ($close != "") ? '1' : '0';
     echo "INSERT";
    // Insert new record
    $insertQuery = mysqli_query($con, "INSERT INTO manual_market_results (market, date, open_panna, open, close, close_panna, status, created_at) 
                                     VALUES ('$market', '$date', '$opanna', '$open', '$close', '$cpanna', '1', '$stamp')");
    
    if ($insertQuery) {
        // Update gametime_manual
        mysqli_query($con, "UPDATE gametime_manual SET open_status='1', close_status='$close_status' WHERE market='$market'");

        // Send notification only if new data was inserted
        if ($open != "" || $opanna != "") {
            $result = formatResult($opanna, $open, $close, $cpanna);
            $body = str_replace("_", " ", $market) . ' result insert';
            sendNotification($body, $result, "result");
        }
    }
}

    
    /////////////////////////
    //// CREATING BATCH /////
    /////////////////////////
    
    $batch_id = md5($stamp.$market.rand().$open.$close.$date.$day.$time);
    
    $batch_result = $opanna.'-'.$open.$close.'-'.$cpanna;
        
    mysqli_query($con, "INSERT INTO `manual_batch`( `market`, `result`, `revert`, `created_at`, `batch_id`,`date`) VALUES ('$market','$batch_result','0','$stamp','$batch_id','$date')");
    
    $xvm = mysqli_query($con, "select * from rate where sn='1'");
    $xv = mysqli_fetch_array($xvm);
    
       if($open !== ""){
                    echo "single";

        $mrk = str_replace(" ","_",$market.' OPEN');
    
        $xx = mysqli_query($con, "select * from games where bazar='$mrk' AND game='single' AND date='$date' AND number='$open' AND status='0' AND is_loss='0'");
        
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            $remrk = $x['game']." ".$x['bazar']." Winning";
        
            mysqli_query($con, "update games set status='1' where sn='$sn'");
            
          
            mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
            
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
            
                            
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            
        }
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$mrk' AND game='single' AND date='$date' AND number!='$open' AND is_loss='0'");
        
    
    }
    
    if($opanna !== ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup'AND game_type !='bulksp' AND game_type !='bulkdp')");
        $rowCount = mysqli_num_rows($xx);

// if (!$xx) {
//     die('Query Error: ' . mysqli_error($con));
// }

// // Fetch results
// $resultArray = [];
// while ($row = mysqli_fetch_assoc($xx)) {
//     $resultArray[] = $row;
// }

//  var_dump($resultArray, "see",$opanna);


// if($rowCount != 0){
                // var_dump("hee",$xv[$x['game']]);die();

        while($x = mysqli_fetch_array($xx))
        {
            
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];

            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
             
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
       
        $result=mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup' AND game_type !='bulksp'  AND game_type !='bulkdp' ) AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
        
        if (!$result) {
    // die('Query Error: ' . mysqli_error($con));
}

// Check how many rows were affected
$affectedRows = mysqli_affected_rows($con);

// if ($affectedRows > 0) {
//     echo "Query executed successfully. Rows affected: $affectedRows.";
// } else {
//     echo "Query executed successfully, but no rows were affected.";
// }
    //  }
    }


        if($opanna !== ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana'  OR game_type ='panelgroup' OR game_type ='bulksp' OR game_type ='bulkdp' )");
          $rowCount = mysqli_num_rows($xx);
         // var_Dump($rowCount);die();
// if($rowCount > 0){
                // var_dump("bye",$xv[$x['game_type']]);die();

        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game_type']];
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
             
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
        
       $result= mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='panelgroup' OR game_type ='bulksp'  OR game_type ='bulkdp' )AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
        
                if (!$result) {
    // die('Query Error: ' . mysqli_error($con));
}

// Check how many rows were affected
$affectedRows = mysqli_affected_rows($con);
    // }
    }
















    
    if($close !== ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND game='single' AND date='$date' AND number='$close' AND status='0' AND is_loss='0'");
                            
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
                mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            }
        } 
        
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND game='single' AND date='$date' AND number!='$close' AND status='0' AND is_loss='0'");
    
    }
    
    if($cpanna !== ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup'  AND game_type !='bulksp'  AND game_type !='bulkdp') AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
    if($xx){                
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0' AND is_loss='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
        $result=mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana'  AND game_type !='panelgroup' AND game_type !='bulksp' AND game_type !='bulkdp') AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
                if (!$result) {
    // die('Query Error: ' . mysqli_error($con));
}

// Check how many rows were affected
$affectedRows = mysqli_affected_rows($con);
        
    
    }
}


     if($cpanna !== ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='panelgroup' OR game_type ='bulksp' OR game_type ='bulkdp' ) AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
       if($xx){                   
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game_type']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0' AND is_loss='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game_type']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
       $result= mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='panelgroup' OR game_type ='bulksp' OR game_type ='bulkdp'  )AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
                if (!$result) {
    // die('Query Error: ' . mysqli_error($con));
}

// Check how many rows were affected
$affectedRows = mysqli_affected_rows($con);
    
    }
}
    
     if($open !== "" && $close !== ""){
        
        
        $bazar = str_replace(" ","_",$market);
        $bazar2 = str_replace(" ","_",$market.' OPEN');
        $bazar3 = str_replace(" ","_",$market.' CLOSE');
        
        $full_num = $open.$close;
        
        
        $xx = mysqli_query($con, "select * from games where ( bazar='$bazar' OR bazar='$bazar2' OR bazar='$bazar3' ) AND game='jodi' AND date='$date' AND number='$full_num' AND status='0' AND is_loss='0'");
       
        
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game_type']];
        
        
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                             
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND game='jodi' AND date='$date' AND number!='$full_num' AND status='0' AND is_loss='0'");
    
    } 
    
    if($opanna !== "" && $cpanna !== ""){
       //  var_dump("tata");die();
        $full_num = $opanna.'-'.$cpanna;
         
        $bazar = str_replace(" ","_",$market);
                        
      $xx = mysqli_query($con, "select * from games where bazar like '%$bazar%' AND game='fullsangam' AND date='$date' AND number='$full_num' AND status='0' AND is_loss='0'");

//        $xy = mysqli_query($con, "select * from games where bazar like '%MAHARANI%' AND game='fullsangam' AND date='07/08/2023' AND number='168-146' AND status='0' AND is_loss='0'");

        
//       $rowCount = mysqli_num_rows($xx);
//           $rowCount1 = mysqli_num_rows($xy);


// var_dump($rowCount,"you",$full_num, $bazar,$date,$rowCount1);die();  
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                             
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        mysqli_query($con, "update games set is_loss='1' where bazar like '%$bazar%' AND game='fullsangam' AND date='$date' AND number!='$full_num' AND status='0' AND is_loss='0'");
         
    }
    
    
    
    if($opanna !== "" && $cpanna !== "" && $open !== "" && $close !== ""){
        

        $num1 = $opanna.'-'.$close;
        $num2 = $open.'-'.$cpanna;
        
$xx = mysqli_query($con, "SELECT * FROM games WHERE bazar LIKE '%$bazar%' AND game='halfsangam' AND date='$date' AND (number='$num1' OR number='$num2') AND status='0' AND is_loss='0'");

//  var_dump($resultArray, "see", $num1, $num2);
// die();
       // echo "select * from games where bazar like '%$bazar%' AND game='halfsangam' AND date='$date' AND ( number='$num1' or number='$num2') AND status='0' AND is_loss='0'";
                            
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                
                             
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        mysqli_query($con, "update games set is_loss='1' where bazar like '%$bazar%' AND game='halfsangam' AND date='$date' AND ( number!='$num1' or number !='$num2') AND status='0' AND is_loss='0'");
        
    }
     
    $result = "";
    
    if($opanna != ""){
        $result = $opanna.'-';
    } else {
        $result = "***-";
    }    
    
    if($open != ""){
        $result .= $open;
    } else {
        $result .= "*";
    }   
    
    if($close != ""){
        $result .= $close.'-';
    } else {
        $result .= "*".'-';
    }   
    
    if($cpanna != ""){
        $result .= $cpanna;
    } else {
        $result .= "***";
    } 
    
    $body = str_replace("_"," ",$bazar);
    $body = str_replace("OPEN","",$body);
    $body = str_replace("CLOSE","",$body);
    
    $body  = $body.' result';
    

  
// sendNotification($body,$result,"result");
  
    unset($open);
    unset($opanna);
    unset($close);
    unset($cpanna);
    
 // echo "<script>window.location.href = 'declare-result.php'</script>";


function sendNotification($title, $body, $topic) {
    $projectId = 'dmbossdemo'; 

    // Get the access token
    $accessToken = getAccessTokens();

    if ($accessToken) {
        $notification = array(
            'title' => $title,
            'body'  => $body,
        );

        $message = array(
            'notification' => $notification,
            'topic' => $topic, // Use the provided topic
        );

        $fields = array(
            'message' => $message,
        );

        $headers = array(
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $result = curl_exec($ch);

       
    } else {
        echo "Failed to retrieve access token";
    }
}



function getAccessTokens() {
    $keyFilePath = '../demodmboss.json'; // Path to your service account JSON file
    
    
    $json = file_get_contents($keyFilePath);
    $key = json_decode($json, true);

    $jwt = createJWTs($key);

    $url = 'https://oauth2.googleapis.com/token';
    $postData = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    } else {
        echo "Error fetching access token: " . $response['error'];
        return null;
    }
}

function createJWTs($key) {
    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];

    $payload = [
        'iss' => $key['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600, // 1 hour expiration
        'iat' => time()
    ];

    $headerEncoded = base64url_encode(json_encode($header));
    $payloadEncoded = base64url_encode(json_encode($payload));
    $signatureInput = $headerEncoded . '.' . $payloadEncoded;

    $privateKey = $key['private_key'];
    $privateKey = openssl_pkey_get_private($privateKey);
    
    openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
    $signatureEncoded = base64url_encode($signature);

    return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Helper function to format the result
// function formatResult($opanna, $open, $close, $cpanna) {
//     $result = ($opanna != "") ? $opanna . '-' : "***-";
//     $result .= ($open != "") ? $open : "*";
//     $result .= ($close != "") ? $close . '-' : "*" . '-';
//     $result .= ($cpanna != "") ? $cpanna : "***";
//     return $result;
// }
function formatResult($opanna, $open, $close, $cpanna) {
    $result = ($opanna !== null && $opanna !== '') ? $opanna . '-' : '***-';
    $result .= ($open !== null && $open !== '') ? $open : '*';
    $result .= ($close !== null && $close !== '') ? $close . '-' : '*' . '-';
    $result .= ($cpanna !== null && $cpanna !== '') ? $cpanna : '***';
    return $result;
}

?>



