<?php
include "db.php";

/* ================= LOG WEBHOOK ================= */
file_put_contents(
    "webhook_log.txt",
    date("Y-m-d H:i:s") . " " . json_encode($_POST) . PHP_EOL,
    FILE_APPEND
);

/* ================= READ DATA ================= */
$order_id = $_POST['order_id'] ?? '';
$status   = $_POST['status'] ?? '';
$utr      = $_POST['result']['utr'] ?? '';

if ($order_id === '' || $status === '') {
    http_response_code(200);
    echo "INVALID DATA";
    exit;
}

/* ================= FETCH PAYMENT ================= */
$paymentRes = mysqli_query($conn, "
    SELECT status, amount, mobile 
    FROM payments 
    WHERE order_id = '$order_id'
");

if (mysqli_num_rows($paymentRes) === 0) {
    http_response_code(200);
    echo "ORDER NOT FOUND";
    exit;
}

$payment = mysqli_fetch_assoc($paymentRes);

/* ================= PROCESS SUCCESS ================= */
if ($status === "SUCCESS" && $payment['status'] !== "SUCCESS") {

    $amount = (int)$payment['amount'];
    $mobile = $payment['mobile'];

    mysqli_begin_transaction($conn);

    try {
        // 1️⃣ Update payment status
        mysqli_query($conn, "
            UPDATE payments 
            SET status='SUCCESS', payment_id='$utr'
            WHERE order_id='$order_id'
        ");
        // Get wallet before
        $q = mysqli_query($conn,"
        SELECT wallet
        FROM users
        WHERE mobile='$mobile'
        LIMIT 1
        ");
        
        $row = mysqli_fetch_assoc($q);
        
        $wallet_before = (float)$row['wallet'];
        $wallet_after  = $wallet_before + $amount;
        // 2️⃣ Credit wallet
        mysqli_query($conn, "
            UPDATE users 
            SET wallet = wallet + $amount 
            WHERE mobile = '$mobile'
        ");
        $remark = "Added ,omey to wallet by IMB gateway | Order:$order_id | UTR:$utr";

        mysqli_query($conn,"
        INSERT INTO transactions
        (
            user,
            amount,
            wallet_before,
            wallet_after,
            type,
            remark,
            created_at
        )
        VALUES
        (
            '$mobile',
            '$amount',
            '$wallet_before',
            '$wallet_after',
            '1',
            '$remark',
            NOW()
        )
        ");
        mysqli_commit($conn);

    } catch (Exception $e) {
        mysqli_rollback($conn);
    }

}
/* ================= PROCESS FAILURE ================= */
elseif ($status !== "SUCCESS") {

    mysqli_query($conn, "
        UPDATE payments 
        SET status='FAILED'
        WHERE order_id='$order_id'
    ");
}

/* ================= ALWAYS RESPOND OK ================= */
http_response_code(200);
echo "OK";














