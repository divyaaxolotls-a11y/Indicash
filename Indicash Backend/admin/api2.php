

<?php

$servername = "localhost";
$username = "upxrjrdpey";
$password = "3FGBuaYp3B";
$dbname = "upxrjrdpey";
$con = mysqli_connect($servername, $username, $password,$dbname);
if (!$con) 
{
    die("Connection failed: " . mysqli_connect_error());
}



$token = mysqli_fetch_array(mysqli_query($con, "SELECT tr_key FROM tr_key WHERE id='1'"));
$tr_key = $token['tr_key']; 
$date=date('Y-m-d');


$market_query = mysqli_query($con, "SELECT * FROM gametime_manual");

if ($market_query) {
    $market_rows = mysqli_fetch_all($market_query, MYSQLI_ASSOC);
foreach ($market_rows as $market) {
     // $market_name = 'TIME BAZAR';
        $market_name = $market['market_name'];

    echo $market_name;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://matkaapi.thebiggame.in/market-data',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'API_token' => $tr_key,
            'markte_name' =>$market_name,
            'date' => $date,
            'username' => '1234567891'
        ),
    ));


$response = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

curl_close($curl);
// echo $response;
$data = json_decode($response, true);

$refreshToken = $data['refresh_token'];
$today_result= $data['today_result'];

foreach ($today_result as $result) {
    $marketName = $result['market_name'];
    $aankdoOpen = $result['aankdo_open'];
    $aankdoClose = $result['aankdo_close'];
    $figureOpen = $result['figure_open'];
    $figureClose = $result['figure_close'];
    $jodi = $result['jodi'];

     echo "Market Name: $marketName, Open: $aankdoOpen, Close: $aankdoClose, Open Panna: $figureOpen, Close Panna: $figureClose, Jodi: $jodi";

            test($con,$marketName, $aankdoOpen, $aankdoClose, $figureOpen, $figureClose,$date);

}


$stmt = mysqli_prepare($con, "UPDATE tr_key SET tr_key = ? WHERE id = '1'");
mysqli_stmt_bind_param($stmt, "s", $refreshToken);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);


    }
} 

function test($con,$market, $opanna, $cpanna, $open, $close,$date)
{
    $date=date('d/m/Y');
$stamp = time();

    // Sample implementation of the test function
    echo "Test Function Called: Market: $market, Openna: $opanna, Cpanna: $cpanna, Open: $open, Close: $close<br><br><br><br>";

    $chk_if_query = mysqli_query($con, "select sn from manual_market_results where market='$market' AND date='$date'");
    if(mysqli_num_rows($chk_if_query) > 0){
        $chk_if_updated = mysqli_fetch_array($chk_if_query);
        $sn = $chk_if_updated['sn'];
        mysqli_query($con, "update manual_market_results set close='$close', close_panna='$cpanna' where sn='$sn'");
    } else {
        
        mysqli_query($con, "INSERT INTO `manual_market_results`(`market`, `date`, `open_panna`, `open`, `close`, `close_panna`, `created_at`) VALUES ('$market','$date','$opanna','$open','$close','$cpanna','$stamp')");

          /////////////////////////
    //// CREATING BATCH /////
    /////////////////////////
    
    $batch_id = md5($stamp.$market.rand().$open.$close.$date.$day.$time);
    
    $batch_result = $opanna.'-'.$open.$close.'-'.$cpanna;
        
    mysqli_query($con, "INSERT INTO `manual_batch`( `market`, `result`, `revert`, `created_at`, `batch_id`,`date`) VALUES ('$market','$batch_result','0','$stamp','$batch_id','$date')");
        
    }


    
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
            
        }
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$mrk' AND game='single' AND date='$date' AND number!='$open' AND is_loss='0'");
        
    
    }
     if($opanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana')");
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
       
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana') AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
    }
    }


        if($opanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' OPEN');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND date='$date' AND number='$opanna' AND status='0' AND is_loss='0' AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana')");
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
             
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana')AND date='$date' AND number!='$opanna' AND status='0' AND is_loss='0'");
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
            }
        } 
        
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND game='single' AND date='$date' AND number!='$close' AND status='0' AND is_loss='0'");
    
    }
    
    if($cpanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana') AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type !='Sp' AND game_type !='Dp' AND game_type !='round' AND game_type !='centerpanna' AND game_type !='aki' AND game_type !='beki'  AND game_type !='chart50' AND game_type !='chart60' AND game_type !='chart70' AND game_type !='akibekicut30'AND game_type !='abr30pana' AND game_type !='startend'  AND game_type !='cyclepana') AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
    
    }
}


     if($cpanna != ""){
        
        
        $bazar = str_replace(" ","_",$market.' CLOSE');
        
        $xx = mysqli_query($con, "select * from games where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana') AND date='$date' AND number='$cpanna' AND status='0' AND is_loss='0'");
        
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
             
            sendNotification("Congratulations, You won",$msg,$user);
            
            }
            
        } 
        
        mysqli_query($con, "UPDATE games set is_loss='1' where bazar='$bazar' AND ( game='singlepatti' OR  game='doublepatti' OR  game='triplepatti' ) AND (game_type ='Sp' OR game_type ='Dp' OR game_type ='round' OR game_type ='centerpanna' OR game_type ='aki' OR game_type ='beki'  OR game_type ='chart50' OR game_type ='chart60' OR game_type ='chart70' OR game_type ='akibekicut30'OR game_type ='abr30pana' OR game_type ='startend'  OR game_type ='cyclepana')AND date='$date' AND number!='$cpanna' AND status='0' AND is_loss='0'");
    
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
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
             
            //sendNotification("Congratulations, You won",$msg,$user);
            }
            
        } 
        
        
        mysqli_query($con, "update games set is_loss='1' where bazar like '%$bazar%' AND game='halfsangam' AND date='$date' AND ( number='$num1' or number='$num2') AND status='0' AND is_loss='0'");
        
    }


     

}

mysqli_close($con);




?>