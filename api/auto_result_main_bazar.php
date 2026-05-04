<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include your database connection file
include "con.php";
date_default_timezone_set('Asia/Kolkata');
// Get the current time in 24-hour format
$currentTime = date('H:i');
// Prepare the SQL query to select markets where the open time is greater than the current time
// $query = "
//     SELECT * FROM `gametime_manual`
//     WHERE `active` = 1
//     AND STR_TO_DATE(open, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i')
//     ORDER BY STR_TO_DATE(open, '%H:%i') DESC
//     LIMIT 1 OFFSET 4
// ";

// // Execute the query
// $result = mysqli_query($con, $query);

// // Initialize an array to store the closest market
// $closestMarket = [];

// // Check if the query executed successfully
// if ($result === false) {
//     // Handle query error
//     $error = mysqli_error($con);
//     $closestMarket = [
//         'success' => '0',
//         'msg' => 'Query failed: ' . htmlspecialchars($error)
//     ];
// } else {
//     // Process the result
//     if ($row = mysqli_fetch_assoc($result)) {
//         $closestMarket = [
//             'success' => '1',
//             'market' => $row['market'],
//             'open' => $row['open'],
//             'close' => $row['close'],
//             'days' => $row['days'],
//             'sort_no' => $row['sort_no'],
//             'active' => $row['active'],
//             'type' => $row['type'],
//             'currentTime' => $currentTime

//         ];
//     } else {
//         // No markets found
//         $closestMarket = [
//             'success' => '0',
//             'msg' => 'No market found with an open time greater than the current time.'
//         ];
//     }
// }

$currentTime = date('H:i');

// Step 1: Select records
// $query = "
//     SELECT * FROM `gametime_manual`
//     WHERE `active` = 1
//     AND (
//         STR_TO_DATE(open, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i') OR 
//         STR_TO_DATE(close, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i')
//     )
//     AND (open_status = 0 OR close_status = 0)
//     ORDER BY STR_TO_DATE(open, '%H:%i') DESC
// ";


$query = "
    SELECT * FROM `gametime_manual`
    WHERE `active` = 1
    AND (
        STR_TO_DATE(open, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i') OR 
        STR_TO_DATE(close, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i')
    )
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

  $market_name="MAIN BAZAR";

// echo json_encode($closestMarket);
// Step 1: Fetch the API_token from the `tr_key` table where id = 1
$id = 1;  // Assuming the `id` of the record storing the token is 1
$selectQuery = "SELECT tr_key FROM tr_key WHERE id = ?";
$stmt = $con->prepare($selectQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $API_token = $row['tr_key'];  // Fetch the API_token
    echo "Using API Token: " . htmlspecialchars($API_token) . "<br>";

    // Define the API URL
    $url = "https://matkawebhook.matka-api.online/market-data";

    // Set up the required parameters
    $username = "9888195353";
    // $market_name = "MILAN MORNING";
    $date = date('Y-m-d');  // Use today's date

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    // Set POST fields (parameters)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $username,
        'API_token' => $API_token,
        'markte_name' => $market_name,  // Correct parameter name
        'date' => $date
    ]));

    // Execute the cURL request and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    } else {
        // Decode the JSON response
        $result = json_decode($response, true);
        // Check if the status is true
        // if ($result['status'] === true) {
                if (!empty($result['today_result'])) {
            // Success, process the result
            echo "<h3>Market Data:</h3>";
            echo "<p>Market Name: " . htmlspecialchars($result['today_result'][0]['market_name']) . "</p>";
            echo "<p>Aankdo Date: " . htmlspecialchars($result['today_result'][0]['aankdo_date']) . "</p>";
            echo "<p>Aankdo Open: " . htmlspecialchars($result['today_result'][0]['aankdo_open']) . "</p>";
            echo "<p>Aankdo Close: " . htmlspecialchars($result['today_result'][0]['aankdo_close']) . "</p>";
            echo "<p>Figure Open: " . htmlspecialchars($result['today_result'][0]['figure_open']) . "</p>";
            echo "<p>Figure Close: " . htmlspecialchars($result['today_result'][0]['figure_close']) . "</p>";
            echo "<p>Jodi: " . htmlspecialchars($result['today_result'][0]['jodi']) . "</p>";
            echo "<p>refresh_token: " . htmlspecialchars($result['refresh_token']) . "</p>";

            $market = $result['today_result'][0]['market_name'];
            $date = $result['today_result'][0]['aankdo_date'];
            $opanna = $result['today_result'][0]['aankdo_open'];
            $cpanna = $result['today_result'][0]['aankdo_close'];
            $open = $result['today_result'][0]['figure_open'];
            $close = $result['today_result'][0]['figure_close'];
            $jodi = $result['today_result'][0]['jodi'];

            $updateQuery = "UPDATE tr_key SET tr_key = ? WHERE id = ?";
            $updateStmt = $con->prepare($updateQuery);
            $updateStmt->bind_param("si", $result['refresh_token'], $id);

            if ($updateStmt->execute()) {
                echo "API Token updated to: " . htmlspecialchars($result['refresh_token']);
            } else {
                echo "Error updating API Token: " . $con->error;
            }
            $updateStmt->close();

        } else {
            // Error, display message
            echo "<p>Errorss: " . htmlspecialchars($result['message']) . "</p>";
                         exit();

        }
    }

    // Close the cURL session
    curl_close($ch);

} else {
    echo "No API token found in the database!";
}

// Close the statement
$stmt->close();

$session = 'open';
echo "<br>Session status: " . htmlspecialchars($session) . "<br>";
$stamp = time(); // Assign a valid timestamp
$time = date("H:i", $stamp);
$day = strtoupper(date("l", $stamp));
$date = date('d/m/Y');  // Get today's date in 'day/month/year' format

// Print values with line breaks
echo "Timestamp: " . htmlspecialchars($stamp) . "<br>";
echo "Time: " . htmlspecialchars($time) . "<br>";
echo "Day: " . htmlspecialchars($day) . "<br>";
echo "Date: " . htmlspecialchars($date) . "<br>";
echo "Open: " . htmlspecialchars($open) . "<br>";


if ($session == 'open') {
    echo "3";

        if($open == "" && $opanna == ""){
            //  echo "<script>window.location.href = 'declare-result.php?error=Not%20a%20valid%20result'</script>";
                         echo "not valid result";

             exit();
        }
        
    } else {
        
        $chk_if_query = mysqli_query($con, "select * from manual_market_results where market='$market' AND date='$date'");
        $chk_if_updated = mysqli_fetch_array($chk_if_query);
        echo "2";
        $open = $chk_if_updated['open'];
        $opanna = $chk_if_updated['open_panna'];
     
         
        if($open == "" && $opanna == ""){
            //  echo "<script>window.location.href = 'declare-result.php?error=Not%20a%20valid%20result'</script>";
             echo "not valid result";
             exit();
        }
    }
    


//     $chk_if_query = mysqli_query($con, "select sn from manual_market_results where market='$market' AND date='$date'");
//     if(mysqli_num_rows($chk_if_query) > 0){
//         echo "1";
//                 echo $market;
//         echo $date;

//         $chk_if_updated = mysqli_fetch_array($chk_if_query);
//         $sn = $chk_if_updated['sn'];
//         mysqli_query($con, "update manual_market_results set close='$close', close_panna='$cpanna', status='1' where sn='$sn'");
//                         $close_status = ($close != "") ? '1' : '0';

//         mysqli_query($con, "update gametime_manual set `close_status` ='1' where market='$market'");

//     } else {
//                 $close_status = ($close != "") ? '1' : '0';

// // $query = "
// //     INSERT INTO `manual_market_results`(`market`, `date`, `open_panna`, `open`, `close`, `close_panna`, `status`, `created_at`) 
// //     VALUES ('$market', '$date', '$opanna', '$open', '$close', '$cpanna', '1', $stamp)";

//      mysqli_query($con, "update gametime_manual set  `open_status`='1',`close_status` ='$close_status' where market='$market'");

//       mysqli_query($con, "INSERT INTO `manual_market_results`(`market`, `date`, `open_panna`, `open`, `close`, `close_panna`,`status`, `created_at`) VALUES ('$market','$date','$opanna','$open','$close','$cpanna','1','$stamp')");
     
//                         echo "tt";

//     }
    
    // Check if a record already exists for the given market and date
$chk_if_query = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$market' AND date='$date'");
if (mysqli_num_rows($chk_if_query) > 0) {
    echo "1";
    echo $market;
    echo $date;

    // Fetch the existing record
    $chk_if_updated = mysqli_fetch_array($chk_if_query);
    $sn = $chk_if_updated['sn'];

    // Prepare the existing values for comparison
    $existing_close = $chk_if_updated['close'];
    $existing_cpanna = $chk_if_updated['close_panna'];

    // Only update if new values are null or blank
    if (is_null($existing_close) || $existing_close === '' || 
        is_null($existing_cpanna) || $existing_cpanna === '') {
        echo "update";
        mysqli_query($con, "UPDATE manual_market_results SET close='$close', close_panna='$cpanna', status='1' WHERE sn='$sn'");
    }

    // Update gametime_manual regardless
    mysqli_query($con, "UPDATE gametime_manual SET close_status='1' WHERE market='$market'");

} else {
    $close_status = ($close != "") ? '1' : '0';

    // Update gametime_manual for new entries
    mysqli_query($con, "UPDATE gametime_manual SET open_status='1', close_status='$close_status' WHERE market='$market'");

    // Insert new record
    mysqli_query($con, "INSERT INTO manual_market_results (market, date, open_panna, open, close, close_panna, status, created_at) VALUES ('$market', '$date', '$opanna', '$open', '$close', '$cpanna', '1', '$stamp')");

    echo "tt";
}

    
    /////////////////////////
    //// CREATING BATCH /////
    /////////////////////////
    
    $batch_id = md5($stamp.$market.rand().$open.$close.$date.$day.$time);
    
    $batch_result = $opanna.'-'.$open.$close.'-'.$cpanna;
        
    mysqli_query($con, "INSERT INTO `manual_batch`( `market`, `result`, `revert`, `created_at`, `batch_id`,`date`) VALUES ('$market','$batch_result','0','$stamp','$batch_id','$date')");
    
    $xvm = mysqli_query($con, "select * from rate where sn='1'");
    $xv = mysqli_fetch_array($xvm);
    
      if($open != ""){
        
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
    
    if($opanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup' AND game_type !='bulksp' AND game_type !='bulkdp')");
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


        if($opanna != ""){
        
        
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
















    
    if($close != ""){
        
        
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
    
    if($cpanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup' AND game_type !='bulksp'  AND game_type !='bulkdp') AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
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
        
        $result=mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='panelgroup' AND game_type !='bulksp' AND game_type !='bulkdp') AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
                if (!$result) {
    // die('Query Error: ' . mysqli_error($con));
}

// Check how many rows were affected
$affectedRows = mysqli_affected_rows($con);
        
    
    }
}


     if($cpanna != ""){
        
        
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
    
     if($open != "" && $close != ""){
        
        
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
    
    if($opanna != "" && $cpanna != ""){
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
    
    
    
    if($opanna != "" && $cpanna != "" && $open != "" && $close != ""){
        

        $num1 = $opanna.'-'.$close;
        $num2 = $open.'-'.$cpanna;
        
$xx = mysqli_query($con, "SELECT * FROM games WHERE bazar LIKE '%$bazar%' AND game='halfsangam' AND date='$date' AND (number='$num1' OR number='$num2') AND status='0' AND is_loss='0'");

// var_dump($resultArray, "see", $num1, $num2);
// die();

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
    

  
sendNotification($body,$result,"result");
  
    unset($open);
    unset($opanna);
    unset($close);
    unset($cpanna);
    
 // echo "<script>window.location.href = 'declare-result.php'</script>";


function sendNotification($title, $body, $topic) {
    $projectId = 'dmboos'; 

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

        // if (curl_errno($ch)) {
        //     $error = curl_error($ch);
        //     echo "cURL Error: $error";
        // } else {
        //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //     $response = json_decode($result, true);

        //     if ($httpCode == 200) {
        //         echo "Notification sent successfully";
        //     } else {
        //         echo "Firebase Error: " . $response['error']['message'];
        //     }
        // }

        // curl_close($ch);
    } else {
        echo "Failed to retrieve access token";
    }
}



function getAccessTokens() {
    $keyFilePath = 'dmboos.json'; // Path to your service account JSON file
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
?>



