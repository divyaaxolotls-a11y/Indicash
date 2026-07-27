<?php 
include('header.php');

// --- HELPER FUNCTION FOR IFSC TO BANK ---
function getBankNameFromIFSC($ifsc) {
    if (empty($ifsc)) return "N/A";
    $code = substr(strtoupper($ifsc), 0, 4);
    $banks = [
        'SBIN' => 'STATE BANK OF INDIA',
        'HDFC' => 'HDFC BANK',
        'ICIC' => 'ICICI BANK',
        'BARB' => 'BANK OF BARODA',
        'PUNB' => 'PUNJAB NATIONAL BANK',
        'UTIB' => 'AXIS BANK',
        'PYTM' => 'PAYTM PAYMENTS BANK',
        'AIRP' => 'AIRTEL PAYMENTS BANK',
        'KKBK' => 'KOTAK MAHINDRA BANK',
        'YESB' => 'YES BANK',
        'MAHB' => 'BANK OF MAHARASHTRA',
        'CNRB' => 'CANARA BANK',
        'UBIN' => 'UNION BANK OF INDIA',
        'IDFB' => 'IDFC FIRST BANK',
        'CENT' => 'CENTRAL BANK OF INDIA'
    ];
    return $banks[$code] ?? "Bank Code: " . $code;
}

// ================= POST HANDLERS =================
if (isset($_POST['saveNoteSimple'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $note = mysqli_real_escape_string($con, $_POST['note_content']);
    mysqli_query($con, "UPDATE withdraw_requests SET info='$note' WHERE sn='$id'");
    echo "<script>window.location.href=window.location.href;</script>";
    exit;
}

if (in_array(13, $HiddenProducts)){

    $whereConditions = [];
    $search_user = '';
    
    if (!empty($_GET['date'])) {
        $date = mysqli_real_escape_string($con, $_GET['date']);
        $whereConditions[] = "DATE(wr.created_at) = '$date'";
    }
    
    if (!empty($_GET['status'])) {
        $status = $_GET['status'];
        if ($status == 'send' || $status == 'pending') { $whereConditions[] = "wr.status = 0"; }
        elseif ($status == 'processing') { $whereConditions[] = "wr.status = 1"; }
        elseif ($status == 'attempt') { $whereConditions[] = "wr.status = 3"; }
        elseif ($status == 'manual') { $whereConditions[] = "wr.status = 4"; }
        elseif ($status == 'wrong') { $whereConditions[] = "wr.status = 2"; }
    } elseif (empty($_GET['date']) && empty($_GET['search_user'])) {
        $whereConditions[] = "wr.status = 0"; 
    }
    
    if (!empty($_GET['search_user'])) {
        $search_user = mysqli_real_escape_string($con, $_GET['search_user']);
        $whereConditions[] = "(wr.mobile LIKE '%$search_user%' OR wr.holder LIKE '%$search_user%' OR u.name LIKE '%$search_user%')";
    }
    
    $sql = "SELECT wr.*, u.name as u_name, u.wallet as u_wallet FROM withdraw_requests wr LEFT JOIN users u ON wr.mobile = u.mobile";
    if (!empty($whereConditions)) { $sql .= " WHERE " . implode(' AND ', $whereConditions); }
    $sql .= " ORDER BY wr.date DESC, wr.sn DESC"; // Modified for date grouping
    
    $result = mysqli_query($con, $sql);
    $total_withdraw_amount = 0;
    $count = mysqli_num_rows($result);
    while ($calc = mysqli_fetch_array($result)) { $total_withdraw_amount += $calc['amount']; }
    mysqli_data_seek($result, 0);
?>

<style>
    body { background-color: #f0f2f5; font-family: 'Source Sans Pro', sans-serif; }
    .main-container { padding: 15px; width: 100%; max-width: 900px; margin: auto; }
    .page-title { background: black; color: white; text-align: center; padding: 10px; border-radius: 8px; font-weight: bold; margin-bottom: 15px; }
    .filter-box { background: white; padding: 15px; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #ddd; }
    .input-row { display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
    .input-row input, .input-row select { flex: 1; min-width: 150px; border-radius: 8px; border: 1px solid #ccc; padding: 10px; font-size: 14px; }
    .btn-filter { width: 100%; background: #007bff; color: white; border: none; padding: 12px; border-radius: 20px; font-weight: bold; cursor: pointer; }
    .total-display { text-align: center; font-weight: bold; font-size: 16px; margin: 15px 0; color: #333; }

    /* Date Divider Style */
    .date-divider { display: flex; align-items: center; text-align: center; margin: 20px 0; color: #333; }
    .date-divider::before, .date-divider::after { content: ''; flex: 1; border-bottom: 1px solid #ccc; }
    .date-divider span { background: #007bff; color:white; padding: 5px 15px; border-radius: 5px; margin: 0 10px; font-size: 14px; font-weight: bold; }

    /* Card Layout */
    .request-card { background: #d1d1d1; border: 2px solid #003366; border-radius: 12px; margin-bottom: 12px; overflow: hidden; cursor: pointer; transition: 0.3s; }
    .card-summary { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; }
    .user-info .name { font-size: 20px; font-weight: bold; color: black; text-transform: capitalize; }
    .point-info { text-align: right; }
    .point-info .value { font-size: 24px; font-weight: bold; color: black; }

    /* Expanded Detail Section */
    .card-detail { display: none; background: #003366; color: white; padding: 20px; border-top: 1px solid #002244; }
    .btn-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; justify-content: center; }
    .btn-pill { border-radius: 20px; border: none; padding: 7px 18px; font-size: 13px; color: white; font-weight: 600; cursor: pointer; }
    .btn-blue { background: #007bff; }
    .btn-green { background: #28a745; }
    .btn-yellow { background: #ffc107; color: black; }
    .btn-teal { background: #17a2b8; }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 14px; margin-bottom: 20px; line-height: 1.6; }
    .bank-info-container { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
    .bank-info-box { background: #007bff; border-radius: 6px; padding: 6px 12px; font-size: 13px; font-weight: 500; }
    .wallet-badge { background: #17a2b8 !important; }

    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 8px; margin-top: 20px; }
    .btn-action { font-size: 12px; padding: 8px 5px; border-radius: 20px; border: none; color: white; text-align: center; cursor: pointer; font-weight: 600; }
    .btn-save-note { width: 100%; background: #17a2b8; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    .footer-actions { display: flex; justify-content: space-between; margin-top: 25px; align-items: center; gap: 10px; }
    .btn-final { border-radius: 8px; padding: 12px 25px; border: none; color: white; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; justify-content: center; }

    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .modal-content { background: white; width: 90%; max-width: 400px; border-radius: 4px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .modal-header { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .modal-footer { padding: 20px; display: flex; justify-content: center; gap: 20px; }
    .btn-payout-modal { background: #ffc107; border:none; padding:10px 25px; border-radius:4px; font-weight:bold; cursor:pointer; }
</style>

<div class="main-container">
    <div class="page-title">Withdraw Money Request</div>

    <form method="get" class="filter-box">
        <div class="input-row">
            <input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?php if(($_GET['status']??"")=="pending") echo 'selected';?>>Pending / Send Request</option>
                <option value="processing" <?php if(($_GET['status']??"")=="processing") echo 'selected';?>>Processing</option>
                <option value="attempt" <?php if(($_GET['status']??"")=="attempt") echo 'selected';?>>Attempt</option>
                <option value="manual" <?php if(($_GET['status']??"")=="manual") echo 'selected';?>>Manual</option>
                <option value="wrong" <?php if(($_GET['status']??"")=="wrong") echo 'selected';?>>Wrong Detail</option>
            </select>
        </div>
        <div class="input-row">
            <input type="text" name="search_user" placeholder="Search user..." value="<?php echo $_GET['search_user'] ?? ''; ?>">
            <button type="submit" class="btn-filter">Filter</button>
        </div>
    </form>

    <div class="total-display">Amount : <?php echo $total_withdraw_amount; ?> (<?php echo $count; ?>)</div>

    <?php 
    $currentGroupDate = "";
    while ($row = mysqli_fetch_array($result)) { 
        // --- DATE GROUPING ---
        $rowDate = $row['date'];
        if ($rowDate != $currentGroupDate) {
            echo '<div class="date-divider"><span>' . date('d-m-Y', strtotime($rowDate)) . '</span></div>';
            $currentGroupDate = $rowDate;
        }

        $unique_id = "req_".$row['sn'];
        $mode = strtolower($row['mode']);
    ?>
    <div class="request-card" onclick="toggleDetail('<?php echo $unique_id; ?>')">
        <div class="card-summary">
            <div class="user-info">
                <div class="mobile"><?php echo $row['mobile']; ?></div>
                <div class="name"><?php echo htmlspecialchars($row['u_name'] ?? $row['holder']); ?></div>
            </div>
            <div class="point-info">
                <div class="label">POINT</div>
                <div class="value"><?php echo $row['amount']; ?></div>
            </div>
        </div>

        <div class="card-detail" id="<?php echo $unique_id; ?>" onclick="event.stopPropagation();">
            <div class="btn-row">
                <a href="user-wallet-history.php?user_mobile=<?php echo $row['mobile']; ?>" class="btn-pill btn-blue" style="text-decoration: none;">Transaction</a>
                <a href="tel:<?php echo $row['mobile']; ?>" class="btn-pill btn-blue" style="text-decoration: none;">Call</a>
                <a href="https://wa.me/<?php echo $row['mobile']; ?>?text=Hello <?php echo urlencode($row['u_name']); ?>, your request of <?php echo $row['amount']; ?> is processing." class="btn-pill btn-green" style="text-decoration: none;">QR Msg</a>
                <a href="user-profile.php?userID=<?php echo $row['mobile']; ?>" class="btn-pill btn-yellow" style="text-decoration: none;">Profile</a>           
                <button class="btn-pill btn-teal" onclick="copyAllDetails('<?php echo $row['amount']; ?>', '<?php echo addslashes(strtoupper($row['holder'])); ?>', '<?php echo addslashes(strtoupper($row['bank'] ?? '')); ?>', '<?php echo $row['ac']; ?>', '<?php echo $row['ifsc']; ?>')">Copy All</button>
            </div>

            <div class="detail-grid">
                <div>Withdrawal Money<br><span style="font-size: 16px; font-weight:bold;">Username : <?php echo htmlspecialchars($row['u_name']); ?></span></div>
                <div style="text-align: right;">
                    <span style="background:green; padding:5px 10px; border-radius:4px; font-weight:bold;">Point : <?php echo $row['amount']; ?></span><br>
                    Wallet : <?php echo $row['u_wallet']; ?>
                </div>
                <div>Request Date : <?php echo date('d/m/Y', strtotime($row['created_at'])); ?><br><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                <div style="text-align: right;">Type : <?php echo $row['mode']; ?><br>Status : Pending</div>
            </div>

            <div class="bank-info-container">
                <?php if ($mode == 'bank' || $mode == 'manual'): ?>
                    <div class="bank-info-box" onclick="copyField('<?php echo addslashes(strtoupper($row['holder'])); ?>')">Name : <?php echo strtoupper($row['holder']); ?></div>
                    <div class="bank-info-box" onclick="copyField('<?php echo addslashes(strtoupper($row['bank'] ?? 'N/A')); ?>')">
                        Bank : <?php echo !empty($row['bank']) ? strtoupper($row['bank']) : getBankNameFromIFSC($row['ifsc']); ?>
                    </div>
                    <div class="bank-info-box" onclick="copyField('<?php echo $row['ac']; ?>')">A/c : <?php echo $row['ac']; ?></div>
                    <div class="bank-info-box" onclick="copyField('<?php echo $row['ifsc']; ?>')">IFSC : <?php echo $row['ifsc']; ?></div>
                <?php else: ?>
                    <div class="bank-info-box">Name : <?php echo strtoupper($row['holder']); ?></div>
                    <div class="bank-info-box wallet-badge" onclick="copyField('<?php echo $row[$mode] ?? $row['payment_number']; ?>')">
                        <?php echo strtoupper($mode); ?> NO : <?php echo $row[$mode] ?? $row['payment_number']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="action-grid">
                <button type="button" class="btn-action btn-green" onclick="alert('Please do it manually')">Send Request</button>
                <button type="button" class="btn-action btn-yellow" onclick="setNote('<?php echo $row['sn']; ?>', 'Processing')">Processing</button>
                <button type="button" class="btn-action" style="background: #e83e8c;" onclick="setNote('<?php echo $row['sn']; ?>', 'Attempt')">Attempt</button>
                <button type="button" class="btn-action" style="background: #343a40;" onclick="openManualModal('<?php echo $row['sn']; ?>', '<?php echo htmlspecialchars($row['u_name']); ?>', '<?php echo $row['mobile']; ?>', '<?php echo $row['amount']; ?>')">Manual</button>
                <button type="button" class="btn-action btn-teal" onclick="setNote('<?php echo $row['sn']; ?>', 'Pending')">Pending</button>
                <button type="button" class="btn-action" style="background: #dc3545;" onclick="setNote('<?php echo $row['sn']; ?>', 'Wrong Detail')">Wrong Detail</button>
                <button type="button" class="btn-action" style="background: #6c757d;" onclick="setNote('<?php echo $row['sn']; ?>', '')">Reset</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                <textarea name="note_content" id="note_<?php echo $row['sn']; ?>" class="form-control mt-3" rows="2"><?php echo htmlspecialchars($row['info']); ?></textarea>
                <button type="submit" name="saveNoteSimple" class="btn-save-note">Save Note</button>
            </form>

            <div class="footer-actions">
                <!--<button type="button" class="btn-final btn-blue" onclick="openApprovalPopup('<?php echo $row['sn']; ?>', '<?php echo htmlspecialchars($row['u_name']); ?>', '<?php echo $row['mobile']; ?>', '<?php echo $row['amount']; ?>')"><i class="fas fa-check"></i> Accepted</button>-->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                    <input type="hidden" name="mobile" value="<?php echo $row['mobile']; ?>">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['u_name']); ?>">
                    <input type="hidden" name="requestApproved" value="1">
                
                    <button type="submit" class="btn-final btn-blue">
                        <i class="fas fa-check"></i> Accepted
                    </button>
                </form>
                <a href="https://wa.me/<?php echo $row['mobile']; ?>" class="btn-pill btn-green" style="padding:10px;"><i class="fab fa-whatsapp"></i></a>
                <button class="btn-pill btn-yellow" style="padding:10px;"><i class="fas fa-bell"></i></button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                    <button type="submit" name="requestRejected" class="btn-final" style="background:#dc3545;"><i class="fas fa-times"></i> Rejected</button>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Modals -->
<div id="manualModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header"><h4>Select Manual Type</h4><span style="cursor:pointer; font-size:24px;" onclick="closeModal()">&times;</span></div>
        <div class="modal-body">Username: <span id="pop_user"></span><br>Phone: <span id="pop_phone"></span><br>Point: <span id="pop_point"></span></div>
        <div class="modal-footer"><button class="btn-payout-modal" onclick="confirmManual('Manual Payout')">Payout</button><button class="btn-payout-modal" style="background:#17a2b8; color:white;" onclick="confirmManual('Manual Others')">Other</button></div>
    </div>
</div>

<div id="approvalModal" class="modal-overlay">
    <div class="modal-content" style="padding:20px;">
        <div class="modal-header"><h4>Name : <span id="display_name"></span></h4><span style="cursor:pointer; font-size:28px;" onclick="closeApprovalModal()">&times;</span></div>
        <textarea id="wp_message_text" style="width:100%; border-radius:10px; padding:10px; height:100px; margin-top:10px;"></textarea>
        <div class="modal-footer"><button type="button" onclick="executeFinalApproval()" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:5px;">Send</button></div>
    </div>
</div>

<form id="hiddenProcessForm" method="post" style="display:none;"><input type="hidden" name="id" id="hidden_sn"><input type="hidden" name="amount" id="hidden_amount"><input type="hidden" name="requestApproved" value="1"></form>

<script>
    function toggleDetail(id) {
        var element = document.getElementById(id);
        if (element.style.display === "block") { element.style.display = "none"; } 
        else { document.querySelectorAll('.card-detail').forEach(el => el.style.display = 'none'); element.style.display = "block"; }
    }
    function setNote(sn, text) { var textarea = document.getElementById('note_' + sn); if (textarea) { textarea.value = text; } }
    let activeSn = null;
    function openManualModal(sn, user, phone, points) { activeSn = sn; document.getElementById('pop_user').innerText = user; document.getElementById('pop_phone').innerText = phone; document.getElementById('pop_point').innerText = points; document.getElementById('manualModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('manualModal').style.display = 'none'; }
    function confirmManual(noteText) { if (activeSn) { setNote(activeSn, noteText); closeModal(); } }
    function copyField(text) { const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); alert("Copied: " + text); }
    function copyAllDetails(amount, name, bank, ac, ifsc) { const t = `Amount: ${amount}\nName: ${name}\nBank: ${bank}\nA/c: ${ac}\nIFSC: ${ifsc}`; copyField(t); }

    let currentApprovalData = {};
    function openApprovalPopup(sn, name, mobile, amount) {
        currentApprovalData = { sn, name, mobile, amount };
        document.getElementById('display_name').innerText = name;
        document.getElementById('wp_message_text').value = `*WITHDRAWAL SUCCESSFULLY*\n\nYour amount of ${amount} has been processed.\n\nThank you for playing!`;
        document.getElementById('approvalModal').style.display = 'flex';
    }
    function closeApprovalModal() {
        // Cancel/X only closes the popup — it never touches the database.
        // The withdraw_requests row was already updated to status='1' the moment
        // "Accepted" was clicked (see requestApproved handler below), before this
        // popup ever opened. So closing this modal cannot "undo" that update.
        document.getElementById('approvalModal').style.display = 'none';
    }
    function executeFinalApproval(){
        const msg = document.getElementById("wp_message_text").value;
        window.open(
            "https://api.whatsapp.com/send?phone=" +
            currentApprovalData.mobile +
            "&text=" + encodeURIComponent(msg),
            "_blank"
        );
    }
</script>
<?php if(isset($_GET['wp'])){ ?>
<script>
currentApprovalData = {
    mobile: "<?php echo addslashes($_GET['mobile'] ?? ''); ?>"
};

document.getElementById("display_name").innerText = <?php echo json_encode(urldecode($_GET['name'] ?? '')); ?>;

document.getElementById("wp_message_text").value = <?php echo json_encode(
    "*WITHDRAWAL SUCCESSFULLY*\n\nYour amount of " . ($_GET['amount'] ?? '') . " has been processed.\n\nThank you for playing!"
); ?>;

document.getElementById("approvalModal").style.display = "flex";
</script>
<?php } ?>
<?php
    $today = date('Y-m-d'); $stamp = time(); 
    if(isset($_POST['requestRejected'])){
        $id = $_POST['id'];
        $today_date = date('Y-m-d');
        $info = mysqli_fetch_array(mysqli_query($con,"select mobile, amount from withdraw_requests where sn='$id'"));
        $mobile = $info['mobile']; $amount = $info['amount'];
        $user_data = mysqli_fetch_array(mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile'"));
        $wallet_after = ($user_data['wallet'] ?? 0) + $amount;
        mysqli_query($con,"update withdraw_requests set status='2', date='$today_date' where sn='$id'");
        mysqli_query($con,"UPDATE users set wallet='$wallet_after' where mobile='$mobile'");
        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$amount','".$user_data['wallet']."','$wallet_after','1','Withdraw cancelled','user','$stamp')");
        echo "<script>window.location.href=window.location.href;</script>";
        exit;
    }

    if (isset($_POST['requestApproved'])) {

        $id = $_POST['id'];
        $today_date = date('Y-m-d');
        $pointsAdd = $_POST['amount'];

        // 1) Update the withdraw request status in the database FIRST.
        mysqli_query($con,"UPDATE withdraw_requests
            SET status='1', date='$today_date'
            WHERE sn='$id'");

        $uInfo = mysqli_fetch_array(mysqli_query($con,
            "SELECT mobile FROM withdraw_requests WHERE sn='$id'"));

        $mobile = $uInfo['mobile'];

        $user_data = mysqli_fetch_array(mysqli_query($con,
            "SELECT wallet FROM users WHERE mobile='$mobile'"));

        mysqli_query($con,"INSERT INTO transactions
            (user,amount,wallet_before,wallet_after,type,remark,owner,created_at)
            VALUES
            ('$mobile','$pointsAdd',
            '".$user_data['wallet']."',
            '".$user_data['wallet']."',
            '0','Withdraw to Bank','user','$stamp')");

        $username = $_POST['username'];

        // 2) THEN redirect to the same page with ?wp=1 so the popup opens.
        // Fixed: single-line JS string (no raw newlines) + everything urlencoded.
        $redirect_url = "withdraw-points-pending.php?wp=1"
            . "&mobile=" . urlencode($mobile)
            . "&amount=" . urlencode($pointsAdd)
            . "&name=" . urlencode($username);

        echo "<script>window.location.href=" . json_encode($redirect_url) . ";</script>";
        exit;
    }
} 
include('footer.php');
?>