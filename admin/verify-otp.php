<?php
include('config.php');
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
// If OTP session is not set or expired, redirect to login page
if (!isset($_SESSION['otp']) || time() - $_SESSION['otp_time'] > 300) { // 300 seconds = 5 minutes
    // OTP expired or not set
    session_destroy();
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit_otp'])) {
    $enteredOtp = $_POST['otp'];

    if ($enteredOtp == $_SESSION['otp']) {
        // OTP verified successfully, log in the user
// $_SESSION['userID'] = $_SESSION['email']; // Store user email in session
// echo "<script>alert('" . $_SESSION['userID'] . "');</script>";


        // Log the successful login action
        $remark = 'Admin successfully logged in with OTP verification';
        log_action($remark);

        // Redirect to the dashboard
        header('Location: dashboard1.php');
        exit();
    } else {
            // session_destroy();

        echo "<script>alert('Invalid OTP. Please try again.');</script>";
            //   header('Location: index1.php');
        // exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin OTP Verification</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .otp-box {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .otp-box .logo {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .otp-box p {
            color: #666;
            font-size: 14px;
        }

        .otp-box .input-group {
            margin-bottom: 15px;
        }

        .otp-box input {
            height: 50px;
            font-size: 16px;
            border-radius: 8px;
            text-align: center;
            letter-spacing: 4px;
        }

        .btn-primary {
            background: #667eea;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="otp-box">
        <div class="logo">
            <i class="fas fa-user-shield"></i> Admin Panel
        </div>
        <p class="mt-2">Enter the OTP sent to your mobile number</p>

        <!-- OTP Form -->
        <form method="POST">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required maxlength="6"/>
            </div>

            <button type="submit" name="submit_otp" class="btn btn-primary w-100">Verify OTP</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
