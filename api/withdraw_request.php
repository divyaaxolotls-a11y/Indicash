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
$mode    = $_POST['mode']    ?? 'bank'; // bank / upi
$ac      = $_POST['ac']      ?? '';
$ifsc    = $_POST['ifsc']    ?? '';
$holder  = $_POST['holder']  ?? '';

// ===== BASIC VALIDATION =====
if ($mobile == '' || $session == '' || $amount == '') {
    echo json_encode([
        "success" => "0",
        "msg" => "Required parameters missing"
    ]);
    exit;
}

// ===== USER AUTH CHECK =====
$auth = mysqli_query(
    $con,
    "SELECT wallet FROM users WHERE mobile='$mobile' AND session='$session'"
);

if (mysqli_num_rows($auth) == 0) {
    echo json_encode([
        "success" => "0",
        "msg" => "Unauthorized user"
    ]);
    exit;
}

$user = mysqli_fetch_assoc($auth);
$wallet = $user['wallet'];

// ===== WALLET CHECK =====
if ($wallet < $amount) {
    echo json_encode([
        "success" => "0",
        "msg" => "Insufficient wallet balance"
    ]);
    exit;
}

// ===== START TRANSACTION =====
mysqli_begin_transaction($con);
$only_date = date('Y-m-d');

try {
    // ===== INSERT WITHDRAW REQUEST =====
    $insert = mysqli_query(
        $con,
        "INSERT INTO withdraw_requests
        (mobile, amount, mode, ac, ifsc, holder, status, created_at, date)
        VALUES
        ('$mobile', '$amount', '$mode', '$ac', '$ifsc', '$holder', '0', NOW(), '$only_date')"
    );

    if (!$insert) {
        throw new Exception(mysqli_error($con));
    }

    // ===== UPDATE WALLET =====
    $update = mysqli_query(
        $con,
        "UPDATE users SET wallet = wallet - $amount WHERE mobile='$mobile'"
    );

    if (!$update) {
        throw new Exception(mysqli_error($con));
    }

    // ===== COMMIT =====
    mysqli_commit($con);

    // ===== GET UPDATED WALLET =====
    $bal = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile'")
    );

    echo json_encode([
        "success" => "1",
        "msg" => "Withdraw request submitted successfully",
        "wallet" => $bal['wallet']
    ]);

} catch (Exception $e) {
    mysqli_rollback($con);

    echo json_encode([
        "success" => "0",
        "msg" => "Withdraw failed",
        "error" => $e->getMessage()
    ]);
}
