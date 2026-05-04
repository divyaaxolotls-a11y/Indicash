<?php 
include('header.php');
if (isset($_POST['saveNoteSimple'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $note = mysqli_real_escape_string($con, $_POST['note_content']);
    
    // Using 'info' column as per your DB structure
    mysqli_query($con, "UPDATE withdraw_requests SET info='$note' WHERE sn='$id'");
    
    echo "<script>window.location.href=window.location.href;</script>";
    exit;
}
if (in_array(13, $HiddenProducts)){

    $whereConditions = [];
    $search_user = '';
    
    /* DATE FILTER */
    if (!empty($_GET['date'])) {
        $date = mysqli_real_escape_string($con, $_GET['date']);
        $whereConditions[] = "DATE(wr.created_at) = '$date'";
    }
    
    /* STATUS FILTER */
    if (!empty($_GET['status'])) {
        $status = $_GET['status'];
        if ($status == 'send' || $status == 'pending') { $whereConditions[] = "wr.status = 0"; }
        elseif ($status == 'processing') { $whereConditions[] = "wr.status = 1"; }
        elseif ($status == 'attempt') { $whereConditions[] = "wr.status = 3"; }
        elseif ($status == 'manual') { $whereConditions[] = "wr.status = 4"; }
        elseif ($status == 'wrong') { $whereConditions[] = "wr.status = 2"; }
    }  elseif (empty($_GET['date']) && empty($_GET['search_user'])) {
        $whereConditions[] = "wr.status = 0"; 
    }
    
    /* SEARCH USER */
    if (!empty($_GET['search_user'])) {
        $search_user = mysqli_real_escape_string($con, $_GET['search_user']);
        $whereConditions[] = "(wr.mobile LIKE '%$search_user%' OR wr.holder LIKE '%$search_user%' OR u.name LIKE '%$search_user%')";
    }
    
    $sql = "SELECT wr.*, u.name as u_name, u.wallet as u_wallet FROM withdraw_requests wr LEFT JOIN users u ON wr.mobile = u.mobile";
    if (!empty($whereConditions)) { $sql .= " WHERE " . implode(' AND ', $whereConditions); }
    $sql .= " ORDER BY wr.sn DESC";
    
    $result = mysqli_query($con, $sql);
    $total_withdraw_amount = 0;
    $count = mysqli_num_rows($result);
    while ($calc = mysqli_fetch_array($result)) { $total_withdraw_amount += $calc['amount']; }
    mysqli_data_seek($result, 0);
?>

<style>
    body { background-color: #f0f2f5; font-family: 'Source Sans Pro', sans-serif; }
    
    /* MODIFIED: Increased width for desktop, full width on mobile */
    .main-container { 
        padding: 15px; 
        width: 100%; 
        max-width: 900px; /* Increased from 500px */
        margin: auto; 
    }

    /* Header & Filter */
    .page-title { background: black; color: white; text-align: center; padding: 10px; border-radius: 8px; font-weight: bold; margin-bottom: 15px; }
    
    .filter-box { background: white; padding: 15px; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #ddd; }
    
    /* MODIFIED: Inputs stay side-by-side on desktop */
    .input-row { display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
    .input-row input, .input-row select { flex: 1; min-width: 150px; border-radius: 8px; border: 1px solid #ccc; padding: 10px; font-size: 14px; }
    
    .btn-filter { width: 100%; background: #007bff; color: white; border: none; padding: 12px; border-radius: 20px; font-weight: bold; cursor: pointer; }
    .total-display { text-align: center; font-weight: bold; font-size: 16px; margin: 15px 0; color: #333; }

    /* Card Layout */
    .request-card { background: #d1d1d1; border: 2px solid #003366; border-radius: 12px; margin-bottom: 12px; overflow: hidden; cursor: pointer; transition: 0.3s; }
    .request-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    .card-summary { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; }
    .user-info .mobile { font-size: 13px; color: #444; }
    .user-info .name { font-size: 20px; font-weight: bold; color: black; text-transform: capitalize; }
    .point-info { text-align: right; }
    .point-info .label { font-size: 12px; font-weight: bold; color: #555; }
    .point-info .value { font-size: 24px; font-weight: bold; color: black; }

    /* Expanded Detail Section */
    .card-detail { display: none; background: #003366; color: white; padding: 20px; border-top: 1px solid #002244; }
    
    /* MODIFIED: Scrollable buttons for small screens, wrap for desktop */
    .btn-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; justify-content: center; }
    .btn-pill { border-radius: 20px; border: none; padding: 7px 18px; font-size: 13px; color: white; font-weight: 600; cursor: pointer; }
    .btn-blue { background: #007bff; }
    .btn-green { background: #28a745; }
    .btn-yellow { background: #ffc107; color: black; }
    .btn-teal { background: #17a2b8; }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 14px; margin-bottom: 20px; line-height: 1.6; }
    
    .bank-info-container { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
    .bank-info-box { background: #007bff; border-radius: 6px; padding: 6px 12px; font-size: 13px; font-weight: 500; }
    
    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 8px; margin-top: 20px; }
    .btn-action { font-size: 12px; padding: 8px 5px; border-radius: 20px; border: none; color: white; text-align: center; cursor: pointer; font-weight: 600; }
    
    .btn-save-note { width: 100%; background: #17a2b8; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    
    .footer-actions { display: flex; justify-content: space-between; margin-top: 25px; align-items: center; gap: 10px; }
    .btn-final { border-radius: 8px; padding: 12px 25px; border: none; color: white; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; justify-content: center; }

/* Manual Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    align-items: center; justify-content: center;
}
.modal-content {
    background: white;
    width: 90%; max-width: 400px;
    border-radius: 4px; overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.modal-header {
    padding: 15px; border-bottom: 1px solid #eee;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-header h4 { margin: 0; font-size: 1.2rem; color: #333; }
.modal-body { padding: 20px; color: #333; font-size: 16px; line-height: 1.6; }
.modal-footer {
    padding: 20px; display: flex; justify-content: center; gap: 20px;
}
.btn-payout-modal { background: #ffc107; color: #000; border: none; padding: 10px 25px; border-radius: 4px; font-weight: bold; font-size: 18px; cursor: pointer; }
.btn-other-modal { background: #17a2b8; color: #fff; border: none; padding: 10px 25px; border-radius: 4px; font-weight: bold; font-size: 18px; cursor: pointer; }
    /* Fix for very wide screens */
    @media (min-width: 1200px) {
        .main-container { max-width: 1000px; }
    }
</style>

<div class="main-container">
    <div class="page-title">Withdraw Money Request</div>

    <!-- Filters -->
    <form method="get" class="filter-box">
        <div class="input-row">
            <input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>" placeholder="Date">
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
            <input type="text" name="search_user" placeholder="search" value="<?php echo $_GET['search_user'] ?? ''; ?>">
            <button type="submit" class="btn-filter">Filter</button>
        </div>
    </form>

    <div class="total-display">Amount : <?php echo $total_withdraw_amount; ?> (<?php echo $count; ?>)</div>

    <!-- Request Cards -->
    <?php 
    $i = 0;
    while ($row = mysqli_fetch_array($result)) { 
        $i++;
        $unique_id = "req_".$row['sn'];
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

        <!-- Detail View (Hidden by default) -->
        <div class="card-detail" id="<?php echo $unique_id; ?>" onclick="event.stopPropagation();">
            <div class="btn-row">
                 <a href="user-wallet-history.php?user_mobile=<?php echo $row['mobile']; ?>" class="btn-pill btn-blue" style="text-decoration: none;">Transaction</a>
                <a href="tel:<?php echo $row['mobile']; ?>" class="btn-pill btn-blue" style="text-decoration: none;">Call</a>
                <a href="https://wa.me/<?php echo $row['mobile']; ?>?text=Hello <?php echo urlencode($row['u_name']); ?>, your withdrawal request of <?php echo $row['amount']; ?> points is being processed." 
                   class="btn-pill btn-green" style="text-decoration: none;">QR Msg</a>
                <a href="user-profile.php?userID=<?php echo $row['mobile']; ?>" class="btn-pill btn-yellow" style="text-decoration: none;">Profile</a>           
                 <button class="btn-pill btn-teal" onclick="copyAllDetails('<?php echo $row['amount']; ?>', '<?php echo addslashes(strtoupper($row['u_name'] ?? $row['holder'])); ?>', '<?php echo addslashes(strtoupper($row['bank'] ?? 'N/A')); ?>', '<?php echo $row['ac']; ?>', '<?php echo $row['ifsc']; ?>')">Copy All</button>
            </div>

            <div class="detail-grid">
                <div>Withdrawal Money<br><span style="font-size: 16px; font-weight:bold;">Username : <?php echo htmlspecialchars($row['u_name']); ?></span></div>
                <div style="text-align: right;">Point : <span style="background:green; padding:2px 5px; border-radius:4px; cursor:pointer;" onclick="copyField('<?php echo $row['amount']; ?>')"><?php echo $row['amount']; ?></span><br>Wallet : <?php echo $row['u_wallet']; ?></div>
                
                <!--<div>Request Date : <?php echo date('d/m/Y', strtotime($row['created_at'])); ?><br><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>-->
                <!--<div style="text-align: right;">Type : <?php echo $row['mode'] ?? 'bank'; ?><br>-->
                <!--    Status : Pending <span class="status-badge"><?php echo htmlspecialchars($row['holder']); ?>-</span>-->
                <!--</div>-->
                <div>
                    Request Date : <?php echo date('d/m/Y', strtotime($row['created_at'])); ?><br>
                    <?php echo date('h:i A', strtotime($row['created_at'])); ?><br>
                    <span style="display:inline-block; margin-top:5px;">
                        Status : Pending <span class="status-badge"><?php// echo htmlspecialchars($row['holder']); ?></span>
                    </span>
                </div>
                
                <!-- RIGHT COLUMN: Only Type -->
                <div style="text-align: right;">
                    Type : <?php echo $row['mode'] ?? 'bank'; ?>
                </div>
            </div>

          <div class="bank-info-container">
                <div class="bank-info-box" style="cursor:pointer;" onclick="copyField('<?php echo addslashes(strtoupper($row['holder'])); ?>')">Name : <?php echo strtoupper($row['holder']); ?></div>
                <div class="bank-info-box" style="cursor:pointer;" onclick="copyField('<?php echo addslashes(strtoupper($row['bank'] ?? 'N/A')); ?>')">Bank : <?php echo strtoupper($row['bank'] ?? 'N/A'); ?></div>
                <div class="bank-info-box" style="cursor:pointer;" onclick="copyField('<?php echo $row['ac']; ?>')">A/c : <?php echo $row['ac']; ?></div>
                <div class="bank-info-box" style="cursor:pointer;" onclick="copyField('<?php echo $row['ifsc']; ?>')">IFSC : <?php echo $row['ifsc']; ?></div>
            </div>

            <!--<div class="action-grid">-->
            <!--    <button class="btn-action btn-green">Send Request</button>-->
            <!--    <button class="btn-action btn-yellow">Processing</button>-->
            <!--    <button class="btn-action" style="background: #e83e8c;">Attempt</button>-->
            <!--    <button class="btn-action" style="background: #343a40;">Manual</button>-->
            <!--    <button class="btn-action btn-teal">Pending</button>-->
            <!--    <button class="btn-action" style="background: #dc3545;">Wrong Detail</button>-->
            <!--    <button class="btn-action" style="background: #6c757d;">Reset</button>-->
            <!--</div>-->

            <!--<textarea class="form-control mt-3" rows="2" placeholder="Note..."></textarea>-->
            <!-- Updated Action Grid -->
            <div class="action-grid">
                <!-- Send Request: Simple Alert -->
                <button type="button" class="btn-action btn-green" onclick="alert('Please do it manually')">Send Request</button>
                
                <!-- Processing: Sets text to Processing -->
                <button type="button" class="btn-action btn-yellow" onclick="setNote('<?php echo $row['sn']; ?>', 'Processing')">Processing</button>
                
                <!-- Attempt: Sets text to Attempt -->
                <button type="button" class="btn-action" style="background: #e83e8c;" onclick="setNote('<?php echo $row['sn']; ?>', 'Attempt')">Attempt</button>
                
                <!-- Manual: Prompt for Payout or Others -->
                <!--<button type="button" class="btn-action" style="background: #343a40;" onclick="handleManual('<?php echo $row['sn']; ?>')">Manual</button>-->
                <button type="button" class="btn-action" style="background: #343a40;" 
                    onclick="openManualModal('<?php echo $row['sn']; ?>', '<?php echo htmlspecialchars($row['u_name']); ?>', '<?php echo $row['mobile']; ?>', '<?php echo $row['amount']; ?>')">
                    Manual
                </button>
                <!-- Pending: Sets text to Pending -->
                <button type="button" class="btn-action btn-teal" onclick="setNote('<?php echo $row['sn']; ?>', 'Pending')">Pending</button>
                
                <!-- Wrong Detail: Sets text to Wrong Detail -->
                <button type="button" class="btn-action" style="background: #dc3545;" onclick="setNote('<?php echo $row['sn']; ?>', 'Wrong Detail')">Wrong Detail</button>
                
                <!-- Reset: Clears the note -->
                <button type="button" class="btn-action" style="background: #6c757d;" onclick="setNote('<?php echo $row['sn']; ?>', '')">Reset</button>
            </div>
            
            <!-- Added ID to Textarea -->
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                
                <!-- We load existing note from 'info' column into the textarea -->
                <textarea name="note_content" id="note_<?php echo $row['sn']; ?>" class="form-control mt-3" rows="2" placeholder="Note..."><?php echo htmlspecialchars($row['info']); ?></textarea>
                
                <button type="submit" name="saveNoteSimple" class="btn-save-note">Save Note</button>
            </form>

            <div class="footer-actions">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                    <!--<button type="submit" name="requestApproved" class="btn-final btn-blue"><i class="fas fa-check"></i> Accepted</button>-->
                    <button type="button" class="btn-final btn-blue" 
                        onclick="openApprovalPopup('<?php echo $row['sn']; ?>', '<?php echo htmlspecialchars($row['u_name'] ?? $row['holder']); ?>', '<?php echo $row['mobile']; ?>', '<?php echo $row['amount']; ?>')">
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
<div id="manualModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Select Manual Type</h4>
            <span style="cursor:pointer; font-size:24px;" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            Username : <span id="pop_user"></span><br>
            Phone : <span id="pop_phone"></span><br>
            Point : <span id="pop_point"></span>
        </div>
        <div class="modal-footer">
            <button class="btn-payout-modal" onclick="confirmManual('Manual Payout')">payout</button>
            <button class="btn-other-modal" onclick="confirmManual('Manual Others')">Other</button>
        </div>
    </div>
</div>
<!-- Approval Modal -->
<div id="approvalModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:10000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:#fff; width:90%; max-width:450px; border-radius:10px; overflow:hidden; padding:20px;">
        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            <h4 style="margin:0; font-size:22px;">Name : <span id="display_name" style="font-weight:normal;"></span></h4>
            <span style="cursor:pointer; font-size:28px;" onclick="closeApprovalModal()">&times;</span>
        </div>
        <div class="modal-body">
            <textarea id="wp_message_text" style="width:100%; border:1px solid #ddd; border-radius:15px; padding:15px; height:120px; font-size:16px; color:#333;"></textarea>
        </div>
        <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
            <button type="button" onclick="closeApprovalModal()" style="background:#6c757d; color:#fff; border:none; padding:10px 25px; border-radius:5px; font-weight:bold; cursor:pointer;">Close</button>
            <button type="button" onclick="executeFinalApproval()" style="background:#28a745; color:#fff; border:none; padding:10px 25px; border-radius:5px; font-weight:bold; cursor:pointer;">Send</button>
        </div>
    </div>
</div>

<!-- Hidden Form to process the Database update after clicking Send -->
<form id="hiddenProcessForm" method="post" style="display:none;">
    <input type="hidden" name="id" id="hidden_sn">
    <input type="hidden" name="amount" id="hidden_amount">
    <input type="hidden" name="requestApproved" value="1">
</form>
<script>
    function toggleDetail(id) {
        var element = document.getElementById(id);
        if (element.style.display === "block") {
            element.style.display = "none";
        } else {
            // Optional: Close all others first
            document.querySelectorAll('.card-detail').forEach(el => el.style.display = 'none');
            element.style.display = "block";
        }
    }
    
     function setNote(sn, text) {
        var textarea = document.getElementById('note_' + sn);
        if (textarea) {
            textarea.value = text;
        }
    }

    // Function specifically for the Manual button logic
    function handleManual(sn) {
        // Simple confirmation box to choose path
        // OK = Payout, Cancel = Others
        var choice = confirm("Click 'OK' for Manual Payout\nClick 'Cancel' for Manual Others");
        
        if (choice) {
            setNote(sn, "Manual Payout");
        } else {
            setNote(sn, "Manual Others");
        }
    }
    
    let activeSn = null;

function openManualModal(sn, user, phone, points) {
    activeSn = sn;
    document.getElementById('pop_user').innerText = user;
    document.getElementById('pop_phone').innerText = phone;
    document.getElementById('pop_point').innerText = points;
    document.getElementById('manualModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('manualModal').style.display = 'none';
}

function confirmManual(noteText) {
    if (activeSn) {
        setNote(activeSn, noteText);
        closeModal();
    }
}

function setNote(sn, text) {
    // This finds the textarea by ID and sets its value before the user clicks "Save Note"
    var textarea = document.getElementById('note_' + sn);
    if (textarea) {
        textarea.value = text;
    }
}

function copyAllDetails(amount, name, bank, ac, ifsc) {
    // Format the text exactly like the screenshot
    const textToCopy = `Amount: ${amount}\nName: ${name}\nBank : ${bank}\nA/c : ${ac}\nifsc : ${ifsc}`;

    // Create a temporary textarea to copy the text (works on more devices than navigator.clipboard)
    const tempElement = document.createElement('textarea');
    tempElement.value = textToCopy;
    document.body.appendChild(tempElement);
    tempElement.select();
    document.execCommand('copy');
    document.body.removeChild(tempElement);

    // Show the alert exactly as requested
    alert("Copied the text: " + textToCopy);
}

function copyField(text) {
    // 1. Create a temporary hidden textarea
    const tempElement = document.createElement('textarea');
    tempElement.value = text;
    document.body.appendChild(tempElement);
    
    // 2. Select and copy the text
    tempElement.select();
    document.execCommand('copy');
    
    // 3. Remove the temporary element
    document.body.removeChild(tempElement);

    // 4. Show the alert exactly like your screenshot
    alert("Copied the text: " + text);
}

let currentApprovalData = {};

function openApprovalPopup(sn, name, mobile, amount) {
        alert('success');

    // Store data for the final click
    currentApprovalData = { sn, name, mobile, amount };

    // Set Name in Header
    document.getElementById('display_name').innerText = name;

    // Set Default Message in Textarea
    const defaultMsg = `*WITHDRAWAL SUCCESSFULLY*\n\nYour amount of ${amount} has been processed to your bank account.\n\nThank you for playing with us!`;
    document.getElementById('wp_message_text').value = defaultMsg;

    // Show Modal
    document.getElementById('approvalModal').style.display = 'flex';
}

function closeApprovalModal() {
    document.getElementById('approvalModal').style.display = 'none';
}

function executeFinalApproval() {
    const message = document.getElementById('wp_message_text').value;
    const mobile = currentApprovalData.mobile;
    // console.log(mobile , message);
    // 1. OPEN WHATSAPP with the message
    const waUrl = `https://wa.me/${mobile}?text=${encodeURIComponent(message)}`;
    window.open(waUrl, '_blank');

    // 2. SUBMIT FORM TO UPDATE DATABASE (The existing PHP logic)
    document.getElementById('hidden_sn').value = currentApprovalData.sn;
    document.getElementById('hidden_amount').value = currentApprovalData.amount;
    
    // Submit the form
    document.getElementById('hiddenProcessForm').submit();
}
</script>

<?php
    // --- Existing Backend Logic Preserved ---
    if(isset($_POST['requestRejected'])){
        $id = $_POST['id'];
        $info = mysqli_fetch_array(mysqli_query($con,"select user, amount from withdraw_requests where sn='$id'"));
        $mobile = $info['user'];
        $amount = $info['amount'];
        mysqli_query($con,"update withdraw_requests set status='2' where sn='$id'");
        mysqli_query($con,"UPDATE users set wallet=wallet+$amount where mobile='$mobile'");
        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$amount','1','Withdraw cancelled','user','$stamp')");
        echo "<script>window.location.href= 'withdraw-points-request.php';</script>";
    }

    if (isset($_POST['requestApproved'])) {
        $id = $_POST['id'];
        $pointsAdd = $_POST['amount'];
        mysqli_query($con, "UPDATE withdraw_requests SET status='1' WHERE sn='$id'");
        $uInfo = mysqli_fetch_array(mysqli_query($con, "SELECT user FROM withdraw_requests WHERE sn='$id'"));
        $mobile = $uInfo['user'];
        mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$pointsAdd','0','Withdraw to Bank','user','$stamp')");
        echo "<script>window.location.href= 'withdraw-points-request.php';</script>";
    }
} 
include('footer.php');
?>