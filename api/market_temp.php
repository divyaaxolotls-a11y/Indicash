<?php
include "con.php";

extract($_REQUEST);


$time = date("H:i",$stamp);
//$day = strtoupper(date("l",$stamp));
$day = "SUNDAY";

$get = mysqli_query($con,"select * from gametime_new");
while($xc = mysqli_fetch_array($get))
{
    // echo $xc['days'];
    // echo "<br>";
    // echo $day."(CLOSE)";
    // echo "<br>";
    // echo strpos($xc['days'],$day."(CLOSE)");
    // echo "<br>";
    // echo "<br>";
    if($xc['days'] == "ALL" || substr_count($xc['days'],$day) == 0){
        if(strtotime($time)<strtotime($xc['open']))
        {
            $xc['is_open'] = "1";
        }
        else
        {
            $xc['is_open'] = "0";
        }
        
        if(strtotime($time)<strtotime($xc['close']))
        {
            $xc['is_close'] = "1";
        }
        else
        {
            $xc['is_close'] = "0";
        }
    } else if(substr_count($xc['days'],$day."(CLOSE)") > 0){
        $xc['is_open'] = "0";
        $xc['is_close'] = "0";
        $xc['open'] = "CLOSE";
        $xc['close'] = "CLOSE";
    } else {
        $time_array = explode(",",$xc['days']);
        for($i =0;$i< count($time_array);$i++){
          //  echo $time_array[$i];
//echo strpos($time_array[$i],$day);
            if(substr_count($time_array[$i],$day) > 0){
                $day_conf = $time_array[$i];
            }
        }
        
        $day_conf = str_replace($day."(","",$day_conf);
        $day_conf = str_replace(")","",$day_conf);
        
        $mrk_time = explode("-",$day_conf);
        
        
        $xc['open'] = $mrk_time[0];
        $xc['close'] = $mrk_time[1];
        
        if(strtotime($time)<strtotime($mrk_time[0]))
        {
            $xc['is_open'] = "1";
        }
        else
        {
            $xc['is_open'] = "0";
        }
        
        if(strtotime($time)<strtotime($mrk_time[1]))
        {
            $xc['is_close'] = "1";
        }
        else
        {
            $xc['is_close'] = "0";
        }
    }
    
    $data['data'][] = $xc;
}

echo json_encode($data);