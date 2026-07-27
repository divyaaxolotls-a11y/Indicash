<?php 
include('header.php'); 

// 1. SECURITY & SESSION INITIALIZATION
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

$user_id = mysqli_real_escape_string($con, $_GET['userID']);
$main_admin_mail = $main_admin_mail ?? 'admin@gmail.com'; 
$stamp = time();

// --- 2. BACKEND HANDLERS (LOGIC) ---

// Handle User Status & Betting Verification Toggles
if(isset($_GET['BettingActive'])){
    mysqli_query($con, "UPDATE `users` SET `verify`='1' WHERE `mobile`='".$_GET['BettingActive']."'");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}
if(isset($_GET['BettingDeactive'])){
    mysqli_query($con, "UPDATE `users` SET `verify`='0' WHERE `mobile`='".$_GET['BettingDeactive']."'");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}
if(isset($_GET['UserActive'])){
    mysqli_query($con, "UPDATE `users` SET `active`='1' WHERE `mobile`='".$_GET['UserActive']."'");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}
if(isset($_GET['UserDeactive'])){
    mysqli_query($con, "UPDATE `users` SET `active`='0' WHERE `mobile`='".$_GET['UserDeactive']."'");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}

// Handle Profile Field Updates (Name, Email, Mobile)
if (isset($_POST['updateField'])) {
    $fieldName = mysqli_real_escape_string($con, $_POST['field_name']);
    $newValue = htmlspecialchars($_POST['field_value'], ENT_QUOTES, 'UTF-8');
    $oldMobile = $_POST['old_mobile'];
    $stmt = $con->prepare("UPDATE users SET $fieldName = ? WHERE mobile = ?");
    $stmt->bind_param("ss", $newValue, $oldMobile);
    if ($stmt->execute()) {
        $redirectID = ($fieldName == 'mobile') ? $newValue : $oldMobile;
        echo "<script>window.location.href='user-profile.php?userID=$redirectID&msg=Updated';</script>";
    }
    $stmt->close();
}

// Handle Bank Details Updates
if (isset($_POST['updateBankField'])) {
    $fieldName = mysqli_real_escape_string($con, $_POST['field_name']); 
    $newValue = htmlspecialchars($_POST['field_value'], ENT_QUOTES, 'UTF-8');
    mysqli_query($con, "UPDATE bank_history SET $fieldName = '$newValue' WHERE user = '$user_id'");
    echo "<script>window.location.href='user-profile.php?userID=$user_id&msg=BankUpdated';</script>";
}

// Handle Add Points (with Admin Balance Check & CSRF)
if(isset($_POST['AddPoints'])){
    if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $pAdd = $_POST['pointsAdd'];
        $proceed = true;
        if($_SESSION['userID'] != $main_admin_mail){
            $adminCheck = mysqli_query($con,"SELECT sn FROM admin WHERE wallet >= $pAdd AND email='".$_SESSION['userID']."'");
            if(mysqli_num_rows($adminCheck) == 0){
                echo "<script>window.location.href='user-profile.php?userID=$user_id&error=Insufficient Admin Balance';</script>";
                $proceed = false;
            } else {
                mysqli_query($con,"UPDATE admin SET wallet=wallet-$pAdd WHERE email='".$_SESSION['userID']."'");
                mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('".$_SESSION['userID']."','$pAdd','0','Points sent to $user_id','".$_SESSION['userID']."','$stamp')");
            }
        }
        if($proceed){
            mysqli_query($con,"UPDATE users SET wallet=wallet+'$pAdd' WHERE mobile='$user_id'");
            mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`owner`) VALUES ('$user_id','$pAdd','1','Points Added By Admin','$stamp','admin@gmail.com')");
            $ca = date("Y-m-d H:i:s"); $cas = date('Y-m-d h:i:sa');
            mysqli_query($con,"INSERT INTO auto_deposits (mobile, amount, pay_id, method, status, created_at, date, updated_at) VALUES ('$user_id', $pAdd, 0, 'Added By Admin', 1, '$ca', '$cas', '$ca')");
            sendNotification("Admin added $pAdd points","Wallet Updated",$user_id);
            echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
        }
    }
}

// Handle Withdraw Points (Manual Deduction)
if(isset($_POST['WithdwawPoints'])){
    if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $pWith = $_POST['pointsWithdwaw'];
        mysqli_query($con,"UPDATE users SET wallet=wallet-'$pWith' WHERE mobile='$user_id'");
        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`owner`) VALUES ('$user_id','$pWith','0','Points Withdraw By Admin','$stamp','admin@gmail.com')");
        $cas = date('Y-m-d h:i:sa');
        mysqli_query($con, "INSERT INTO `withdraw_requests`(`user`, `amount`, `mode`, `status`, `ac`, `ifsc`, `holder`,`date`) VALUES ('$user_id', '$pWith', 'Points Withdraw By Admin', '1', '0', '0', '0','$cas')");
        sendNotification("Admin withdrew $pWith points","Wallet Updated",$user_id);
        echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
    }
}

// Handle Withdraw Request Approval/Rejection
if(isset($_POST['requestApproved'])){
    $id = $_POST['id'];
    mysqli_query($con,"UPDATE withdraw_requests SET status='1' WHERE sn='$id'");
    log_action("Approved withdrawal request #$id");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}
if(isset($_POST['requestRejected'])){
    $id = $_POST['id'];
    $info = mysqli_fetch_array(mysqli_query($con,"SELECT user, amount FROM withdraw_requests WHERE sn='$id'"));
    $mob = $info['user']; $amt = $info['amount'];
    mysqli_query($con,"UPDATE withdraw_requests SET status='2' WHERE sn='$id'");
    mysqli_query($con,"UPDATE users SET wallet=wallet+$amt WHERE mobile='$mob'");
    mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mob','$amt','1','Withdraw cancelled by team','user','$stamp')");
    log_action("Rejected withdrawal #$id and refunded $amt");
    echo "<script>window.location.href='user-profile.php?userID=$user_id';</script>";
}


// --- FETCH DISPLAY DATA ---
$row = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$user_id' "));
$bank = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `bank_history` WHERE `user`='$user_id'"));
?>

<style>
    body { background: #f4f7f6; font-family: 'Source Sans Pro', sans-serif; }
    .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; overflow: hidden; }
    .details-header { background: #f8f9fa; padding: 15px 25px; border-bottom: 1px solid #eee; }
    .details-header h4, .details-header h5 { color: #2c3e50; font-weight: 800; font-size: 15px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
    
    .status-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; border-bottom: 1px solid #f8f9fa; }
    .status-badge { padding: 6px 16px; border-radius: 50px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    
    .wallet-box { margin: 20px; padding: 25px; background: #fff; border-radius: 15px; border: 1px solid #f0f0f0; text-align: center; }
    .wallet-box .amount { font-size: 36px; font-weight: 800; color: #2c3e50; line-height: 1; }
    .btn-add { background: linear-gradient(135deg, #28a745, #218838); color: #fff !important; border-radius: 50px; padding: 10px 20px; font-weight: 700; border: none; }
    .btn-withdraw { background: linear-gradient(135deg, #dc3545, #c82333); color: #fff !important; border-radius: 50px; padding: 10px 20px; font-weight: 700; border: none; }

    .info-group { display: flex; align-items: center; padding: 18px 25px; border-bottom: 1px solid #f8f9fa; }
    .info-icon { width: 40px; height: 40px; background: #eef2f7; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: #007bff; flex-shrink: 0; }
    .editable-input { border: none; background: transparent; font-weight: 600; color: #2c3e50; width: 100%; font-size: 15px; }
    .btn-save-inline { background: none; border: none; color: #007bff; opacity: 0.5; transition: 0.3s; }
    .btn-save-inline:hover { opacity: 1; transform: scale(1.2); }

    .custom-table thead th { background: #f8f9fa; font-size: 11px; text-transform: uppercase; color: #adb5bd; padding: 15px; text-align: center; border: none; }
    .custom-table tbody td { padding: 15px; text-align: center; border-bottom: 1px solid #f8f9fa; font-weight: 600; font-size: 13px; }
    .badge-pill-custom { padding: 5px 12px; border-radius: 50px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .badge-credit { background: #e6f9ed; color: #28a745; }
    .badge-debit { background: #ffebeb; color: #dc3545; }

    .nav-tabs { border: none; justify-content: center; margin-bottom: 20px; }
    .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 700; text-transform: uppercase; font-size: 12px; padding: 10px 25px; }
    .nav-tabs .nav-link.active { color: #17a2b8; background: transparent; border-bottom: 3px solid #17a2b8; }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row pt-4">
            <!-- LEFT: USER STATUS & WALLET -->
            <div class="col-md-4">
                <div class="card">
                    <div class="details-header text-center"><h4>User Status</h4></div>
                    <?php if(isset($_GET['error'])){ echo '<div class="alert alert-danger mx-3 mt-3">'.$_GET['error'].'</div>'; } ?>
                    
                    <div class="status-row">
                        <span>User Account</span>
                        <a href="user-profile.php?<?php echo ($row['active']==0?'UserActive':'UserDeactive').'='.$row['mobile']; ?>&userID=<?php echo $user_id; ?>">
                            <span class="badge status-badge <?php echo ($row['active']==0?'badge-danger':'badge-success'); ?>"><?php echo ($row['active']==0?'Inactive':'Active'); ?></span>
                        </a>
                    </div>
                    <div class="status-row">
                        <span>Betting Permission</span>
                        <a href="user-profile.php?<?php echo ($row['verify']==0?'BettingActive':'BettingDeactive').'='.$row['mobile']; ?>&userID=<?php echo $user_id; ?>">
                            <span class="badge status-badge <?php echo ($row['verify']==0?'badge-danger':'badge-success'); ?>"><?php echo ($row['verify']==0?'Blocked':'Allowed'); ?></span>
                        </a>
                    </div>

                    <div class="wallet-box">
                        <div style="color:#ddd; font-size:24px; margin-bottom:5px;"><i class="fas fa-wallet"></i></div>
                        <div class="amount"><?php echo number_format($row['wallet']); ?></div>
                        <div style="color:green; font-weight:700; font-size:12px; margin-bottom:15px; letter-spacing:1px;">POINTS</div>
                        <div class="d-flex justify-content-center">
                            <a href="#addPoint" data-toggle="modal" class="btn btn-add mx-1">Add Points</a>
                            <a href="#withdrowPoints" data-toggle="modal" class="btn btn-withdraw mx-1">Withdraw</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: PERSONAL INFORMATION -->
            <div class="col-md-8">
                <div class="card">
                    <div class="details-header"><h5>Personal Information</h5></div>
                    
                    <!-- Name -->
                    <div class="info-group">
                        <div class="info-icon"><i class="fas fa-user"></i></div>
                        <div class="info-content">
                            <label style="font-size:10px; font-weight:700; color:#adb5bd; text-transform:uppercase; display:block; margin:0;">Full Name</label>
                            <form method="POST" class="d-flex align-items-center">
                                <input type="text" name="field_value" value="<?php echo htmlspecialchars($row['name']); ?>" class="editable-input">
                                <input type="hidden" name="field_name" value="name">
                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                <button type="submit" name="updateField" class="btn-save-inline"><i class="fas fa-check-circle"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="info-group">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <label style="font-size:10px; font-weight:700; color:#adb5bd; text-transform:uppercase; display:block; margin:0;">Email Address</label>
                            <form method="POST" class="d-flex align-items-center">
                                <input type="email" name="field_value" value="<?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?>" class="editable-input">
                                <input type="hidden" name="field_name" value="email">
                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                <button type="submit" name="updateField" class="btn-save-inline"><i class="fas fa-check-circle"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div class="info-group">
                        <div class="info-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="info-content">
                            <label style="font-size:10px; font-weight:700; color:#adb5bd; text-transform:uppercase; display:block; margin:0;">Mobile Number</label>
                            <form method="POST" class="d-flex align-items-center">
                                <input type="text" name="field_value" value="<?php echo $row['mobile']; ?>" class="editable-input">
                                <input type="hidden" name="field_name" value="mobile">
                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                <button type="submit" name="updateField" class="btn-save-inline" onclick="return confirm('Changing this changes the login ID. Proceed?')"><i class="fas fa-check-circle"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Registration -->
                    <div class="info-group">
                        <div class="info-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="info-content">
                            <label style="font-size:10px; font-weight:700; color:#adb5bd; text-transform:uppercase; display:block; margin:0;">Registration Date</label>
                            <span style="font-weight:600; color:#2c3e50;"><?php echo date('d M Y', $row['created_at']); ?></span>
                        </div>
                    </div>

                    <div class="text-center py-4">
                        <a href="https://api.whatsapp.com/send?phone=91<?php echo $row['mobile']; ?>" target="_blank" class="btn btn-sm px-4 mr-2" style="background:#25d366; color:#fff; border-radius:50px; font-weight:700;">WhatsApp</a>
                        <a href="tel:+91<?php echo $row['mobile']; ?>" class="btn btn-sm px-4" style="background:#ff4b2b; color:#fff; border-radius:50px; font-weight:700;">Call User</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENT INFORMATION GRID -->
        <div class="card">
            <div class="details-header"><h5><i class="fas fa-university mr-2" style="color:#fd7e14;"></i> Payment Information</h5></div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1px; background:#f1f3f5;">
                <?php 
                $p_fields = ['holder'=>'Account Holder', 'ac'=>'Account Number', 'ifsc'=>'IFSC Code'];
                foreach($p_fields as $key => $lbl): ?>
                <div style="background:#fff; padding:20px; display:flex; align-items:center;">
                    <div style="width:40px; height:40px; background:#fff4e5; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:15px; color:#fd7e14;"><i class="fas fa-id-card"></i></div>
                    <div style="flex-grow:1;">
                        <label style="font-size:10px; font-weight:700; color:#adb5bd; text-transform:uppercase; margin:0;"><?php echo $lbl; ?></label>
                        <form method="POST" class="d-flex align-items-center">
                            <input type="text" name="field_value" value="<?php echo htmlspecialchars($bank[$key] ?? 'N/A'); ?>" class="editable-input" style="font-size:14px;">
                            <input type="hidden" name="field_name" value="<?php echo $key; ?>">
                            <button type="submit" name="updateBankField" class="btn-save-inline"><i class="fas fa-check-circle"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- WITHDRAWAL REQUESTS -->
        <div class="card">
            <div class="details-header"><h5>Withdraw Points Requests</h5></div>
            <div class="table-responsive">
                <table class="custom-table w-100">
                    <thead><tr><th>Points</th><th>Req ID</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $page0 = isset($_GET["page0"]) ? (int)$_GET["page0"] : 1;
                        $start0 = ($page0-1) * 10;
                        $res0 = mysqli_query($con, "SELECT * FROM withdraw_requests WHERE user='$user_id' ORDER BY sn DESC LIMIT $start0, 10");
                        while($r0 = mysqli_fetch_array($res0)){
                            $st = $r0['status'];
                            $lbl = ($st==1 ? 'Accepted' : ($st==0 ? 'Pending' : 'Rejected'));
                            $cls = ($st==1 ? 'badge-accepted' : ($st==0 ? 'badge-pending' : 'badge-rejected'));
                        ?>
                        <tr>
                            <td style="color:#dc3545; font-weight:800;"><?php echo number_format($r0['amount']); ?></td>
                            <td>#<?php echo $r0['sn']; ?></td>
                            <td><?php echo $r0['created_at']; ?></td>
                            <td><span class="badge-pill-custom <?php echo ($st==0?'badge-warning':($st==1?'badge-credit':'badge-debit')); ?>"><?php echo $lbl; ?></span></td>
                            <td>
                                <?php if($st == 0){ ?>
                                    <form method="post" style="display:inline;"><input type="hidden" name="id" value="<?php echo $r0['sn']; ?>"><button type="submit" name="requestApproved" class="btn btn-success btn-xs px-2 mr-1">Approve</button></form>
                                    <form method="post" style="display:inline;"><input type="hidden" name="id" value="<?php echo $r0['sn']; ?>"><button type="submit" name="requestRejected" class="btn btn-danger btn-xs px-2">Reject</button></form>
                                <?php } else { echo "Done"; } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESTORED BID HISTORY (All 7 Columns) -->
        <div class="card">
            <div class="details-header"><h5>Bid History (Latest 15)</h5></div>
            <div class="table-responsive">
                <table class="custom-table w-100">
                    <thead>
                        <tr>
                            <th>S. No.</th>
                            <th>Game Name</th>
                            <th>Game Type</th>
                            <th>Digits</th>
                            <th>Points</th>
                            <th>Date</th>
                            <th>Placed on</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                        $start = ($page-1) * 15;
                        $resB = mysqli_query($con, "SELECT * FROM games WHERE user='$user_id' ORDER BY sn DESC LIMIT $start, 15");
                        $i = $start + 1;
                        while($rb = mysqli_fetch_array($resB)){ ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $rb['bazar']; ?></td>
                            <td><?php echo $rb['game_type']; ?></td>
                            <td><?php echo $rb['number']; ?></td>
                            <td style="color:#007bff;"><?php echo $rb['amount']; ?></td>
                            <td><?php echo $rb['date']; ?></td>
                            <td><?php echo date("h:i A d/m/Y", $rb['created_at']); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WALLET TABS (All Columns Restored) -->
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-3" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#allT">All Txns</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#creditT">Credit</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#debitT">Debit (TXN ID)</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="allT">
                        <table class="custom-table w-100">
                            <thead><tr><th>S. No.</th><th>Points</th><th>Note</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php $resA = mysqli_query($con,"SELECT * FROM transactions WHERE user='$user_id' ORDER BY sn DESC LIMIT 10");
                                $ia = 1; while($ra = mysqli_fetch_array($resA)){ ?>
                                <tr><td><?php echo $ia++; ?></td><td><span class="badge-pill-custom <?php echo ($ra['type']=='1'?'badge-credit':'badge-debit'); ?>"><?php echo ($ra['type']=='1'?'+':'-').$ra['amount']; ?></span></td><td><?php echo $ra['remark']; ?></td><td><?php echo date('h:i A d-m-Y',$ra['created_at']); ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="creditT">
                        <table class="custom-table w-100">
                            <thead><tr><th>S. No.</th><th>Points</th><th>Note</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php $resC = mysqli_query($con,"SELECT * FROM transactions WHERE user='$user_id' AND type='1' ORDER BY sn DESC LIMIT 10");
                                $ic = 1; while($rc = mysqli_fetch_array($resC)){ ?>
                                <tr><td><?php echo $ic++; ?></td><td><span class="badge-pill-custom badge-credit">+<?php echo $rc['amount']; ?></span></td><td><?php echo $rc['remark']; ?></td><td><?php echo date('d-m-Y',$rc['created_at']); ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="debitT">
                        <table class="custom-table w-100">
                            <thead><tr><th>S. No.</th><th>Points</th><th>Note</th><th>Date</th><th>TXN ID</th></tr></thead>
                            <tbody>
                                <?php $resD = mysqli_query($con,"SELECT * FROM transactions WHERE user='$user_id' AND type='0' ORDER BY sn DESC LIMIT 10");
                                $id = 1; while($rd = mysqli_fetch_array($resD)){ ?>
                                <tr><td><?php echo $id++; ?></td><td><span class="badge-pill-custom badge-debit">-<?php echo $rd['amount']; ?></span></td><td><?php echo $rd['remark']; ?></td><td><?php echo date('d-m-Y',$rd['created_at']); ?></td><td>#<?php echo $rd['sn']; ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <a href="user_transaction_all.php?user_id=<?php echo $user_id; ?>"><button class="btn btn-danger px-5" style="border-radius:50px; font-weight:700;">View Full History</button></a>
        </div>
    </div>
</section>

<!-- --- MODALS (ADD/WITHDRAW) --- -->
<div class="modal fade" id="addPoint">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:20px;"><div class="modal-header"><h5>Add Points</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">
    <form method="post"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><div class="form-group"><label>Amount</label><input type="number" name="pointsAdd" class="form-control" required></div>                                            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
 <button type="submit" name="AddPoints" class="btn btn-add w-100">Add Points</button></form>
    </div></div></div>
</div>

<div class="modal fade" id="withdrowPoints">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:20px;"><div class="modal-header"><h5>Withdraw Points</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">
    <form method="post"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><div class="form-group"><label>Amount</label><input type="number" name="pointsWithdwaw" class="form-control" required></div><button class="btn btn-withdraw w-100" type="submit" name="WithdwawPoints">Withdraw Points</button></form>
    </div></div></div>
</div>
<script>
window.onload = function() {
    document.body.classList.remove("modal-open");
    var backdrops = document.getElementsByClassName("modal-backdrop");
    while(backdrops.length > 0){
        backdrops[0].parentNode.removeChild(backdrops[0]);
    }
};
</script>
<?php include('footer.php'); ?>