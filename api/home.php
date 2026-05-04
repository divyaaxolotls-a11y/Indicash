<?php
include "con.php";

date_default_timezone_set('Asia/Kolkata');
$stamp = time() + 6.63*3600; // Add 7 hours
$current_time = date("H:i", $stamp);
extract($_REQUEST);

// Set timezone to your local timezone (e.g., 'Asia/Kolkata' for India)

if(mysqli_num_rows(mysqli_query($con,"select sn from users where mobile='$mobile' and session ='$session'")) == 0){
    $data['msg'] = "You are not authrized to use this";
      
    $dd = mysqli_query($con,"select session,active from users where mobile='$mobile'");
    $d = mysqli_fetch_array($dd);
    $data['session'] = "0";
    $data['active'] = "0";
    
    echo json_encode($data);
    return;
} else {
  
    $data['session'] = "1";
    $data['active'] = "1";
}
$stamp = time();  // This should return the current Unix timestamp

$day = strtoupper(date("l",$stamp));
$date = date('d/m/Y');

// 1. Get the blocked list for this specific user
$u_res = mysqli_query($con, "SELECT blocked_markets FROM users WHERE mobile='$mobile'");
$u_row = mysqli_fetch_array($u_res);
// $blocked_list = explode(',', $u_row['blocked_markets'] ?? '');
$blocked_string = trim($u_row['blocked_markets'] ?? '');
$blocked_array = ($blocked_string != '') ? explode(',', $blocked_string) : [];
//$curr_datetime = date("Y-m-d "+"00:00:00");

$get = mysqli_query($con,"select * from gametime_manual order by open");
while($xc = mysqli_fetch_array($get))
{
    $bazar = $xc['market']; // Get the current market name

    // --- ADD THIS CHECK HERE ---
    if (in_array($bazar, $blocked_array)) {
        continue; // This skips the current market and moves to the next one
    }
    // $time = date("H:i",$stamp);
    $time = date("H:i");
    
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
        
    }
    else if(substr_count($xc['days'],$day."(CLOSED)") > 0){
        //checks holidys
        $xc['is_open'] = "0";
        $xc['is_close'] = "0";
        $xc['open'] = "CLOSE";
        $xc['close'] = "CLOSE";
        $xc['open_time'] = "CLOSE";
        $xc['close_time'] = "CLOSE";
    } 
    else {
        // echo $xc['market'] ; echo " wwwwwwwwwwwwww  " ; echo $current_time;

        $time_array = explode(",",$xc['days']);
        for($i =0;$i< count($time_array);$i++){
            if(substr_count($time_array[$i],$day) > 0){
                $day_conf = $time_array[$i];
            }
        }
        
        $day_conf = str_replace($day."(","",$day_conf);
        $day_conf = str_replace(")","",$day_conf);
        
        $mrk_time = explode("-",$day_conf);
        
        
        $xc['open'] = $mrk_time[0];
        $xc['close'] = $mrk_time[1];
        
        // if(strtotime($time)<strtotime($mrk_time[0]))
        // {
        //     $xc['is_open'] = "1";
        // }
        // else
        // {
        //     $xc['is_open'] = "0";
        // }
        
        // if(strtotime($time)<strtotime($mrk_time[1]))
        // {
        //     $xc['is_close'] = "1";
        // }
        // else
        // {
        //     $xc['is_close'] = "0";
        // }
        // $current_time = strtotime(date("Y-m-d ") . $time);        
       if($current_time < $mrk_time[0] ) {
            // echo $xc['market'] ; echo " wwwwwwwwwwwwww  " ; echo $current_time;
            $xc['is_open'] = "1";
            $xc['is_close'] = "1";
        } elseif($current_time >= $mrk_time[0] && $current_time < $mrk_time[1]) {
            // echo $xc['market'] ; echo " eeeeeeeeee  " ; echo $current_time;
            $xc['is_open'] = "0";
            $xc['is_close'] = "1";
        } else {
            // echo $xc['market'] ; echo " pppppppppppppppppp  " ; echo $current_time;
            $xc['is_open'] = "0";
            $xc['is_close'] = "0";
        }
    }
    
    $bazar = $xc['market'];
    
    $chk_if_query = mysqli_query($con,"select * from manual_market_results where market='$bazar' AND date='$date'");
    if(mysqli_num_rows($chk_if_query) > 0){
        $xc['is_open'] = "0";
        $chk_if_updated = mysqli_fetch_array($chk_if_query);
    
         
        if($chk_if_updated['close'] != ''){
           
            $xc['is_close'] = "0";
           
        } 
        
        
    } 
   
    
    $mrk['market'] = $xc['market'];
    $market  = $xc['market'];
    $date = date("d/m/Y");
    
    $chk_if_query = mysqli_query($con,"select * from manual_market_results where market='$market' AND date='$date'");
    if(mysqli_num_rows($chk_if_query) > 0){
        
    $chk_if_updated = mysqli_fetch_array($chk_if_query);
    
        $rslt = $chk_if_updated['open_panna'].'-'.$chk_if_updated['open'];
        
        if($chk_if_updated['close'] != ''){
            $rslt = $rslt.$chk_if_updated['close'];
        } else {
             $rslt = $rslt.'*';
        }
        
         if($chk_if_updated['close_panna'] != ''){
            $rslt = $rslt.'-'.$chk_if_updated['close_panna'];
        } else {
            $rslt = $rslt.'-***';
        }
        
        
    } else {
        
         
      if (((int) date('H')) < 6) {
        $date2 = date('d/m/Y',strtotime("-1 days"));
         $chk_if_query = mysqli_query($con,"select * from manual_market_results where market='$market' AND date='$date2'");
          if(mysqli_num_rows($chk_if_query) > 0){

          $chk_if_updated = mysqli_fetch_array($chk_if_query);

              $rslt = $chk_if_updated['open_panna'].'-'.$chk_if_updated['open'];

              if($chk_if_updated['close'] != ''){
                  $rslt = $rslt.$chk_if_updated['close'];
              } else {
                   $rslt = $rslt.'*';
              }

               if($chk_if_updated['close_panna'] != ''){
                  $rslt = $rslt.'-'.$chk_if_updated['close_panna'];
              } else {
                  $rslt = $rslt.'-***';
              }


          } else {
      
        $rslt = "***-**-***";
        
      }
      } else {
      
        $rslt = "***-**-***";
        
      }
        
    }
    
    
    $mrk['is_close'] = $xc['is_close'];
    $mrk['is_open'] = $xc['is_open'];
    
    if($xc['open_time'] != "CLOSE"){
    $mrk['open_time'] = date("g:i a", strtotime($xc['open']));
    } else {
     $mrk['open_time'] = "HOLIDAY";
    }
  if($xc['close_time'] != "CLOSE"){
    $mrk['close_time'] = date("g:i a", strtotime($xc['close']));
  }else {
     $mrk['close_time'] = "HOLIDAY";
    }
	$mrk['result'] = $rslt;
    $data['result'][] = $mrk;
}
$today = date("m-d-y ");
   $name = 'open_time';
   usort($data['result'], function ($a, $b) use(&$name){
      return strtotime($today.' '.$a[$name]) - strtotime($today.' '.$b[$name]);});


$dd = mysqli_query($con,"select sn,wallet,active,session,code,transfer_points_status,paytm,verify,name,ref_id,email from users where mobile='$mobile'");
$d = mysqli_fetch_array($dd);

$nt = mysqli_query($con,"select homeline from content where sn='1'");
$n = mysqli_fetch_array($nt);

// Fetch Notice, Marquee, and Homeline from the content table
$content = mysqli_query($con, "SELECT notice, marquee, homeline FROM content WHERE sn='1'");
$ncontent = mysqli_fetch_array($content);

// Creating the new array as requested
$data['notice_data'] = [
    'notice'   => htmlspecialchars_decode($ncontent['notice']),
    'marquee'  => htmlspecialchars_decode($ncontent['marquee']),
    'homeline' => $ncontent['homeline']
];
if($d['code'] == "0")
{
    $code = $d['sn'].rand(100000,9999999);
    mysqli_query($con,"update users set code='$code' where mobile='$mobile'");
}
else
{
    $code = $d['code'];
}


$getConfig = mysqli_query($con,"select * from settings");
while($config = mysqli_fetch_array($getConfig)){
    
    $data[$config['data_key']] = $config['data'];
}

if(mysqli_num_rows(mysqli_query($con,"select sn from gateway_config where active='1'")) > 0){
    $data['gateway'] = "1";
} else {
    $data['gateway'] = "0";
}



$pending_deposite = mysqli_query($con, "SELECT COUNT(*) AS pending_deposite FROM auto_deposits WHERE mobile='$mobile' AND status = 0");
$pending_depo= mysqli_fetch_array($pending_deposite);


$pending_withdrawal = mysqli_query($con, "SELECT COUNT(*) AS withdraw_requests FROM withdraw_requests WHERE user='$mobile' AND status = 0");
$pending_with= mysqli_fetch_array($pending_withdrawal);

$getConfig = mysqli_query($con,"select * from image_slider where verify='".$d['verify']."'");
//$getConfig = query("select * from image_slider where verify='1' AND screen='matka'");
while($config = mysqli_fetch_array($getConfig)){
    
    if($config['refer'] == "market"){
        for($i = 0; $i < count($data['result']); $i++){
            if($data['result'][$i]['market'] == $config['data'] && $data['result'][$i]['is_close'] == "1"){
                
                $config['market']     = $data['result'][$i]['market'];
                $config['is_open']    = $data['result'][$i]['is_open'];
                $config['is_close']   = $data['result'][$i]['is_close'];
                $config['open_time']  = $data['result'][$i]['open_time'];
                $config['close_time'] = $data['result'][$i]['close_time'];
                $data['images'][]     = $config;
            }
            
        }
        
    } else {
        $data['images'][] = $config;
    }
}

$data['transfer_points_status'] = $d['transfer_points_status'];
$data['paytm'] = $d['paytm'];
$data['code'] = $code;
$data['verify'] = $d['verify'];
$data['wallet'] = $d['wallet'];
$data['active'] = $d['active'];
$data['session'] = $d['session'];
$data['homeline'] = $n['homeline'];
$data['name'] = $d['name'];
$data['email'] = $d['email'];
$data['ref_id'] = $d['ref_id'];
$data['id'] = $d['sn'];
$data['pending_deposite'] = $pending_depo['pending_deposite'];
$data['pending_withdrawal'] = $pending_with['withdraw_requests'];

echo json_encode($data);