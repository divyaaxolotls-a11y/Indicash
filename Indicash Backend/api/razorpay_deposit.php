<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Kolkata');

include "con.php";

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required input
if (
    !isset($data['mobile']) || empty($data['mobile']) ||
    !isset($data['amount']) || empty($data['amount']) ||
    !isset($data['pay_id']) || empty($data['pay_id']) ||
    !isset($data['status']) || empty($data['status'])
) {
    echo json_encode([
        "status" => false,
        "message" => "mobile, amount, pay_id, and status are required"
    ]);
    exit;
}

// Escape input
$mobile = $con->real_escape_string($data['mobile']);
$amount = (int) $data['amount'];
$pay_id = $con->real_escape_string($data['pay_id']);
$razorpay_status = strtolower(trim($data['status'])); // e.g., 'created', 'failed', etc.
$razorpay_response = isset($data['razorpay_response']) ? $con->real_escape_string(json_encode($data['razorpay_response'])) : NULL;

$method = 'Razorpay';
$created_at = date("Y-m-d H:i:s");
$updated_at = date("Y-m-d H:i:s");
$created_ats = date('Y-m-d h:i:sa');

// Database status
$db_status = 1;

// Insert into auto_deposits
$sql = "INSERT INTO auto_deposits (mobile, amount, pay_id, method, status, created_at,date,updated_at, razorpay_response) 
        VALUES ('$mobile', $amount, '$pay_id', '$method', $db_status, '$created_at', '$created_ats','$updated_at', '$razorpay_response')";

if ($con->query($sql) === TRUE) {
    $insert_id = $con->insert_id;


$remark = 'Points added by user using Razorpay ' . $mobile . ' and amount ' . $amount;
$transaction_type = 1;
$created_at_timestamp = time(); // Current Unix timestamp
$dated_on = date('Y-m-d'); // Current date

$sql_insert_transaction = "INSERT INTO transactions (user, amount, type, remark, created_at) 
                           VALUES (?, ?, ?, ?, ?)";

if ($stmt_insert_transaction = $con->prepare($sql_insert_transaction)) {
    $stmt_insert_transaction->bind_param("siiss", $mobile, $amount, $transaction_type, $remark, $created_at_timestamp);

    if ($stmt_insert_transaction->execute()) {
        // Successfully inserted transaction
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Transaction insert failed",
            "error" => $stmt_insert_transaction->error
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Transaction prepare failed",
        "error" => $con->error
    ]);
}

        // Update wallet
        $userCheck = "SELECT wallet FROM users WHERE mobile = '$mobile'";
        $userResult = $con->query($userCheck);

        if ($userResult->num_rows > 0) {
          // Perform the update
        $con->query("UPDATE users SET wallet = wallet + $amount WHERE mobile = '$mobile'");

        // Fetch the updated wallet balance
        $walletResult = $con->query("SELECT wallet FROM users WHERE mobile = '$mobile'");
        $walletRow = $walletResult->fetch_assoc();
        $updated_wallet = $walletRow['wallet'];

        echo json_encode([
            "status" => true,
            "message" => "Payment created, wallet updated",
            "insert_id" => $insert_id,
            "wallet" => $updated_wallet
        ]);
    }  else {
            echo json_encode([
                "status" => false,
                "message" => "Payment inserted, but user not found"
            ]);
        }
   
} else {
    echo json_encode([
        "status" => false,
        "message" => "Insert failed",
        "error" => $con->error
    ]);
}

$con->close();
?>
