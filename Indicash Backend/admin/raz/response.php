<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('vendor/autoload.php');
use Razorpay\Api\Api;

include('../config.php');
date_default_timezone_set('Asia/Kolkata');

// $api = new Api('rzp_live_16EiPycQfSmnxY', '6P2us9b8vldznWjthAGo3vrC');
$api = new Api('rzp_live_PxHkewYEfs2yE2', 'VDwUKPH7tRbyyNONpOVdAO7c');

$success = true;
$error = "Payment Failed";

if (isset($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_payment_id'])) {
    try {
        $attributes = [
            'razorpay_order_id'   => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature'  => $_POST['razorpay_signature']
        ];

        // Verify the payment signature
        $api->utility->verifyPaymentSignature($attributes);

        $payment_id = $_POST['razorpay_payment_id'];
        $order_id = $_POST['razorpay_order_id'];
        $amount = $_POST['amount'];
        $contact = $_POST['contact'];
        $userid = $_POST['userid'];
        $payment_method = 'Razorpay';  
        $payment_status = '1';  
        $created_at = date('Y-m-d h:i:sa');

        // Check if order_id exists in razorpay_req table
        $sql_check_order = "SELECT COUNT(*) FROM razorpay_req WHERE pay_id = ?";
        if ($stmt_check_order = $con->prepare($sql_check_order)) {
            $stmt_check_order->bind_param("s", $order_id);
            $stmt_check_order->execute();
            $stmt_check_order->bind_result($order_count);
            $stmt_check_order->fetch();
            $stmt_check_order->close();

            if ($order_count > 0) {
                // Update the status in razorpay_req table to 1
                $sql_update_status = "UPDATE razorpay_req SET status = 1 WHERE pay_id = ?";
                if ($stmt_update_status = $con->prepare($sql_update_status)) {
                    $stmt_update_status->bind_param("s", $order_id);
                    if ($stmt_update_status->execute()) {
                        // Proceed with inserting into auto_deposits
                        $sql_insert = "INSERT INTO auto_deposits (mobile, pay_id, method, amount, status, date) VALUES (?, ?, ?, ?, ?, ?)";
                        if ($stmt_insert = $con->prepare($sql_insert)) {
                            $stmt_insert->bind_param("sssiss", $contact, $payment_id, $payment_method, $amount, $payment_status, $created_at);
                            if ($stmt_insert->execute()) {
                                // Update wallet balance for the current user
                                $sql_update_wallet = "UPDATE users SET wallet = wallet + ? WHERE sn = ?";
                                if ($stmt_update_wallet = $con->prepare($sql_update_wallet)) {
                                    $stmt_update_wallet->bind_param("ii", $amount, $userid);
                                    if ($stmt_update_wallet->execute()) {
                                        
                                        // Insert the transaction into the transactions table
                                        $transaction_type = 1; // Type is 1 for payment
                                        $remark = 'Points added by user using Razorpay';
                                        $created_at_timestamp = time(); // Current Unix timestamp
                                        $dated_on = date('Y-m-d'); // Current date
                                        
            ////////////////////////////
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $remark = 'Points added by user using Razorpay '. $contact.' and amount ' . $amount;
            $insert_stmt = $con->prepare("INSERT INTO login_logs (user_email, ip_address, user_agent, remark) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $contact, $ip_address, $user_agent, $remark);
            $insert_stmt->execute();
            $insert_stmt->close();
            //////////////////////////////

                                        $sql_insert_transaction = "INSERT INTO transactions (user, amount, type, remark, created_at, dated_on) VALUES (?, ?, ?, ?, ?, ?)";
                                        if ($stmt_insert_transaction = $con->prepare($sql_insert_transaction)) {
                                            $stmt_insert_transaction->bind_param("siisss", $contact, $amount, $transaction_type, $remark, $created_at_timestamp, $dated_on);
                                            if ($stmt_insert_transaction->execute()) {
                                                // Transaction successfully logged in transactions table
                                            } else {
                                                echo "<h3>Failed to insert transaction record. MySQL Error: " . $con->error . "</h3>";
                                            }
                                            $stmt_insert_transaction->close();
                                        } else {
                                            echo "<h3>Error preparing the insert transaction query. MySQL Error: " . $con->error . "</h3>";
                                        }

                                        // Fetch referral code of the current user and handle referral bonus logic
                                        $sql_refcode = "SELECT refcode FROM users WHERE sn = ?";
                                        if ($stmt_refcode = $con->prepare($sql_refcode)) {
                                            $stmt_refcode->bind_param("i", $userid);
                                            $stmt_refcode->execute();
                                            $stmt_refcode->bind_result($refcode);
                                            
                                            if ($stmt_refcode->fetch()) {
                                                $stmt_refcode->close();

                                                // Find the referrer (user with the matching ref_id)
                                                $sql_referrer = "SELECT sn FROM users WHERE ref_id = ?";
                                                if ($stmt_referrer = $con->prepare($sql_referrer)) {
                                                    $stmt_referrer->bind_param("s", $refcode);
                                                    $stmt_referrer->execute();
                                                    $stmt_referrer->bind_result($referrer_id);
                                                    
                                                    if ($stmt_referrer->fetch()) {
                                                        $stmt_referrer->close();
                                                        
                                                        // Fetch referral bonus amount from settings
                                                        $sql_bonus = "SELECT data FROM settings WHERE data_key = 'ref_bonus'";
                                                        $result_bonus = $con->query($sql_bonus);
                                                        if ($result_bonus && $row_bonus = $result_bonus->fetch_assoc()) {
                                                            $ref_bonus = $row_bonus['data'];

                                                            // Update the wallet of the referrer
                                                            $sql_update_referrer = "UPDATE users SET wallet = wallet + ? WHERE sn = ?";
                                                            if ($stmt_update_referrer = $con->prepare($sql_update_referrer)) {
                                                                $stmt_update_referrer->bind_param("ii", $ref_bonus, $referrer_id);
                                                                if ($stmt_update_referrer->execute()) {
                                                                    // Insert referral bonus transaction in the transactions table
                                                                    $bonus_remark = 'Referral bonus';
                                                                    $bonus_transaction_type = 1; // Type 2 for referral bonus

                                                                    $sql_insert_bonus_transaction = "INSERT INTO transactions (user, amount, type, remark, created_at, dated_on) VALUES (?, ?, ?, ?, ?, ?)";
                                                                    if ($stmt_insert_bonus_transaction = $con->prepare($sql_insert_bonus_transaction)) {
                                                                        $stmt_insert_bonus_transaction->bind_param("siisss", $referrer_id, $ref_bonus, $bonus_transaction_type, $bonus_remark, $created_at_timestamp, $dated_on);
                                                                        if ($stmt_insert_bonus_transaction->execute()) {
                                                                            // Referral bonus successfully logged in transactions table
                                                                        } else {
                                                                            echo "<h3>Failed to insert referral bonus transaction. MySQL Error: " . $con->error . "</h3>";
                                                                        }
                                                                        $stmt_insert_bonus_transaction->close();
                                                                    } else {
                                                                        echo "<h3>Error preparing referral bonus transaction query. MySQL Error: " . $con->error . "</h3>";
                                                                    }
                                                                } else {
                                                                    echo "<h3>Failed to update referrer's wallet balance. MySQL Error: " . $con->error . "</h3>";
                                                                }
                                                                $stmt_update_referrer->close();
                                                            } else {
                                                                echo "<h3>Error preparing the update referrer wallet query. MySQL Error: " . $con->error . "</h3>";
                                                            }
                                                        } else {
                                                            echo "<h3>Failed to fetch referral bonus from settings. MySQL Error: " . $con->error . "</h3>";
                                                        }
                                                    } else {
                                                        echo "<h3>No referrer found for this user.</h3>";
                                                    }
                                                    $stmt_referrer->close();
                                                } else {
                                                    echo "<h3>Failed to prepare query for finding referrer. MySQL Error: " . $con->error . "</h3>";
                                                }
                                            } else {
                                                echo "<h3>Failed to fetch referral code for the current user. MySQL Error: " . $con->error . "</h3>";
                                            }
                                        } else {
                                            echo "<h3>Error preparing the query for fetching referral code. MySQL Error: " . $con->error . "</h3>";
                                        }

                                        // Display SweetAlert
                                        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                                        echo "<script>
                                            Swal.fire({
                                                title: 'Payment Successful!',
                                                text: 'Payment ID: " . htmlspecialchars($payment_id) . "\\nOrder ID: " . htmlspecialchars($order_id) . "\\nAmount: " . htmlspecialchars($amount) . " INR',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = 'success.php';
                                                }
                                            });
                                        </script>";
                                    } else {
                                        echo "<h3>Error preparing the update wallet query for the current user. MySQL Error: " . $con->error . "</h3>";
                                    }
                                    $stmt_update_wallet->close();
                                } else {
                                    echo "<h3>Error preparing the update wallet query. MySQL Error: " . $con->error . "</h3>";
                                }
                            } else {
                                echo "<h3>Failed to insert payment details into the database. MySQL Error: " . $con->error . "</h3>";
                            }
                            $stmt_insert->close();
                        } else {
                            echo "<h3>Error preparing the insert payment query. MySQL Error: " . $con->error . "</h3>";
                        }
                    } else {
                        echo "<h3>Failed to update razorpay_req status. MySQL Error: " . $con->error . "</h3>";
                    }
                    $stmt_update_status->close();
                } else {
                    echo "<h3>Error preparing the update status query. MySQL Error: " . $con->error . "</h3>";
                }
            } else {
                echo "<h3>Order ID not found in razorpay_req table.</h3>";
            }
        } else {
            echo "<h3>Error preparing the order check query. MySQL Error: " . $con->error . "</h3>";
        }
    } catch (Exception $e) {
        $success = false;
        $error = 'Payment Error: ' . $e->getMessage();
    }
} else {
    $success = false;
    $error = 'Payment Failed: Missing payment ID';
}
?>
