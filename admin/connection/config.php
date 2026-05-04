<?php
ob_start();
session_start();

define("zone",  'Asia/Kolkata');    /** FIND YOUR TIMEZONE - https://www.php.net/manual/en/timezones.php */


define("SALT","Z~Zhy}~FVaJmY:oGmjQ8a/AEe7&F3|pco(vdHnM%9d`]50(Og^hUyoZ:$?maZ^m");    /** 504 bit SALT TO SECURE PASSWORD */

$stamp = time();


function sendNotification($body,$title,$topic){
    $msg = array
    (
        'body'  => $body,
        'title'     => $title,
        'vibrate'   => 1,
        'sound'     => 1,
    );
    
    $data['body'] = $body;
    $data['title'] = $title;
    
    $fields = array
    (
        'to'  => '/topics/'.$topic,
        'notification'  => $msg,
        'data' => $data
    );
    
    $headers = array
    (
        'Authorization: key=AIzaSyBc8A9ODOBb86Rn7pTYKTbTt3qghjAoD48',
        'Content-Type: application/json'
    );
    
    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
    curl_close( $ch );
    
    return $result;
}



// include "lib/lib.php";

