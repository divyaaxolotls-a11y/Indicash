<?php 
include('header.php'); 

// ================= UNIVERSAL UPDATE HANDLER =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updateFields = [];

    // Allowed columns for security
    $allowed = [
        // 'contact','whatsapp','telegram','wpchannel','upi',
        'contact','whatsapp','telegram','wpchannel','instagram','upi',
        'first_recharge_above','first_recharge_bonus',
        'payment_accept','payment_options','bonus',
        'min_recharge','max_recharge',
        'min_withdraw','withdraw_with_info',
        'withdraw_start','withdraw_end',
        'withdraw_status','processing',
        'withdraw_count_limit','withdraw_total_limit',
        'max_withdraw','min_bid','maintain_balance',
        'upi_type','login_option','forget_password'
    ];

    foreach ($_POST as $key => $value) {

        if (in_array($key, $allowed)) {
            $updateFields[] = "`$key`='".mysqli_real_escape_string($con,$value)."'";
        }
    }

    // Checkbox Handling
    if (isset($_POST['UpdateWithdrawUpi'])) {

        $upi_phonepe = isset($_POST['upi_phonepe']) ? 1 : 0;
        $upi_gpay    = isset($_POST['upi_gpay']) ? 1 : 0;
        $upi_paytm   = isset($_POST['upi_paytm']) ? 1 : 0;

        $updateFields[] = "upi_phonepe='$upi_phonepe'";
        $updateFields[] = "upi_gpay='$upi_gpay'";
        $updateFields[] = "upi_paytm='$upi_paytm'";
    }

    if (!empty($updateFields)) {

        $query = "UPDATE admin SET ".implode(',', $updateFields)." WHERE email='$idd'";
        mysqli_query($con, $query);

        echo "<script>alert('Updated Successfully!'); window.location.href='profile.php';</script>";
        exit();
    }
}


// ================= FETCH LIVE DATA =================
$sql = "SELECT * FROM admin WHERE email='$idd'";
$result = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($result);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Profile Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

<style>
* { box-sizing: border-box; }

body {
    background: #f4f6f9;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.container {
    max-width: 420px;
    margin: 20px auto;
    padding: 10px 15px;
}

.card {
    background: #ffffff;
    padding: 18px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    margin-bottom: 18px;
}

label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    display:block;
}

input {
    width: 100%;
    padding: 8px 10px;
    margin: 6px 0 14px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

button {
    background: #007bff;
    color: #fff;
    padding: 6px 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    width: fit-content !important;
    display: inline-block !important;
}

button:hover {
    background: #0056b3;
}

.setting-box label {
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
    display: block;
    margin-bottom: 6px;
}

.setting-box input,
.setting-box select {
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 13px;
}

.setting-box button {
    background: #1e88e5;
    color: #fff;
    padding: 6px 16px;
    border: none;
    border-radius: 5px;
    font-size: 13px;
    cursor: pointer;
    width: auto;
}

.setting-box button:hover {
    background: #1565c0;
}

/* Mobile Responsive */
@media (max-width: 600px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}
.row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.radio-group {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: normal;
    text-transform: none;
    margin: 0;
}

.radio-group input[type="radio"] {
    margin: 0;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: normal;
    text-transform: none;
}
.section-title {
    text-align: left !important;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 10px;
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 12px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: normal;
    text-transform: none;
}

.checkbox-group input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

</style>

</head>

<body>

<div class="container">

    <!-- CONTACT DETAILS -->
    <form method="POST">
        <div class="card">
            <label>Phone Number</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>">
    
            <label>WhatsApp Number</label>
            <!-- FIXED: Changed from $whatsapp_val to $user['whatsapp'] -->
            <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?>">
    
            <label>Telegram</label>
            <!-- FIXED: Changed from $telegram_val to $user['telegram'] -->
            <input type="text" name="telegram" value="<?php echo htmlspecialchars($user['telegram'] ?? ''); ?>">
    
            <label>Whatsapp Channel</label>
            <!-- FIXED: Changed from $wpchannel_val to $user['wpchannel'] -->
            <input type="text" name="wpchannel" value="<?php echo htmlspecialchars($user['wpchannel'] ?? ''); ?>">
    
            <!--<label>Instagram</label>-->
            <!-- ADDED: Instagram Input -->
            <!--<input type="text" name="instagram" value="<?php echo htmlspecialchars($user['instagram'] ?? ''); ?>">-->
    
            <button type="submit" name="UpdateContact">Update</button>
        </div>
    </form>

    <!-- UPI -->
    <form method="POST">
        <div class="card">
            <label>UPI</label>
            <input type="text" name="upi" value="<?php echo htmlspecialchars($upi_val); ?>">
            <button type="submit" name="UpdateUpi">Update</button>
        </div>
    </form>

    <!-- MIN & MAX RECHARGE -->
    <!-- SETTINGS SECTION -->
<form method="POST">
<div class="card">

    <!-- Row 1 -->
    <div class="row-2">
        <div class="setting-box">
            <label>FIRST RECHARGE ABOVE</label>
            <input type="text" name="first_recharge_above" value="<?php echo $user['first_recharge_above'] ?? ''; ?>">
            <button type="submit" name="UpdateFirstAbove">Update</button>
        </div>

        <div class="setting-box">
            <label>FIRST RECHARGE BONUS</label>
            <input type="text" name="first_recharge_bonus" value="<?php echo $user['first_recharge_bonus'] ?? ''; ?>">
            <button type="submit" name="UpdateFirstBonus">Update</button>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="row-2">
        <div class="setting-box">
            <label>PAYMENT ACCEPT</label>
            <select name="payment_accept">
                <option value="IMB">IMB</option>
            </select>
            <button type="submit" name="UpdatePaymentAccept">Update</button>
        </div>

        <div class="setting-box">
            <label>PAYMENT OPTIONS</label>
            <input type="text" name="payment_options" value="<?php echo $user['payment_options'] ?? ''; ?>">
            <button type="submit" name="UpdatePaymentOptions">Update</button>
        </div>
    </div>

    <!-- Bonus Full Width -->
    <div class="setting-box">
        <label>BONUS</label>
        <input type="text" name="bonus" value="<?php echo $user['bonus'] ?? ''; ?>">
        <button type="submit" name="UpdateBonus">Update</button>
    </div>

</div>

</form>


<form method="POST">
<div class="card">

    <div class="row-2">
        <div class="setting-box">
            <label>MIN RECHARGE</label>
            <input type="text" name="min_recharge" value="<?php echo $user['min_recharge'] ?? ''; ?>">
            <button type="submit" name="UpdateMin">Update</button>
        </div>

        <div class="setting-box">
            <label>MAX RECHARGE</label>
            <input type="text" name="max_recharge" value="<?php echo $user['max_recharge'] ?? ''; ?>">
            <button type="submit" name="UpdateMax">Update</button>
        </div>
    </div>

</div>
</form>

<form method="POST">
<div class="card">

    <!-- ROW 1 -->
    <div class="row-2">

        <div class="setting-box">
            <label>MIN WITHDRAW</label>
            <input type="text" name="min_withdraw" value="<?php echo $user['min_withdraw'] ?? ''; ?>">
            <button type="submit" name="UpdateMinWithdraw">Update</button>
        </div>

        <div class="setting-box">
    <label>WITHDRAW WITH INFO</label>

    <div class="radio-group">

        <label>
            <input type="radio" name="withdraw_with_info" value="ON"
            <?php if(($user['withdraw_with_info'] ?? '')=='ON') echo 'checked'; ?>>
            ON
        </label>

        <label>
            <input type="radio" name="withdraw_with_info" value="OFF"
            <?php if(($user['withdraw_with_info'] ?? '')=='OFF') echo 'checked'; ?>>
            OFF
        </label>

    </div>

    <button type="submit" name="UpdateWithdrawInfo">Update</button>
</div>


    </div>

    <!-- ROW 2 -->
    <div class="row-2">

        <div class="setting-box">
            <label>MONEY WITHDRAW START</label>
            <input type="time" name="withdraw_start" value="<?php echo $user['withdraw_start'] ?? ''; ?>">
            <button type="submit" name="UpdateWithdrawStart">Update</button>
        </div>

        <div class="setting-box">
            <label>MONEY WITHDRAW END</label>
            <input type="time" name="withdraw_end" value="<?php echo $user['withdraw_end'] ?? ''; ?>">
            <button type="submit" name="UpdateWithdrawEnd">Update</button>
        </div>

    </div>

    <!-- ROW 3 -->
    <div class="row-2">

        <div class="setting-box">
    <label>WITHDRAW ON OFF</label>
    <div class="radio-group">
        <label>
            <input type="radio" name="withdraw_status" value="ON"
            <?php if(($user['withdraw_status'] ?? '')=='ON') echo 'checked'; ?>>
            ON
        </label>

        <label>
            <input type="radio" name="withdraw_status" value="OFF"
            <?php if(($user['withdraw_status'] ?? '')=='OFF') echo 'checked'; ?>>
            OFF
        </label>
    </div>
    <button type="submit" name="UpdateWithdrawStatus">Update</button>
</div>


        <div class="setting-box">
            <label>PROCESSING</label>
            <div class="radio-group">
                <input type="radio" name="processing" value="ON"
<?php if(($user['processing'] ?? '')=='ON') echo 'checked'; ?>>
                <input type="radio" name="processing" value="OFF"
<?php if(($user['processing'] ?? '')=='OFF') echo 'checked'; ?>>

            </div>
            <button type="submit" name="UpdateProcessing">Update</button>
        </div>

    </div>

    <!-- ROW 4 -->
    <div class="row-2">

        <div class="setting-box">
            <label>WITHDRAW COUNT LIMIT</label>
            <input type="text" name="withdraw_count_limit" value="<?php echo $user['withdraw_count_limit'] ?? ''; ?>">
            <button type="submit" name="UpdateWithdrawCount">Update</button>
        </div>

        <div class="setting-box">
            <label>WITHDRAW TOTAL AMOUNT LIMIT</label>
            <input type="text" name="withdraw_total_limit" value="<?php echo $user['withdraw_total_limit'] ?? ''; ?>">
            <button type="submit" name="UpdateWithdrawTotal">Update</button>
        </div>

    </div>

    <!-- ROW 5 -->
    <div class="setting-box">
        <label>MAX WITHDRAW</label>
        <input type="text" name="max_withdraw" value="<?php echo $user['max_withdraw'] ?? ''; ?>">
        <button type="submit" name="UpdateMaxWithdraw">Update</button>
    </div>

</div>
</form>
<form method="POST">
<div class="card">

    <!-- ROW 1 -->
    <div class="row-2">
        <div class="setting-box">
            <label>MIN BID</label>
            <input type="text" name="min_bid" value="<?php echo $user['min_bid'] ?? ''; ?>">
            <button type="submit" name="UpdateMinBid">Update</button>
        </div>

        <div class="setting-box">
            <label>MAINTAIN BALANCE</label>
            <input type="text" name="maintain_balance" value="<?php echo $user['maintain_balance'] ?? ''; ?>">
            <button type="submit" name="UpdateMaintainBalance">Update</button>
        </div>
    </div>

    <!-- ROW 2 -->
    <div class="row-2">
        <div class="setting-box">
            <label>UPI TYPE</label>
            <select name="upi_type">
                
                <option value="UPI" <?php if(($user['upi_type'] ?? '')=='UPI') echo 'selected'; ?>>UPI</option>
                <option value="GATEWAY" <?php if(($user['upi_type'] ?? '')=='GATEWAY') echo 'selected'; ?>>GATEWAY</option>
                <option value="IMB" <?php if(($user['upi_type'] ?? '')=='IMB') echo 'selected'; ?>>IMB</option>

            </select>
            <button type="submit" name="UpdateUpiType">Update</button>
        </div>

        <div class="setting-box">
            <label>LOGIN OPTION</label>
            <select name="login_option">
                <option value="PASSWORD">PASSWORD</option>
                <option value="OTP">OTP</option>
            </select>
            <button type="submit" name="UpdateLoginOption">Update</button>
        </div>
    </div>

    <!-- ROW 3 (Single Column) -->
    <div class="row-2">
    <div class="setting-box">
        <label>FORGET PASSWORD</label>
        <select name="forget_password">
            <option value="PASSWORD">PASSWORD</option>
            <option value="OTP">OTP</option>
        </select>
        <button type="submit" name="UpdateForgetPassword">Update</button>
    </div>

    <div></div> <!-- empty column for balance -->
</div>

</form>
</div>

<form method="POST">
<div class="card">

    <div class="setting-box">
        <label class="section-title">Withdraw Hide Show Upi</label>

        <div class="checkbox-group">

            <div class="checkbox-row">
                <span>PhonePe :</span>
                <input type="checkbox" name="upi_phonepe" value="1"
<?php if(($user['upi_phonepe'] ?? 0)==1) echo 'checked'; ?>>

            </div>

            <div class="checkbox-row">
                <span>GPay :</span>
                <input type="checkbox" name="upi_gpay" value="1"
<?php if(($user['upi_gpay'] ?? 0)==1) echo 'checked'; ?>>

            </div>

            <div class="checkbox-row">
                <span>Paytm :</span>
                <input type="checkbox" name="upi_paytm" value="1"
<?php if(($user['upi_paytm'] ?? 0)==1) echo 'checked'; ?>>

            </div>

        </div>

        <button type="submit" name="UpdateWithdrawUpi">Update</button>
    </div>

</div>
</form>



</div>

</body>
</html>
