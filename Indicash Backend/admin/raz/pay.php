<?php
require('vendor/autoload.php');
use Razorpay\Api\Api;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');

$api = new Api('rzp_live_PxHkewYEfs2yE2', 'VDwUKPH7tRbyyNONpOVdAO7c');

$amount = $_GET['amount'];  
$userid = $_GET['userid'];

// Fetch user details
$sql  = "SELECT name, email, mobile FROM users WHERE sn = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($name, $email, $contact);
$stmt->fetch();
$stmt->close();

$orderData = [
    'receipt'         => $userid, 
    'amount'          => $amount * 100,
    'currency'        => 'INR',
    'payment_capture' => 1 
];

$razorpayOrder = $api->order->create($orderData);
$order_id = $razorpayOrder['id'];
$displayAmount = $orderData['amount'] / 100;

// Insert record into tbl_razorpay_req before response
$payment_method = 'Razorpay';
$created_at = date('Y-m-d h:i:sa');
$payment_status = 0;  //0 for pending 

$sql_insert = "INSERT INTO razorpay_req (mobile, pay_id, method, amount, status, date) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_insert = $con->prepare($sql_insert);
$stmt_insert->bind_param("sssiss", $contact, $order_id, $payment_method, $displayAmount, $payment_status, $created_at);

if ($stmt_insert->execute()) {
} else {
    echo "Failed to insert Razorpay request into database: " . $con->error;
}
$stmt_insert->close();

$data = [
    'key'               => 'rzp_live_PxHkewYEfs2yE2',
    'amount'            => $orderData['amount'],
    'name'              => 'Dmbossonline',
    'description'       => 'Razorpay Transaction',
    'image'             => 'https://dmbossonline.com/img/dm.png',
    'prefill'           => [
        'name'          => $name,
        'email'         => $email,
        'contact'       => $contact,
    ],
    'order_id'          => $order_id,
    'theme'             => ['color' => '#F37254']
];

$jsonData = json_encode($data);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h1>Redirecting to payment...</h1>
    <script>
        var options = <?php echo $jsonData; ?>;

        options.handler = function (response) {
            var form = document.createElement("form");
            form.setAttribute("method", "POST");
            form.setAttribute("action", "response.php");

            var razorpay_payment_id = document.createElement("input");
            razorpay_payment_id.setAttribute("type", "hidden");
            razorpay_payment_id.setAttribute("name", "razorpay_payment_id");
            razorpay_payment_id.setAttribute("value", response.razorpay_payment_id);
            form.appendChild(razorpay_payment_id);

            var razorpay_order_id = document.createElement("input");
            razorpay_order_id.setAttribute("type", "hidden");
            razorpay_order_id.setAttribute("name", "razorpay_order_id");
            razorpay_order_id.setAttribute("value", response.razorpay_order_id);
            form.appendChild(razorpay_order_id);

            var razorpay_signature = document.createElement("input");
            razorpay_signature.setAttribute("type", "hidden");
            razorpay_signature.setAttribute("name", "razorpay_signature");
            razorpay_signature.setAttribute("value", response.razorpay_signature);
            form.appendChild(razorpay_signature);

            var amount = document.createElement("input");
            amount.setAttribute("type", "hidden");
            amount.setAttribute("name", "amount");
            amount.setAttribute("value", "<?php echo $amount; ?>");
            form.appendChild(amount);

            var userid = document.createElement("input");
            userid.setAttribute("type", "hidden");
            userid.setAttribute("name", "userid");
            userid.setAttribute("value", "<?php echo $userid; ?>");
            form.appendChild(userid);

            var contact = document.createElement("input");
            contact.setAttribute("type", "hidden");
            contact.setAttribute("name", "contact");
            contact.setAttribute("value", "<?php echo $contact; ?>");
            form.appendChild(contact);

            document.body.appendChild(form);
            form.submit();
        };

        var rzp1 = new Razorpay(options);
        window.onload = function() {
            rzp1.open();
        };
    </script>
</body>
</html>
