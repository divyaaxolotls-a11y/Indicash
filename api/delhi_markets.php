<?php
include "con.php";

extract($_REQUEST);

$time = date("H:i",$stamp);
$day = strtoupper(date("l",$stamp));
$date = date("d/m/Y");


$sx = mysqli_query($con,"SELECT * FROM `rates` where sn='1'");
$x = mysqli_fetch_array($sx);
$data = $x;

$get_timings = mysqli_query($con,"select * from gametime_delhi order by str_to_date(open, '%H:%i')");

while($xc = mysqli_fetch_array($get_timings)){
    
    $market = $xc['market'];
  
    $time_id = $xc['close'];
    $dd['close'] = $time_id;
    
        $dd['days'] = $mrk['days'];
        $dd['$day'] = $day;
    if($mrk['days'] == "" || substr_count($mrk['days'],$day) == 0){
    
        if(strtotime($time)<strtotime($xc['close'])) {
            $dd['is_open'] = "1";
        } else {
            $dd['is_open'] = "0";
        }
        
    } else if(substr_count($xc['days'],$day."(CLOSE)") > 0){
        $dd['is_open'] = "0";
    } else {
        $dd['is_open'] = "0";
    }
    
    $dd['time'] = date("g:i a", strtotime($xc['close']));
    
    $getResult = mysqli_query($con,"select open, close from manual_market_results where market='$market' AND date='$date'");
    if(mysqli_num_rows($getResult) > 0){
        
        $dd['is_open'] = "0";
        
        $result = mysqli_fetch_array($getResult);
        
        $dd['result'] = $result['open'].$result['close'];
    } else {
        $dd['result'] = "-";
    }
    
      //  $dd['result'] = "-";
        
        $dd['market'] = $xc['market'];
  
    if(mysqli_num_rows(mysqli_query($con,"select * from gametime_delhi where market='$market' AND close > str_to_date('$time_id', '%H:%i') limit 1")) > 0){
    
        $dd['is_close'] = "1";
        
    } else {
        $dd['is_close'] = "0";
    }
  
  	//$dd['result'] = $xc['market'].' - '.$dd['result'];
  	$dd['result'] = $dd['result'];
  	$dd['name2'] = $xc['market'];
        
    $data['data'][] = $dd;
}

echo json_encode($data);