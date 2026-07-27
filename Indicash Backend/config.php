<?php
date_default_timezone_set("Asia/Kolkata");


$servername = "localhost";
$username = "sql_demo_dmbossg";
$password = "c7bcc86b39b8f8";
$dbname = "sql_demo_dmbossg";

// Create connection
$con = mysqli_connect($servername, $username, $password,$dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully";


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
        'Authorization: key=AAAAqSyhZTM:APA91bE9D0L-IguLfNdMbdpZuDczV611Edi6jNCpAko2zm0PrFZ9pOV7hn0yv7L9yBoDem8_pjErwaGapUXVcCg30In_3Ac4WkxaKqEsVjIJbg-q6UL8FC2AvZ1orDRjkbhaTFkPw1oW',
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
}


function getRate($market,$game){


$servername = "localhost";
$username = "sql_demo_dmbossg";
$password = "c7bcc86b39b8f8";
$dbname = "sql_demo_dmbossg";

  // Create connection
  $con = mysqli_connect($servername, $username, $password,$dbname);

  $check_man = mysqli_query($con,"select rate from market_rates where market='$market' AND game='$game'");
  if(mysqli_num_rows($check_man)>0){
   $get_man = mysqli_fetch_array($check_man); 
    return $get_man['rate'];
  } else {
    $get_rate = mysqli_fetch_array(mysqli_query($con,"select * from rate"));
    return $get_rate[$game];
  }
}
?>