<?php

    $resultDate = $_POST['resultDate'];
    $gameId = $_POST['gameID'];
    $pana = $_POST['pana'];
    $digit = $_POST['digit'];
    
    include('config.php');
    
    if($resultDate != '' && $gameId != '' && $pana != '' && $digit != ''){
    
        $selectResult = mysqli_query($con, "SELECT * FROM `starline_declare_result` WHERE `game_id`='$gameId' AND `result_date`='$resultDate' ");
        $count = mysqli_num_rows($selectResult);
        $row = mysqli_fetch_array($selectResult);
        // echo $row;
        if($count > 0){
            if($row['declare_result_status'] == 1){
                    $response = array(
                        "result" => false,
                        "msg"   =>  'This game result already Declared.'
                    );
            }else{
                $response = array(
                        "result" => true,
                        "msg"   =>  'ok.'
                    );
            }
        }else{
        ///////// insert data declare result
        $insert = mysqli_query($con, "INSERT INTO `starline_declare_result`(`game_id`, `result_date`, `open_pana`, `open_digit`, `declare_result_status`) 
                                                                        VALUES ('$gameId','$resultDate','$pana','$digit','0')");
            // echo "hiiiiiiiiiiiiiii";
            if($insert){
                $response = array(
                        "result" => true,
                        "msg"   =>  'Result successfully Added.'
                    );
            }else{
                $response = array(
                        "result"    => false,
                        "msg"       => 'Server Error. Please Try again.'
                    );
            }
        }
    }
    
    
    echo json_encode($response);
?>