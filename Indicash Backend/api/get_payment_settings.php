<?php
include "con.php"; 

header('Content-Type: application/json');

// 1. Fetch settings from the 'admin' table
$query = mysqli_query($con, "SELECT upi_type, upi, upi_phonepe, upi_gpay, upi_paytm FROM admin LIMIT 1");
$row = mysqli_fetch_assoc($query);

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Settings not found"]);
    exit;
}

// 2. Prepare the basic response
$response = [
    "status" => "success",
    "upi_type" => $row['upi_type'] // "UPI", "GATEWAY", or "IMB"
];

// 3. Add data based on the type
if ($row['upi_type'] == "UPI") {
    // Only send UPI ID and Toggles when type is UPI
    $response['upi_id'] = $row['upi'];
    $response['qr_code'] = "https://test.apluscrm.in/assets/image/qr_code.jpeg";
} 
elseif ($row['upi_type'] == "IMB") {
    $response['imb__url'] = "https://test.apluscrm.in/Payment_Gateway";
    $response['qr_code'] = "https://test.apluscrm.in/assets/image/qr_code.jpeg";
} 
elseif ($row['upi_type'] == "GATEWAY") {
    $response['gateway_url'] = "https://test.apluscrm.in";
        $response['qr_code'] = "https://test.apluscrm.in/assets/image/qr_code.jpeg";
}

echo json_encode($response);
?>