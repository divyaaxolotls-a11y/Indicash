<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "con.php";

$mobile = isset($_REQUEST['mobile']) ? mysqli_real_escape_string($con, $_REQUEST['mobile']) : '';
$name = isset($_REQUEST['name']) ? mysqli_real_escape_string($con, $_REQUEST['name']) : '';
$email = isset($_REQUEST['email']) ? mysqli_real_escape_string($con, $_REQUEST['email']) : '';
$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : '';
$refcode = isset($_REQUEST['refcode']) ? mysqli_real_escape_string($con, $_REQUEST['refcode']) : '';
$session = isset($_REQUEST['session']) ? mysqli_real_escape_string($con, $_REQUEST['session']) : '';
$brand = isset($_REQUEST['brand']) ? mysqli_real_escape_string($con, $_REQUEST['brand']) : '';
$model = isset($_REQUEST['model']) ? mysqli_real_escape_string($con, $_REQUEST['model']) : '';
$dev_id = isset($_REQUEST['device_id']) ? mysqli_real_escape_string($con, $_REQUEST['device_id']) : '';
$login_time = date("d/m/Y h:i A");
$plain_password = $pass; // store plain password
date_default_timezone_set("Asia/Kolkata");

// Get user's IP address
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $user_ip = $_SERVER['REMOTE_ADDR'];
}


$stamp = time();
$time = date("H:i", $stamp);
$day = strtoupper(date("l", $stamp));

function generateRefId($length = 5) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $refId = '';
    for ($i = 0; $i < $length; $i++) {
        $refId .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $refId;
}

$refId = generateRefId();

if (empty($mobile)) {
    $data['success'] = "0";
    $data['msg'] = "Mobile number required";
} 


$current_time= time();
$one_minute_ago = $current_time-60;

// Check if there's a previous request from the same IP within the last minute
$stmt = $con->prepare("SELECT sn FROM users WHERE ip_address = ? AND created_at > ?");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("ss", $user_ip, $one_minute_ago);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data['msg'] = "You can only make one request per minute from the same IP address.";
    $data['success'] = "0";
    echo json_encode($data);
    return;
}
$stmt->close();



$stmt = $con->prepare("SELECT mobile FROM users WHERE mobile = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$check = $stmt->get_result();
$stmt->close();

if (mysqli_num_rows($check) > 0) {
    $data['success'] = "0";
    $data['msg'] = "Mobile number already registered";
} else {

    $commisionQuery = mysqli_query($con, "SELECT commision FROM admin WHERE ref_id='$refcode'");
    $commision = mysqli_fetch_array($commisionQuery);
    $com = $commision['commision'] ?? 0;

    if ($com > 0) {
        mysqli_query($con, "UPDATE admin SET wallet=wallet+'$com' WHERE ref_id='$refcode'");
    }

    $code = substr($mobile, 0, 2) . rand(100000, 9999999);

    $verify = (mysqli_num_rows(mysqli_query($con, "SELECT data FROM settings WHERE data_key='auto_verify' AND data='1'")) > 0) ? "1" : "0";

    $get_reward = mysqli_fetch_array(mysqli_query($con, "SELECT data FROM settings WHERE data_key='signup_reward'"));

    $password = md5($pass);



    // $insertQuery = "INSERT INTO `users`(`name`, `mobile`,`email`, `password`, `created_at`, `code`, `verify`, `wallet`, `session`, `refcode`,`ref_id`,`ip_address`) VALUES
    // ('$name','$mobile','$email','$password','$stamp', '$code','$verify','".$get_reward['data']."','$session','$refcode','$refId','$user_ip')";

    // $insertQuery = "INSERT INTO `users`(`name`, `mobile`,`email`, `password`, `created_at`, `code`, `verify`, `wallet`, `session`, `refcode`,`ref_id`,`ip_address`, `device_brand`, `device_model`, `device_id`, `last_login_time`) VALUES 
    // ('$name','$mobile','$email','$password','$stamp', '$code','$verify','".$get_reward['data']."','$session','$refcode','$refId','$user_ip', '$brand', '$model', '$dev_id', '$login_time')";
    $insertQuery = "INSERT INTO `users`(
    `name`,`mobile`,`email`,`password`,`plain_password`,
    `created_at`,`code`,`verify`,`wallet`,`session`,
    `refcode`,`ref_id`,`ip_address`,`device_brand`,
    `device_model`,`device_id`,`last_login_time`
    ) VALUES (
        '$name','$mobile','$email','$password','$plain_password',
        '$stamp','$code','$verify','".$get_reward['data']."','$session',
        '$refcode','$refId','$user_ip','$brand',
        '$model','$dev_id','$login_time'
    )";
    
    
    mysqli_query($con, $insertQuery);

    if (mysqli_error($con)) {
        die("SQL error: " . mysqli_error($con));
    }

    $data['success'] = "1";
    $data['msg'] = "Register successful";

    if (!empty($refcode)) {
        mysqli_query($con, "INSERT INTO `refers`(`user`, `code`) VALUES ('$mobile','$refcode') ");
    }
}

echo json_encode($data);
?>
