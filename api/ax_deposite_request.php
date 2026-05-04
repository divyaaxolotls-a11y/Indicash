<?php
include "con.php";
date_default_timezone_set('Asia/Kolkata');
extract($_REQUEST);

if (empty($mobile) || empty($session)) {
    $data['msg'] = "Mobile number and session are required.";
    $data['active'] = "0";
    echo json_encode($data);
    return;
}

$stmt = $con->prepare("SELECT sn FROM users WHERE mobile = ? AND session = ?");
$stmt->bind_param("ss", $mobile, $session);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $data['msg'] = "You are not authorized to use this";
    $data['active'] = "0";
    echo json_encode($data);
    return;
} else {
    $stmt = $con->prepare("SELECT active FROM users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $dd = $stmt->get_result();
    $d = $dd->fetch_array();
    $data['active'] = "1";
}

$get_times = $con->query("SELECT * FROM settings WHERE data_key = 'withdrawOpenTime' OR data_key = 'withdrawCloseTime'");
while ($get = $get_times->fetch_array()) {
    $times[$get['data_key']] = $get['data'];
}

$user_ip = $_SERVER['REMOTE_ADDR'];
$current_time = date('H:i:s Y-m-d');
$sunrise = $times['withdrawOpenTime'] . ":00 " . date('Y-m-d');
$sunset = $times['withdrawCloseTime'] . ":00 " . date('Y-m-d');
$date1 = strtotime($current_time);
$date2 = strtotime($sunrise);
$date3 = strtotime($sunset);
$created_at = date('Y-m-d h:i:sa');



$current_times= date('Y-m-d H:i:s');
$one_minute_ago = date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($current_times)));

// Check if there's a previous request from the same IP within the last minute
$stmt = $con->prepare("SELECT sn FROM auto_deposits WHERE ip_address = ? AND created_at > ?");
$stmt->bind_param("ss", $user_ip, $one_minute_ago);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data['msg'] = "You can only make one request per minute from the same IP address.";
    $data['success'] = "0";
    echo json_encode($data);
    return;
}

if (!is_numeric($amount) || $amount <= 0) {
    $data['msg'] = "Invalid amount";
    $data['success'] = "0";
    echo json_encode($data);
    return;
}

$stmt = $con->prepare("SELECT wallet FROM users WHERE mobile = ? AND wallet >= ?");
$stmt->bind_param("si", $mobile, $amount);
$stmt->execute();
$check = $stmt->get_result();

mysqli_query($con,"INSERT INTO `auto_deposits`(`mobile`, `amount`, `method`, `pay_id`, `status`,`date`,`ip_address`) VALUES ('$mobile','$amount','$method','$pay_id','0','$created_at','$user_ip')");

$get_bal = mysqli_fetch_array(mysqli_query($con,"select wallet from users where mobile='$mobile'"));
$data['wallet'] = $get_bal['wallet'];

$data['msg'] = "Your Deposite request received by our team";
$data['success'] = "1";

echo json_encode($data);
?>
