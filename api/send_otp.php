<?php

function httpGet($url)
{
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//  curl_setopt($ch,CURLOPT_HEADER, false); 
 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
}
 
$mobile = $_REQUEST['mobile'];
$otp = $_REQUEST['otp'];

if($_REQUEST['code'] == "38ho3f3ws"){
echo httpGet("https://2factor.in/API/V1/e68df82f-7610-11ec-b710-0200cd936042/SMS/+91$mobile/$otp");
} else {
  
  echo '<h3>404 page not found</h3>';
  
}