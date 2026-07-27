<?php
header("Content-Type: application/json");
include "con.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

// ===== INPUT PARAMETERS =====
$mobile  = $_POST['mobile']  ?? '';
$session = $_POST['session'] ?? '';
$amount  = $_POST['amount']  ?? '';
$mode    = $_POST['mode']    ?? 'bank'; // bank, paytm, phonepe, gpay
$payment_number = $_POST['payment_number'] ?? ''; // New: The number for wallets

// Bank Details (Optional if using wallet)
$ac      = $_POST['ac']      ?? '';
$ifsc    = $_POST['ifsc']    ?? '';
$holder  = $_POST['holder']  ?? '';

// ===== BASIC VALIDATION =====
if ($mobile == '' || $session == '' || $amount == '') {
    echo json_encode(["success" => "0", "msg" => "Required parameters missing"]);
    exit;
}

// ===== USER AUTH CHECK =====
$auth = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' AND session='$session'");

if (mysqli_num_rows($auth) == 0) {
    echo json_encode(["success" => "0", "msg" => "Unauthorized user"]);
    exit;
}

$user = mysqli_fetch_assoc($auth);
$wallet = $user['wallet'];

// ===== WALLET CHECK =====
if ($wallet < $amount) {
    echo json_encode(["success" => "0", "msg" => "Insufficient wallet balance"]);
    exit;
}

// ===== PREPARE WALLET COLUMNS =====
$paytm   = ($mode == 'paytm')   ? "'$payment_number'" : "NULL";
$phonepe = ($mode == 'phonepe') ? "'$payment_number'" : "NULL";
$gpay    = ($mode == 'gpay')    ? "'$payment_number'" : "NULL";

// ===== START TRANSACTION =====
mysqli_begin_transaction($con);
$only_date = date('Y-m-d');

try {
    // ===== INSERT WITHDRAW REQUEST (Updated with wallet columns) =====
    $insert = mysqli_query(
        $con,
        "INSERT INTO withdraw_requests
        (mobile, amount, mode, ac, ifsc, holder, paytm, phonepe, gpay, status, created_at, date)
        VALUES
        ('$mobile', '$amount', '$mode', '$ac', '$ifsc', '$holder', $paytm, $phonepe, $gpay, '0', NOW(), '$only_date')"
    );

    if (!$insert) {
        throw new Exception(mysqli_error($con));
    }

    // ===== UPDATE WALLET =====
    $update = mysqli_query($con, "UPDATE users SET wallet = wallet - $amount WHERE mobile='$mobile'");
    if (!$update) { throw new Exception(mysqli_error($con)); }

    mysqli_commit($con);

    echo json_encode([
        "success" => "1",
        "msg" => "Withdraw request submitted successfully",
        "wallet" => ($wallet - $amount)
    ]);

} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(["success" => "0", "msg" => "Withdraw failed", "error" => $e->getMessage()]);
}