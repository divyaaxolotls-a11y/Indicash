<?php
    $resultDate = $_POST['resultDate'];
    $gameID = $_POST['gameID'];
    $session = $_POST['session'];
    $dateTime = date('Y-m-d H:i');
    
    include('config.php');
    
    // Game Rates Calculate
    $gameRates = mysqli_query($con, "SELECT *FROM `game_rates` ");
    $rates = mysqli_fetch_array($gameRates);
    
    $singleDigitRate = $rates['single_digit_value2'] / $rates['single_digit_value1'];
    $jodiDigitRate = $rates['jodi_digit_value2'] / $rates['jodi_digit_value1'];
    $singlePanaRate = $rates['single_pana_value2'] / $rates['single_pana_value1'];
    $doublePanaRate = $rates['double_pana_value2'] / $rates['double_pana_value1'];
    $triplePanaRate = $rates['triple_pana_value2'] / $rates['triple_pana_value1'];
    $halfSangamRate = $rates['half_sangam_value2'] / $rates['half_sangam_value1'];
    $fullSangamRate = $rates['full_sangam_value2'] / $rates['full_sangam_value1'];
    
    if(!empty($resultDate) && !empty($gameID) && !empty($session)){
        if($session == 'open'){
            /////// Declare Result status
            $update = mysqli_query($con, "UPDATE `declare_result` SET `declare_status_open`='1' WHERE `game_id`='$gameID' AND `result_date`='$resultDate'");
        
            if($update){
                // fetch data for open result
                $result = mysqli_query($con, "SELECT * FROM `declare_result` WHERE `game_id`='$gameID' AND `result_date`='$resultDate' ");
                $fetchResult = mysqli_fetch_array($result);
                $openDigit = $fetchResult['open_digit'];
                $openPana = $fetchResult['open_pana'];
                
                // fetch bid History
                $bidHistory = mysqli_query($con, "SELECT * FROM `bid_history` WHERE `game_id`='$gameID' AND `date`='$resultDate' ");
                while($bidFetch = mysqli_fetch_array($bidHistory)){
                    $i = 1;
                    
                    if($bidFetch['game_type'] == 'single digit' && $openDigit == $bidFetch['open_digit'] && $bidFetch['session'] == 'open'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $singleDigitRate;
                        $type = $bidFetch['game_type'];
                        $winDigit = $bidFetch['open_digit'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
            if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
            // winning History insert data
            $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winPana','$winDigit','$SSion','$type','$bidTx','$resultDate')");
        
                $dateTime = date('Y-m-d H:i');
                    if($winningInsert){
                        $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                        $gameListFetch = mysqli_fetch_array($gameList);
                        $description = $gameListFetch['game_name'].' ('.$type.')';
                        
                        $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                    VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                    }
        }
                        
                    }elseif($bidFetch['game_type'] == 'single pana' && $openPana == $bidFetch['open_pana'] && $bidFetch['session'] == 'open'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $singlePanaRate;
                        $type = $bidFetch['game_type'];
                        $winPana = $bidFetch['open_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
            // winning History insert data
            $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winPana','$winDigit','$SSion','$type','$bidTx','$resultDate')");
        
                $dateTime = date('Y-m-d H:i');
                    if($winningInsert){
                        $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                        $gameListFetch = mysqli_fetch_array($gameList);
                        $description = $gameListFetch['game_name'].' ('.$type.')';
                        
                        $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                    VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                    }
        }
                        
                    }elseif($bidFetch['game_type'] == 'double pana' && $openPana == $bidFetch['open_pana'] && $bidFetch['session'] == 'open'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $doublePanaRate;
                        $type = $bidFetch['game_type'];
                        $winPana = $bidFetch['open_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                        
                if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
            // winning History insert data
            $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winPana','$winDigit','$SSion','$type','$bidTx','$resultDate')");
        
                $dateTime = date('Y-m-d H:i');
                    if($winningInsert){
                        $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                        $gameListFetch = mysqli_fetch_array($gameList);
                        $description = $gameListFetch['game_name'].' ('.$type.')';
                        
                        $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                    VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                    }
        }
                        
                    }elseif($bidFetch['game_type'] == 'triple pana' && $openPana == $bidFetch['open_pana'] && $bidFetch['session'] == 'open'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $triplePanaRate;
                        $type = $bidFetch['game_type'];
                        $winPana = $bidFetch['open_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                        
                if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
            // winning History insert data
            $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winPana','$winDigit','$SSion','$type','$bidTx','$resultDate')");
        
                $dateTime = date('Y-m-d H:i');
                    if($winningInsert){
                        $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                        $gameListFetch = mysqli_fetch_array($gameList);
                        $description = $gameListFetch['game_name'].' ('.$type.')';
                        
                        $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                    VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                    }
        }
                        
                    }
        

                    
                    $i++;
                }
            }
        }elseif($session == 'close'){
            /////// Declare Result status
            $update = mysqli_query($con, "UPDATE `declare_result` SET `declare_status_close`='1' WHERE `game_id`='$gameID' AND `result_date`='$resultDate'");
            
            if($update){
                // fetch data for open result
                $result = mysqli_query($con, "SELECT * FROM `declare_result` WHERE `game_id`='$gameID' AND `result_date`='$resultDate' ");
                $fetchResult = mysqli_fetch_array($result);
                $openDigit = $fetchResult['open_digit'];
                $openPana = $fetchResult['open_pana'];
                $closeDigit = $fetchResult['close_digit'];
                $closePana = $fetchResult['close_pana'];
                
                // fetch bid History
                $bidHistory = mysqli_query($con, "SELECT * FROM `bid_history` WHERE `game_id`='$gameID' AND `date`='$resultDate' ");
                $i = 1;
                while($bidFetch = mysqli_fetch_array($bidHistory)){
                    
                    if($bidFetch['game_type'] == 'single digit' && $closeDigit == $bidFetch['close_digit'] && $bidFetch['session'] == 'close'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $singleDigitRate;
                        $type = $bidFetch['game_type'];
                        
                        $winCloseDigit = $bidFetch['close_digit'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                        if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }elseif($bidFetch['game_type'] == 'single pana' && $closePana == $bidFetch['close_pana'] && $bidFetch['session'] == 'close'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $singlePanaRate;
                        $type = $bidFetch['game_type'];
                        
                        $winClosePana = $bidFetch['close_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                        if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                            // winning history insert
                            $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                            
                                $dateTime = date('Y-m-d H:i');
                                if($winningInsert){
                                    $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                    $gameListFetch = mysqli_fetch_array($gameList);
                                    $description = $gameListFetch['game_name'].' ('.$type.')';
                                    
                                    $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                                VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                                }
                        }
                        
                    }elseif($bidFetch['game_type'] == 'double pana' && $closePana == $bidFetch['close_pana'] && $bidFetch['session'] == 'close'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $doublePanaRate;
                        $type = $bidFetch['game_type'];
                        
                        $winClosePana = $bidFetch['close_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                        if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }elseif($bidFetch['game_type'] == 'triple pana' && $closePana == $bidFetch['close_pana'] && $bidFetch['session'] == 'close'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $triplePanaRate;
                        $type = $bidFetch['game_type'];
                        
                        $winClosePana = $bidFetch['close_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                    if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }elseif($bidFetch['game_type'] == 'jodi digit' && $openDigit == $bidFetch['open_digit'] && $closeDigit == $bidFetch['close_digit']){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $jodiDigitRate;
                        $type = $bidFetch['game_type'];
                        
                        $winOpenDigit = $bidFetch['open_digit'];
                        $winCloseDigit = $bidFetch['close_digit'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                    if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }elseif($bidFetch['game_type'] == 'half sangam' && $openDigit == $bidFetch['open_digit'] && $closePana == $bidFetch['close_pana'] && $bidFetch['session'] == 'open'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $halfSangamRate;
                        $type = $bidFetch['game_type'];
                        
                        $winOpenDigit = $bidFetch['open_digit']; 
                        $winClosePana = $bidFetch['close_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                    if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                        
                    }elseif($bidFetch['game_type'] == 'half sangam' && $closeDigit == $bidFetch['close_digit'] && $openPana == $bidFetch['open_pana'] && $bidFetch['session'] == 'close'){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $halfSangamRate;
                        $type = $bidFetch['game_type'];
                        
                        $winCloseDigit = $bidFetch['close_digit']; 
                        $winOpenPana = $bidFetch['open_pana'];
                        $SSion = $bidFetch['session'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                    if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }elseif($bidFetch['game_type'] == 'full sangam' && $openPana == $bidFetch['open_pana'] && $closePana == $bidFetch['close_pana']){
                        $userId = $bidFetch['user_id'];
                        //$bidTx = $bidFetch['bid_tx_id'];
                        $bidPoint = $bidFetch['points'];
                        $winningPoint = $bidPoint * $fullSangamRate;
                        $type = $bidFetch['game_type'];
                        
                        $winOpenPana = $bidFetch['open_pana'];
                        $winClosePana = $bidFetch['close_pana'];
                        
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                        
                    if(!empty($userId) && !empty($bidTx) && !empty($bidPoint) && !empty($winningPoint) && !empty($type)){
                        // winning history insert
                        $winningInsert = mysqli_query($con, "INSERT INTO `winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `close_pana`, `close_digit`, `session`, `game_type`, `bid_tx_id`, `date`) 
                                                                                    VALUES ('$userId','$bidPoint','$winningPoint','$gameID','$winOpenPana','$winOpenDigit','$winClosePana','$winCloseDigit','$SSion','$type','$bidTx','$resultDate')");                    
                        
                            $dateTime = date('Y-m-d H:i');
                            if($winningInsert){
                                $gameList = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
                                $gameListFetch = mysqli_fetch_array($gameList);
                                $description = $gameListFetch['game_name'].' ('.$type.')';
                                
                                $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                            VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                            }
                    }
                        
                    }
                    
        
                    
                $i++;
                }
            }
        }
    }
    
   
?>