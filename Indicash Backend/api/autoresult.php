<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include your database connection file
include "con.php";
date_default_timezone_set('Asia/Kolkata');

// Get the current time in 24-hour format
$currentTime = date('H:i');

// Prepare the SQL query to select markets where the open time is less than the current time
$query = "
    SELECT * FROM `gametime_manual`
    WHERE `active` = 1
    AND STR_TO_DATE(open, '%H:%i') < STR_TO_DATE('$currentTime', '%H:%i')
    ORDER BY STR_TO_DATE(open, '%H:%i') DESC
";

// Execute the query
$result = mysqli_query($con, $query);

// Initialize an array to store the closest markets
$markets = [];

// Check if the query executed successfully
if ($result === false) {
    $error = mysqli_error($con);
    $markets[] = [
        'success' => '0',
        'msg' => 'Query failed: ' . htmlspecialchars($error)
    ];
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $markets[] = [
            'market' => $row['market'],
            'open' => $row['open'],
            'close' => $row['close'],
            'active' => $row['active'],
        ];
    }

    if (empty($markets)) {
        $markets[] = [
            'success' => '0',
            'msg' => 'No market found with an open time greater than the current time.'
        ];
    }
}

echo json_encode($markets);

// Fetch the API token from the `tr_key` table
$id = 1;
$selectQuery = "SELECT tr_key FROM tr_key WHERE id = ?";
$stmt = $con->prepare($selectQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $API_token = $row['tr_key'];

    // Define the API URL
    $url = "https://matkawebhook.matka-api.online/market-data";
    $username = "9888195353";
    $date = date('Y-m-d');

    // Loop through each market and process the data
    foreach ($markets as $marketData) {
        $market_name = $marketData['market'];
        $open = $marketData['open'];
        $close = $marketData['close'];
        
        // Execute your additional logic for each market
        $session = 'open';
        $stamp = time();
        $time = date("H:i", $stamp);
        $day = strtoupper(date("l", $stamp));
        $formatted_date = date('d/m/Y');

        echo "<br>Session status: " . htmlspecialchars($session) . "<br>";
        echo "Timestamp: " . htmlspecialchars($stamp) . "<br>";
        echo "Time: " . htmlspecialchars($time) . "<br>";
        echo "Day: " . htmlspecialchars($day) . "<br>";
        echo "Date: " . htmlspecialchars($formatted_date) . "<br>";
        echo "Open: " . htmlspecialchars($open) . "<br>";

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
    


    $chk_if_query = mysqli_query($con, "select sn from manual_market_results where market='$market' AND date='$date'");
    if(mysqli_num_rows($chk_if_query) > 0){
        echo "1";
        $chk_if_updated = mysqli_fetch_array($chk_if_query);
        $sn = $chk_if_updated['sn'];
        mysqli_query($con, "update manual_market_results set close='$close', close_panna='$cpanna', status='1' where sn='$sn'");
    } else {
                echo "::";
                echo $open;

       mysqli_query($con, "INSERT INTO `manual_market_results`(`market`, `date`, `open_panna`, `open`, `close`, `close_panna`,`status`, `created_at`) VALUES ('$market','$date','$opanna','$open','$close','$cpanna','1','$stamp')");
     
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
             
            // sendNotification("Congratulations, You won",$msg,$user);
            
        }
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$mrk' AND game='single' AND date='$date' AND number!='$open' AND is_loss='0'");
        
    
    }
    
    if($opanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana'  AND game_type !='groupjodi'  AND game_type !='panelgroup' AND game_type !='bulkjodi' AND game_type !='bulksp' AND game_type !='bulkdp')");
        $rowCount = mysqli_num_rows($xx);
if($rowCount != 0){
                            //var_dump("hee",$xv[$x['game']]);die();

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
             
            // sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
       
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='groupjodi' AND game_type !='panelgroup' AND game_type !='bulkjodi' AND game_type !='bulksp'  AND game_type !='bulkdp' ) AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
    }
    }


        if($opanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='groupjodi' OR game_type ='panelgroup' OR game_type ='bulkjodi' OR game_type ='bulksp' OR game_type ='bulkdp' )");
          $rowCount = mysqli_num_rows($xx);
         // var_Dump($rowCount);die();
if($rowCount > 0){
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
             
            // sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='groupjodi' OR game_type ='panelgroup' OR game_type ='bulkjodi' OR game_type ='bulksp'  OR game_type ='bulkdp' )AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
    }
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
             
            // sendNotification("Congratulations, You won",$msg,$user);
            }
        } 
        
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND game='single' AND date='$date' AND number!='$close' AND status='0' AND is_loss='0'");
    
    }
    
    if($cpanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='groupjodi' AND game_type !='panelgroup' AND game_type !='bulkjodi' AND game_type !='bulksp'  AND game_type !='bulkdp'  ) AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
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
             
            // sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana' AND game_type !='groupjodi' AND game_type !='panelgroup' AND game_type !='bulkjodi' AND game_type !='bulksp' AND game_type !='bulkdp' ) AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
    
    }
}


     if($cpanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana' OR game_type ='groupjodi' OR game_type ='panelgroup' OR game_type ='bulkjodi' OR game_type ='bulksp' OR game_type ='bulkdp' ) AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
       if($xx){                   
        while($x = mysqli_fetch_array($xx))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game_type']];
            
            
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0' AND is_loss='0'")) > 0){
        
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                                 
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            // sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana'  OR game_type ='groupjodi' OR game_type ='panelgroup' OR game_type ='bulkjodi' OR game_type ='bulksp' OR game_type ='bulkdp'  )AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
    
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
            $amount = $x['amount']*$xv[$x['game']];
        
        
            if(mysqli_num_rows(mysqli_query($con, "select sn from games where sn='$sn' AND status='0'")) > 0){
                mysqli_query($con, "update games set status='1' where sn='$sn'");
            
                mysqli_query($con, "update users set wallet=wallet+'$amount' where mobile='$user'");
                
                $remrk = $x['game']." ".$x['bazar']." Winning";
                
            mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`batch_id`,`game_id`) VALUES ('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')");
                             
            $msg = "You won ".$amount." for your ".$mrk.' '.$x['game'].' game';
             
            // sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND game='jodi' AND date='$date' AND number!='$full_num' AND status='0' AND is_loss='0'");
    
    } 
    
    if($opanna != "" && $cpanna != ""){
       //  var_dump("tata");die();
        $full_num = $opanna.'-'.$cpanna;
         
        $bazar = str_replace(" ","_",$market);
                        
      $xx = mysqli_query($con, "select * from games where bazar like '%$bazar%' AND game='fullsangam' AND date='$date' AND number='$full_num' AND status='0' AND is_loss='0'");

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
             
            // sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        mysqli_query($con, "update games set is_loss='1' where bazar like '%$bazar%' AND game='fullsangam' AND date='$date' AND number!='$full_num' AND status='0' AND is_loss='0'");
         
    }
    
    
    
    if($opanna != "" && $cpanna != "" && $open != "" && $close != ""){
        

        $num1 = $opanna.'-'.$close;
        $num2 = $open.'-'.$cpanna;
        
        $xx = mysqli_query($con, "select * from games where bazar like '%$bazar%' AND game='halfsangam' AND date='$date' AND ( number='$num1' or number='$num2') AND status='0' AND is_loss='0'");

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
             
            // sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        mysqli_query($con, "update games set is_loss='1' where bazar like '%$bazar%' AND game='halfsangam' AND date='$date' AND ( number='$num1' or number='$num2') AND status='0' AND is_loss='0'");
        
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
    

  
//   sendNotification($body,$result,"result");
  
    unset($open);
    unset($opanna);
    unset($close);
    unset($cpanna);
    
    }
} else {
    echo "No API token found in the database!";
}

// Close the statement
$stmt->close();
