<?php 
include('header.php'); 

// --- 1. ORIGINAL DATABASE LOGIC ---
$user_id = mysqli_real_escape_string($con, $_GET['userID']);
$stamp = time();
$main_admin_mail = "admin@gmail.com"; 

// Handle Profile/Bank Updates
// if (isset($_POST['updateField'])) {
//     $fieldName = $_POST['field_name'];
//     $newValue = htmlspecialchars($_POST['field_value'], ENT_QUOTES, 'UTF-8');
//     $target = ($_POST['type'] == 'bank') ? 'bank_history' : 'users';
//     $col = ($_POST['type'] == 'bank') ? 'user' : 'mobile';

//     $stmt = $con->prepare("UPDATE $target SET $fieldName = ? WHERE $col = ?");
//     $stmt->bind_param("ss", $newValue, $user_id);
//     if ($stmt->execute()) {
//         echo "<script>window.location.href='user-profile.php?userID=$user_id&msg=Updated';</script>";
//     }
//     $stmt->close();
// }

// Handle Profile/Bank Updates
if (isset($_POST['updateField'])) {
    $fieldName = $_POST['field_name'];
    $newValue = htmlspecialchars($_POST['field_value'], ENT_QUOTES, 'UTF-8');
    $type = $_POST['type'];

    if ($type == 'bank') {
        // 1. Check if a record exists for this user in bank_history
        $check = mysqli_query($con, "SELECT sn FROM bank_history WHERE user = '$user_id'");
        
        if (mysqli_num_rows($check) > 0) {
            // Record exists, perform UPDATE
            $stmt = $con->prepare("UPDATE bank_history SET $fieldName = ? WHERE user = ?");
            $stmt->bind_param("ss", $newValue, $user_id);
        } else {
            // Record doesn't exist, perform INSERT
            // This assumes 'user' is the mobile number and other fields like 'mode' have defaults
            $stmt = $con->prepare("INSERT INTO bank_history (user, $fieldName, mode) VALUES (?, ?, 'bank')");
            $stmt->bind_param("ss", $user_id, $newValue);
        }
    } else {
        // Standard user profile update
        // if ($fieldName == 'password') {
        //     // $newValue = md5($newValue); 
        // }

        // $stmt = $con->prepare("UPDATE users SET $fieldName = ? WHERE mobile = ?");
        // $stmt->bind_param("ss", $newValue, $user_id);
        if ($fieldName == 'password') {
            $plainPass = $newValue;         // store plain password
            $newValue = md5($newValue);     // hash for password column
            $stmt = $con->prepare("UPDATE users SET password=?, plain_password=? WHERE mobile=?");
            $stmt->bind_param("sss", $newValue, $plainPass, $user_id);
        } else {
            $stmt = $con->prepare("UPDATE users SET $fieldName = ? WHERE mobile = ?");
            $stmt->bind_param("ss", $newValue, $user_id);
        }
    }

    if ($stmt->execute()) {
        echo "<script>window.location.href='user-profile.php?userID=$user_id&msg=Updated';</script>";
    } else {
        echo "<script>alert('Database Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle Game Block List Saving
if (isset($_POST['save_blocked_games'])) {
    $blocked_array = isset($_POST['blocked_markets']) ? $_POST['blocked_markets'] : [];
    $blocked_string = implode(',', $blocked_array);
    mysqli_query($con, "UPDATE users SET blocked_markets = '$blocked_string' WHERE mobile = '$user_id'");
    echo "<script>alert('Block list saved successfully!'); window.location.href='user-profile.php?userID=$user_id';</script>";
}

// Points Logic
// if(isset($_POST['AddPoints']) && $_POST['csrf_token'] === $_SESSION['csrf_token']){
//     $pAdd = $_POST['pointsAdd'];
//     $user_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user_id'");
//     $user_data = mysqli_fetch_assoc($user_q);
//     $wallet_before = (float)$user_data['wallet'];

//     // 2. Calculate wallet_after
//     $wallet_after = $wallet_before + $pAdd;
//     mysqli_query($con,"UPDATE users SET wallet=wallet+'$pAdd' WHERE mobile='$user_id'");
//     mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`, `owner`) 
//                         VALUES ('$user_id', '$pAdd', '$wallet_before', '$wallet_after', '1', 'Points Added By Admin', '$stamp', 'admin@gmail.com')");
//     // mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`owner`) VALUES ('$user_id','$pAdd','1','Points Added By Admin','$stamp','admin@gmail.com')");
//     echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
// }
// Points Logic (Add Points)
if(isset($_POST['AddPoints']) && $_POST['csrf_token'] === $_SESSION['csrf_token']){
    $pAdd = (float)$_POST['pointsAdd'];
    $current_date = date('Y-m-d H:i:s');

    // 1. Get current user data (Name and Wallet balance)
    $user_q = mysqli_query($con, "SELECT name, wallet FROM users WHERE mobile='$user_id'");
    $user_data = mysqli_fetch_assoc($user_q);
    
    $u_name = mysqli_real_escape_string($con, $user_data['name']);
    $wallet_before = (float)$user_data['wallet'];

    // 2. Calculate wallet_after
    $wallet_after = $wallet_before + $pAdd;

    // 3. Update User Wallet
    mysqli_query($con, "UPDATE users SET wallet='$wallet_after' WHERE mobile='$user_id'");

    // 4. Insert into Transactions Table (Log for wallet history)
    mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`, `owner`, `dated_on`) 
                        VALUES ('$user_id', '$pAdd', '$wallet_before', '$wallet_after', '1', 'Points Added By Admin', '$stamp', 'admin@gmail.com', '$current_date')");

    // 5. Insert into Payments Table (Log for Add Money History page)
    // status='SUCCESS' ensures it counts toward the total added points
    mysqli_query($con, "INSERT INTO `payments` (`name`, `mobile`, `amount`, `status`, `created_at`, `updated_at`) 
                        VALUES ('$u_name', '$user_id', '$pAdd', 'SUCCESS', '$current_date', '$current_date')");

    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}

// Withdraw Points Logic (Based on your reference file)
if(isset($_POST['WithdrawPoints']) && $_POST['csrf_token'] === $_SESSION['csrf_token']){
    $pWith = mysqli_real_escape_string($con, $_POST['pointsWithdwaw']);
    $created_at_date = date('Y-m-d h:i:sa');
    $user_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$user_id'");
    $user_data = mysqli_fetch_assoc($user_q);
    $wallet_before = (float)$user_data['wallet'];
     if($pWith > $wallet_before) {
        // If withdrawal amount is more than current wallet balance, show error and stop
        echo "<script>
                alert('ERROR: User only has $wallet_before points. You cannot withdraw $pWith points!'); 
                window.location.href='user-profile.php?userID=$user_id';
              </script>";
        exit; // Stop the script here so no database changes happen
    }
    // 2. Calculate wallet_after
    $wallet_after = $wallet_before - $pWith;
    // 1. Deduct from User Wallet
    $withdrawQuery = mysqli_query($con, "UPDATE users SET wallet = wallet - '$pWith' WHERE mobile = '$user_id'");
    
    if($withdrawQuery){
        // 2. Insert into Transactions (Type 0 = Debit)
        // mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`, `owner`) 
        //                     VALUES ('$user_id', '$pWith', '0', 'Points Withdraw By Admin', '$stamp', 'admin@gmail.com')");
        mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `created_at`, `owner`) 
                            VALUES ('$user_id', '$pWith', '$wallet_before', '$wallet_after', '0', 'Points Withdraw By Admin', '$stamp', 'admin@gmail.com')");
        // 3. Insert into Withdraw Requests (Status 1 = Approved/Direct)
        mysqli_query($con, "INSERT INTO `withdraw_requests`(`mobile`, `amount`, `mode`, `status`, `ac`, `ifsc`, `holder`, `date`) 
                            VALUES ('$user_id', '$pWith', 'Points Withdraw By Admin', '1', '0', '0', '0', '$created_at_date')");
        
        echo "<script>alert('Points Withdrawn Successfully'); window.location.href='user-profile.php?userID=$user_id';</script>";
    }
}

// Fetch Records
$select = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$user_id' ");
$row = mysqli_fetch_array($select);
$current_blocked = explode(',', $row['blocked_markets'] ?? '');
$bank = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `bank_history` WHERE `user`='$user_id'"));
$games_query = mysqli_query($con, "SELECT market FROM `gametime_manual` WHERE active='1'");
?>

<style>
    /* UI STYLING FROM SCREENSHOTS */
    .ten11-container { background: #fff; border-radius: 18px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
    .section-title { text-align: center; font-weight: bold; padding: 10px 0; border-bottom: 1px solid #eee; margin-bottom: 15px; }
    
    .info-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; }
    .info-label { width: 35%; color: #333; font-size: 14px; font-weight: 500; }
    .input-wrapper { width: 60%; position: relative; }

    .rounded-input { width: 100%; border-radius: 30px; border: 1px solid #e2e6ea; padding: 8px 18px; background: #f8f9fb; font-size: 14px; min-height: 38px; display: flex; align-items: center; color: #666; }
    .edit-trigger { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #f1f3f6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #333; }
    .edit-trigger:hover { background: #17a2b8; color: white; }

    .btn-copy-info { background: linear-gradient(135deg,#17a2b8,#138496); color: #fff; border: none; border-radius: 30px; padding: 8px 25px; font-size: 13px; }
    .btn-save-green { background: linear-gradient(135deg,#28a745,#1e7e34); color: white; border: none; border-radius: 30px; padding: 10px 45px; font-weight: 600; }
    .status-active { background: #28a745; color: #fff; border-radius: 15px; padding: 4px 15px; font-size: 12px; display: inline-block; }

    /* Orange Game Block UI */
    .game-item-orange { background: #ffa500; color: #fff; margin: 10px 0; padding: 15px; border-radius: 12px; display: flex; align-items: center; font-weight: bold; box-shadow: 0 4px 10px rgba(255,165,0,0.3); }
    .game-item-orange input { margin-right: 15px; width: 20px; height: 20px; }

    .device-table { width: 100%; }
    .device-table td { padding: 12px 0; font-size: 14px; color: #444; border-bottom: 1px solid #f1f1f1; }
    .device-table .val { color: #777; padding-left: 20px; }
    .h-100 { height: 100% !important; }
.flex-column-container { display: flex; flex-direction: column; }
</style>

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-md-6">
                    <div class="ten11-container">
                        <div class="text-center mb-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="90" class="rounded-circle">
                        </div>

                        <div class="info-row">
                            <span class="info-label">Username :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input"><?php echo $row['name']; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('name', '<?php echo $row['name']; ?>', 'user')"></i>
                            </div>
                        </div>
                        <!--<div class="info-row">-->
                        <!--    <span class="info-label">Password :</span>-->
                        <!--    <div class="input-wrapper">-->
                                <!--<div class="rounded-input"><?php echo $row['password']; ?></div>-->
                                <!--<i class="fas fa-edit edit-trigger" onclick="openEdit('password', '<?php echo $row['password']; ?>', 'user')"></i>-->
                        <!--        <div class="rounded-input"><?php echo $row['plain_password']; ?></div>-->
                        <!--        <div class="rounded-input" id="user_password"><?php echo $row['plain_password']; ?></div>-->
                        <!--       <i class="fas fa-edit edit-trigger" onclick="openEdit('password', '<?php echo $row['plain_password']; ?>', 'user')"></i>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="info-row">
                            <span class="info-label">Password :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="user_password"><?php echo $row['plain_password']; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('password', '<?php echo $row['plain_password']; ?>', 'user')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Wallet Point :</span>
                            <div class="input-wrapper">Rs. <?php echo $row['wallet']; ?> /-</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Contact number :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="user_mobile"><?php echo $row['mobile']; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('mobile', '<?php echo $row['mobile']; ?>', 'user')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Login With :</span>
                            <div class="input-wrapper">
                                <form method="POST" id="loginWithForm">
                                    <!-- These hidden fields use your existing updateField logic -->
                                    <input type="hidden" name="field_name" value="login_with">
                                    <input type="hidden" name="type" value="user">
                                    
                                    <select name="field_value" class="rounded-input w-100" style="border:1px solid #e2e6ea" onchange="this.form.submit()">
                                        <option value="password" <?php if($row['login_with'] == 'password') echo 'selected'; ?>>Password</option>
                                        <option value="otp" <?php if($row['login_with'] == 'otp') echo 'selected'; ?>>OTP</option>
                                        <option value="super" <?php if($row['login_with'] == 'super') echo 'selected'; ?>>Super</option>
                                    </select>
                                    
                                    <!-- We add the updateField name so the PHP catches it -->
                                    <input type="hidden" name="updateField" value="1">
                                </form>
                            </div>
                        </div>
                        <div class="text-center mt-3"><button class="btn-copy-info" onclick="copyPhone(event)">Copy Phone Password</button></div>
                        <!--<div class="info-row"><span class="info-label">Status :</span><div class="input-wrapper"><span class="status-active">active</span></div></div>-->
                        <div class="info-row">
                            <span class="info-label">Status :</span>
                            <div class="input-wrapper">
                                <?php if($row['active'] == 1): ?>
                                    <span class="status-active" style="background: #28a745;">active</span>
                                <?php else: ?>
                                    <span class="status-active" style="background: #dc3545;">banned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row mt-3"><span class="info-label">Account detail</span><button class="btn-copy-info" onclick="copyBank(event)">Copy Bank Detail</button></div>
                        <div class="info-row"><span class="info-label">Bank Change History :</span>
                        <!--<button class="btn-copy-info" style="background:#28a745">Bank change History</button>-->
                        <button class="btn-copy-info" style="background:#28a745" 
                            onclick="window.location.href='bank-change-history.php?userID=<?php echo $user_id; ?>'">
                            Bank change History
                            </button>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Bank Name :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="bank_name"><?php echo $bank['bank_name'] ?: 'null'; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('bank_name', '<?php echo $bank['bank_name']; ?>', 'bank')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">A/c Holder :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="bank_holder"><?php echo $bank['holder'] ?: 'null'; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('holder', '<?php echo $bank['holder']; ?>', 'bank')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">A/c No. :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="bank_ac"><?php echo $bank['ac'] ?: 'null'; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('ac', '<?php echo $bank['ac']; ?>', 'bank')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">IFSC :</span>
                            <div class="input-wrapper">
                                <div class="rounded-input" id="bank_ifsc"><?php echo $bank['ifsc'] ?: 'null'; ?></div>
                                <i class="fas fa-edit edit-trigger" onclick="openEdit('ifsc', '<?php echo $bank['ifsc']; ?>', 'bank')"></i>
                            </div>
                        </div>
                        <div class="text-center mt-4"><button class="btn-save-green">Save Bank Detail</button></div>
                        <div class="text-center mt-3">
                            <button class="btn btn-success btn-sm px-3" data-toggle="modal" data-target="#addPoint">Add Points</button>
                            <button class="btn btn-danger btn-sm px-3" data-toggle="modal" data-target="#withdrowPoints">Withdraw</button>
                        </div>
                    </div>

                    <!-- DEVICE INFO SECTION (WITH STATUS ACTIVE) -->
                    <!--<div class="ten11-container">-->
                    <!--    <div class="section-title">Device Info</div>-->
                    <!--    <table class="device-table">-->
                    <!--        <tr><td>Brand :</td><td class="val">vivo</td></tr>-->
                    <!--        <tr><td>Model :</td><td class="val">V2052</td></tr>-->
                    <!--        <tr><td>Device ID :</td><td class="val">RP1A.200720.012V2052</td></tr>-->
                    <!--        <tr><td>Login Time :</td><td class="val">26/08/2025 01:07 PM</td></tr>-->
                    <!--        <tr><td>Status :</td><td class="val"><span class="status-active">active</span></td></tr>-->
                    <!--    </table>-->
                    <!--</div>-->
                    
                    <!-- DEVICE INFO SECTION (DYNAMIC DATA) -->
                    <div class="ten11-container">
                        <div class="section-title">Device Info</div>
                        <table class="device-table">
                            <tr>
                                <td>Brand :</td>
                                <td class="val"><?php echo $row['device_brand'] ?: 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <td>Model :</td>
                                <td class="val"><?php echo $row['device_model'] ?: 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <td>Device ID :</td>
                                <td class="val" style="word-break: break-all;"><?php echo $row['device_id'] ?: 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <td>Login Time :</td>
                                <td class="val"><?php echo $row['last_login_time'] ?: 'Never Logged In'; ?></td>
                            </tr>
                            <tr>
                                <td>Status :</td>
                                <td class="val">
                                    <?php if($row['active'] == 1): ?>
                                        <span class="status-active" style="background: #28a745;">active</span>
                                    <?php else: ?>
                                        <span class="status-active" style="background: #dc3545;">banned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- RIGHT COLUMN: BLOCK LIST -->
               <!-- RIGHT COLUMN: BLOCK LIST -->
                <div class="col-md-6 d-flex"> <!-- Added d-flex here -->
                    <div class="ten11-container w-100 h-100"> <!-- Removed max-height, added h-100 -->
                        <div class="section-title">Game Block List</div>
                        <form method="POST">
                            <!-- Removed the div that had max-height and overflow-y:auto -->
                            <div class="game-list-wrapper">
                                <?php 
                                mysqli_data_seek($games_query, 0); // Reset pointer
                                while($g = mysqli_fetch_array($games_query)) { 
                                    $checked = in_array($g['market'], $current_blocked) ? 'checked' : '';
                                ?>
                                <div class="game-item-orange">
                                    <input type="checkbox" name="blocked_markets[]" value="<?php echo $g['market']; ?>" <?php echo $checked; ?>>
                                    <span><?php echo $g['market']; ?></span>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" name="save_blocked_games" class="btn btn-primary btn-block py-3 shadow" style="border-radius:30px; font-weight:bold;">SAVE BLOCK LIST</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODALS -->
<div class="modal fade" id="editModal"><div class="modal-dialog"><form method="POST" class="modal-content">
    <div class="modal-header"><h5>Edit Information</h5></div>
    <div class="modal-body">
        <input type="hidden" name="field_name" id="f_name"><input type="hidden" name="type" id="f_type">
        <label id="f_label" class="mb-2 text-muted"></label>
        <input type="text" name="field_value" id="f_val" class="form-control" style="border-radius:10px" required>
    </div>
    <div class="modal-footer border-0"><button type="submit" name="updateField" class="btn btn-primary px-4">Save Changes</button></div>
</form></div></div>

<!-- WITHDRAW MODAL -->
<div class="modal fade" id="withdrowPoints">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdraw Points</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Amount to Withdraw</label>
                    <input type="number" name="pointsWithdwaw" class="form-control" placeholder="Enter Amount" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="WithdrawPoints" class="btn btn-danger px-4">Withdraw Now</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addPoint"><div class="modal-dialog"><form method="POST" class="modal-content">
    <div class="modal-header"><h5>Add Points</h5></div>
    <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="number" name="pointsAdd" class="form-control" placeholder="Enter Amount" required>
    </div>
    <div class="modal-footer"><button type="submit" name="AddPoints" class="btn btn-success">Add</button></div>
</form></div></div>

<script>
function openEdit(field, val, type) {
    document.getElementById('f_name').value = field;
    document.getElementById('f_val').value = val;
    document.getElementById('f_type').value = type;
    document.getElementById('f_label').innerText = "Update " + field.replace('_', ' ');
    $('#editModal').modal('show');
}

function copyPhone(event) {

    var phone = document.getElementById("user_mobile").innerText;
    var password = document.getElementById("user_password").innerText.trim();
    var btn = event.target;

    // navigator.clipboard.writeText(phone).then(function() {

    //     var originalText = btn.innerText;
    //     btn.innerText = "Copied ✓";

    //     setTimeout(function(){
    //         btn.innerText = originalText;
    //     },2000);

    // });
    var combinedText = "Phone: " + phone + "\nPassword: " + password;

    // Copy to clipboard
    navigator.clipboard.writeText(combinedText).then(function() {
        
        // Show the alert as requested
        alert(combinedText);

        // Visual feedback on the button
        var btn = event.target;
        var originalText = btn.innerText;
        btn.innerText = "Copied ✓";

        setTimeout(function(){
            btn.innerText = originalText;
        }, 2000);

    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });

}

function copyBank(event){

    var holder = document.getElementById("bank_holder").innerText;
    var ac = document.getElementById("bank_ac").innerText;
    var ifsc = document.getElementById("bank_ifsc").innerText;
    var bankName = document.getElementById("bank_name").innerText; // Added this

    var bankDetails =
        "Bank Name: " + bankName + "\n" + // Added this
        "Account Holder: " + holder + "\n" +
        "Account Number: " + ac + "\n" +
        "IFSC: " + ifsc;

    var btn = event.target;

    navigator.clipboard.writeText(bankDetails).then(function(){

        var originalText = btn.innerText;
        btn.innerText = "Copied ✓";

        setTimeout(function(){
            btn.innerText = originalText;
        },2000);

    });

}
</script>

<?php include('footer.php'); ?>