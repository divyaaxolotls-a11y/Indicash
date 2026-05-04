<?php
$resultDate = $_POST['resultDate'];
$gameID = $_POST['gameID'];
$session = $_POST['session'];
$pana = $_POST['pana'];
$digit = $_POST['digit'];
$dateTime = date('Y-m-d H:i');

include('config.php'); 

 
 if($resultDate !== '' && $gameID !== '' && $session !== '' && $pana !== '' && $digit !== ''){
  
    if($session == 'open'){
        $selectResult = mysqli_query($con, "SELECT * FROM `declare_result` WHERE `result_date`='$resultDate' AND `game_id`='$gameID' ");
        $count = mysqli_num_rows($selectResult);
        $row = mysqli_fetch_array($selectResult);
        
        if($count > 0){
            if($row['declare_status_open'] == 1){
                    $response = array(
                        "result" => false,
                        "msg"   =>  'This game open result already Declared.'
                    );
            }else{
                $response = array(
                        "result" => true,
                        "msg"   =>  'ok.'
                    );
            }
            
        }else{
            $insert = mysqli_query($con, "INSERT INTO `declare_result`(`game_id`, `result_date`, `open_declare_date`, `open_pana`, `open_digit`, `declare_status_open`) 
                                                                VALUES ('$gameID','$resultDate','$dateTime','$pana','$digit','0')");
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
     
    }elseif($session == 'close'){
         $selectResult = mysqli_query($con, "SELECT * FROM `declare_result` WHERE `result_date`='$resultDate' AND `game_id`='$gameID' ");
            $count = mysqli_num_rows($selectResult);
            $row = mysqli_fetch_array($selectResult);
            
            if($count > 0){
                if($row['declare_status_open'] == 1){
                    $update = mysqli_query($con, "UPDATE `declare_result` SET `close_declare_date`='$dateTime',`close_pana`='$pana',`close_digit`='$digit',
                                                                    `declare_status_close`='0' WHERE `game_id`='$gameID' AND `result_date`='$resultDate'");
                        if($update){
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
                        
                }else{
                    $response = array(
                            "result" => false,
                            "msg"   =>  'Please Declare Open Result.'
                        );
                }
            }
    }
     
 }

echo json_encode($response);
?>