<?php
include "db.php";

/* 1️⃣ Read parameters from URL */
$name   = $_GET['name']   ?? '';
$email  = $_GET['email']  ?? '';
$mobile = $_GET['mobile'] ?? '';
$amount = $_GET['amount'] ?? '';

/* 2️⃣ Validation */
if ($name === '' || $email === '' || $mobile === '' || $amount === '') {
    die("Invalid payment request: Missing parameters");
}

/* 3️⃣ Generate order ID */
$order_id = "ORD" . time();

/* 4️⃣ Insert into DB (PENDING) */
$insert = mysqli_query($conn, "
    INSERT INTO payments (order_id, name, email, mobile, amount, status)
    VALUES ('$order_id', '$name', '$email', '$mobile', '$amount', 'PENDING')
");

if (!$insert) {
    die("DB Insert Error: " . mysqli_error($conn));
}

/* 5️⃣ Call IMB Create Order API */
$IMB_URL = "https://secure-stage.imb.org.in/api/create-order";
$TOKEN   = "62be318515aae921277e24e163d1382b"; // REAL STAGE TOKEN

$postData = http_build_query([
    "customer_mobile" => $mobile,
    "user_token"      => $TOKEN,
    "amount"          => $amount,
    "order_id"        => $order_id,
    "redirect_url"    => "https://test.apluscrm.in/Payment_Gateway/payment_status.php",
    "remark1"         => $email,
    "remark2"         => $name
]);

$ch = curl_init($IMB_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

/* 6️⃣ Redirect to payment page */
if (!empty($result['status']) && $result['status'] === true) {
    header("Location: " . $result['result']['payment_url']);
    exit;
} else {
    echo "Payment Error: " . ($result['message'] ?? 'Unknown error');
}
