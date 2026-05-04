<?php

    $resultDate = $_POST['resultDate'];
    $gameId = $_POST['gameID'];
    
    include('config.php');
    
    // Game Rates
    $gameRates = mysqli_query($con, "SELECT *FROM `starline_game_rates` ");
    $rates = mysqli_fetch_array($gameRates);
    
    $singleDigitRate = $rates['single_digit_value2'] / $rates['single_digit_value1'];
    $singlePanaRate = $rates['single_pana_value2'] / $rates['single_pana_value1'];
    $doublePanaRate = $rates['double_pana_value2'] / $rates['double_pana_value1'];
    $triplePanaRate = $rates['triple_pana_value2'] / $rates['triple_pana_value1'];
    
    if(!empty($resultDate) && !empty($gameId)){
        
        /////// Declare Result status
        $update = mysqli_query($con, "UPDATE `starline_declare_result` SET `declare_result_status`='1' WHERE `game_id`='$gameId' AND `result_date`='$resultDate'");
        
        if($update){
            ///////// fetch data from declare result table
            $result = mysqli_query($con, "SELECT * FROM `starline_declare_result` WHERE `game_id`='$gameId' AND `result_date`='$resultDate' ");
            $fetchResult = mysqli_fetch_array($result);
                $pana = $fetchResult['open_pana'];
                $digit = $fetchResult['open_digit'];
            
            ///// fetch data for starline bid history table
            $bidHistory = mysqli_query($con, "SELECT * FROM `starline_bid_history` WHERE `game_id`='$gameId' AND `date`='$resultDate' ");
            while($bidFetch = mysqli_fetch_array($bidHistory)){
                $i = 1;
                if($bidFetch['game_type'] == 'single digit' && $digit == $bidFetch['open_digit']){
                    $userId = $bidFetch['user_id'];
                    //$bidTx = $bidFetch['bid_tx_id'];
                    $bidPoint = $bidFetch['points'];
                    $winningPoint = $bidPoint * $singleDigitRate;
                    $type = $bidFetch['game_type'];
                    $winDigit = $bidFetch['open_digit'];
                    
                    $dateString = date('Ymd');
                    $fourRandomDigit = rand(1000,9999);
                    $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                    
if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
                
                // winning history insert data
                $winningInsert = mysqli_query($con, "INSERT INTO `starline_winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `game_type`, `bid_tx_id`, `date`) 
                                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameId','$WinPana','$winDigit','$type','$bidTx','$resultDate')");
                $dateTime = date('Y-m-d H:i');
                if($winningInsert){
                    $gameList = mysqli_query($con, "SELECT * FROM `starline_game_list` WHERE `id`='$gameId' ");
                    $gameListFetch = mysqli_fetch_array($gameList);
                    $description = 'StarLine'.$gameListFetch['game_name'].' ('.$type.')';
                    
                    $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                }
                
            }
                    
                }elseif($bidFetch['game_type'] == 'single pana' && $pana == $bidFetch['open_pana']){
                    $userId = $bidFetch['user_id'];
                    //$bidTx = $bidFetch['bid_tx_id'];
                    $bidPoint = $bidFetch['points'];
                    $winningPoint = $bidPoint * $singlePanaRate;
                    $type = $bidFetch['game_type'];
                    $WinPana = $bidFetch['open_pana'];
                    
                    $dateString = date('Ymd');
                    $fourRandomDigit = rand(1000,9999);
                    $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                    
    if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
                
                // winning history insert data
                $winningInsert = mysqli_query($con, "INSERT INTO `starline_winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `game_type`, `bid_tx_id`, `date`) 
                                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameId','$WinPana','$winDigit','$type','$bidTx','$resultDate')");
                $dateTime = date('Y-m-d H:i');
                if($winningInsert){
                    $gameList = mysqli_query($con, "SELECT * FROM `starline_game_list` WHERE `id`='$gameId' ");
                    $gameListFetch = mysqli_fetch_array($gameList);
                    $description = 'StarLine'.$gameListFetch['game_name'].' ('.$type.')';
                    
                    $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                }
                
            }
                    
                }elseif($bidFetch['game_type'] == 'double pana' && $pana == $bidFetch['open_pana']){
                    $userId = $bidFetch['user_id'];
                    //$bidTx = $bidFetch['bid_tx_id'];
                    $bidPoint = $bidFetch['points'];
                    $winningPoint = $bidPoint * $doublePanaRate;
                    $type = $bidFetch['game_type'];
                    $WinPana = $bidFetch['open_pana'];
                    
                    $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                    
                    
    if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
                
                // winning history insert data
                $winningInsert = mysqli_query($con, "INSERT INTO `starline_winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `game_type`, `bid_tx_id`, `date`) 
                                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameId','$WinPana','$winDigit','$type','$bidTx','$resultDate')");
                $dateTime = date('Y-m-d H:i');
                if($winningInsert){
                    $gameList = mysqli_query($con, "SELECT * FROM `starline_game_list` WHERE `id`='$gameId' ");
                    $gameListFetch = mysqli_fetch_array($gameList);
                    $description = 'StarLine'.$gameListFetch['game_name'].' ('.$type.')';
                    
                    $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                }
                
            }
                    
                }elseif($bidFetch['game_type'] == 'triple pana' && $pana == $bidFetch['open_pana']){
                    $userId = $bidFetch['user_id'];
                    //$bidTx = $bidFetch['bid_tx_id'];
                    $bidPoint = $bidFetch['points'];
                    $winningPoint = $bidPoint * $triplePanaRate;
                    $type = $bidFetch['game_type'];
                    $WinPana = $bidFetch['open_pana'];
                    
                    $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $bidTx = 'WXN'.$dateString.$fourRandomDigit;
                    
    if($userId != '' && $bidTx != '' && $bidPoint != '' && $winningPoint != '' && $type != ''){
                
                // winning history insert data
                $winningInsert = mysqli_query($con, "INSERT INTO `starline_winning_history`(`user_id`, `bid_points`, `winning_points`, `game_id`, `open_pana`, `open_digit`, `game_type`, `bid_tx_id`, `date`) 
                                                                                        VALUES ('$userId','$bidPoint','$winningPoint','$gameId','$WinPana','$winDigit','$type','$bidTx','$resultDate')");
                $dateTime = date('Y-m-d H:i');
                if($winningInsert){
                    $gameList = mysqli_query($con, "SELECT * FROM `starline_game_list` WHERE `id`='$gameId' ");
                    $gameListFetch = mysqli_fetch_array($gameList);
                    $description = 'StarLine'.$gameListFetch['game_name'].' ('.$type.')';
                    
                    $wallet = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                                                                VALUES ('$userId','$winningPoint','+','$bidTx','$description','$dateTime')");
                }
                
            }
                    
                }
                
        
            
        $i++;
            }   
        }
        
    }
    
    
    //echo json_encode($response);
?>