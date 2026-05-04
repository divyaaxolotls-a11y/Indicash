<?php 
include('header.php'); 
$selected_mobile = $_GET['mobile'] ?? '';

$user_name_for_display = '';

if ($selected_mobile) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$selected_mobile'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

// --- HANDLE WITHDRAW UPDATE ---
if (isset($_POST['update_withdraw_btn'])) {
    $row_sn = mysqli_real_escape_string($con, $_POST['row_sn']);
    $new_msg = mysqli_real_escape_string($con, $_POST['new_message']);
    $new_time = $_POST['new_time']; // Format: 14:30

    // Get the existing date to keep the same Day/Month/Year
    $res = mysqli_query($con, "SELECT created_at FROM withdraw_requests WHERE sn = '$row_sn'");
    $row = mysqli_fetch_assoc($res);
    $existing_date = date('Y-m-d', strtotime($row['created_at']));
    
    // Combine old date + new simple time
    $new_datetime = $existing_date . ' ' . $new_time . ':00';

    $update_q = "UPDATE `withdraw_requests` SET `info` = '$new_msg', `created_at` = '$new_datetime' WHERE `sn` = '$row_sn'";
    if (mysqli_query($con, $update_q)) {
        echo "<script>alert('Withdrawal updated successfully!'); window.location.href='withdraw-money-manual.php';</script>";
    }
}

// --- 1. HANDLE WITHDRAW SUBMISSION ---
if (isset($_POST['submit_withdraw'])) {
    $mobile = mysqli_real_escape_string($con, $_POST['user_mobile']);
    $amount = (float)$_POST['amount'];
    $msg = mysqli_real_escape_string($con, $_POST['message']);
    $time = $_POST['time']; 
    $date = date('Y-m-d');
    $datetime = $date . ' ' . date('H:i:s', strtotime($time));
    $created_at_date = date('Y-m-d h:i:sa');

    // Get User Name
    $u_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$mobile'");
    $u_data = mysqli_fetch_assoc($u_res);
    $name = $u_data['name'] ?? 'Unknown';

    // 1. Insert into withdraw_requests table
    // mode = 'Manual', status = '1' (Success/Approved)
    $q1 = "INSERT INTO `withdraw_requests` (`mobile`, `amount`, `mode`, `info`, `status`, `holder`, `created_at`, `date`) 
           VALUES ('$mobile', '$amount', 'Manual', '$msg', '1', '$name', '$datetime', '$created_at_date')";
    
    if (mysqli_query($con, $q1)) {
        // 2. Deduct from User Wallet (MINUS)
        mysqli_query($con, "UPDATE users SET wallet = wallet - $amount WHERE mobile = '$mobile'");
        
        // 3. Log Transaction (Type 0 = Debit/Withdrawal)
        mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `type`, `remark`, `owner`, `created_at`) 
                           VALUES ('$mobile', '$amount', '0', 'Withdraw Manually: $msg', 'admin', '$datetime')");
        
        echo "<script>alert('Money withdrawn successfully!'); window.location.href='withdraw-money-manual.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
    }
}
?>

<style>
    /* Main Theme Colors matching your Add Money page */
    body { background-color: #C1FFC1 !important; font-family: 'Source Sans Pro', sans-serif; }
    .content-wrapper { background-color: #C1FFC1 !important; }
    .main-container { padding: 15px; max-width: 500px; margin: auto; }

    .black-title {
        background-color: #d9435e; color: white; border-radius: 50px;
        text-align: center; padding: 10px; font-weight: bold;
        font-size: 1.3rem; margin-bottom: 20px;
    }

    .input-label { font-size: 19px; color: #333; margin-top: 15px; margin-bottom: 5px; display: block; }

    .rounded-box {
        border-radius: 25px !important; border: 1px solid #ccc;
        height: 45px !important; padding: 0 20px !important; font-size: 16px; width: 100%;
        background-color: #fff !important;
    }
    textarea.rounded-box { height: 100px !important; padding: 15px !important; border-radius: 30px !important; }

    .btn-submit { background-color: #007bff; color: white; border-radius: 25px; border: none; padding: 8px 35px; font-size: 18px; margin-top: 15px; font-weight: 500; }
    .btn-date { background-color: #d9435e; color: white; border-radius: 15px; border: none; padding: 8px 10px; font-weight: bold; width: 100%; font-size: 16px; }

    .total-bar { background-color: black; color: white; text-align: center; padding: 6px; font-weight: bold; font-size: 17px; margin-top: 15px; }

    .custom-table { width: 100%; border-collapse: collapse; margin-top: 0; border: 1.5px solid #000; }
    .custom-table thead th { 
        background: #333; color: #fff; padding: 8px; 
        border: 1px solid #777; font-size: 17px; 
        font-weight: normal; text-transform: lowercase;
    }

    .custom-table tbody td { 
        padding: 10px 5px; border: 1px solid #999; 
        text-align: center; 
        background: #FFE4C4; 
        font-size: 16px; 
        color: #000; 
        vertical-align: middle;
        font-weight: 500;
    }
    
    .date-stack { font-size: 15px; line-height: 1.2; }
    .select2-container--bootstrap4 .select2-selection--single { border-radius: 25px !important; height: 45px !important; padding-top: 8px !important; }
</style>

<div class="main-container">
    <div class="black-title">Withdraw Money Manually</div>

    <form method="post">
        <label class="input-label">Username</label>
        <?php if ($selected_mobile): ?>
        <!-- From popup -->
        <input type="text" class="form-control rounded-box" value="<?php echo $user_name_for_display; ?>" readonly>
        <input type="hidden" name="user_mobile" value="<?php echo $selected_mobile; ?>">
        
        <?php else: ?>
            <!-- Default flow -->
            <select name="user_mobile" id="user_select" class="form-control select2bs4 rounded-box" required>
                <option value="">Search for a user</option>
                <?php 
                $users = mysqli_query($con, "SELECT mobile, name, wallet FROM users ORDER BY name ASC");
                while($u = mysqli_fetch_assoc($users)) {
                    echo '<option value="'.$u['mobile'].'" data-wallet="'.$u['wallet'].'">'.$u['name'].' ('.$u['mobile'].')</option>';
                }
                ?>
            </select>
        <?php endif; ?>

        <label class="input-label">Time</label>
        <input type="time" name="time" class="form-control rounded-box" value="<?php echo date('H:i'); ?>">

        <label class="input-label">Amount</label>
        <input type="number" name="amount" class="rounded-box" required>

        <label class="input-label">Message (Reason)</label>
        <textarea name="message" class="rounded-box"></textarea>

        <button type="submit" name="submit_withdraw" class="btn-submit">Submit</button>
    </form>

    <!-- Date Selection -->
    <form method="POST">
        <div class="row mt-4 align-items-center no-gutters">
            <div class="col-7 pr-2">
                <input type="date" name="filter_date" class="form-control rounded-box" style="height:40px !important;" value="<?php echo $_POST['filter_date'] ?? date('Y-m-d'); ?>">
            </div>
            <div class="col-5">
                <button type="submit" name="date_submit" class="btn-date">Date Submit</button>
            </div>
        </div>
    </form>

    <!-- Total Calculation -->
    <?php 
    $filter_date = $_POST['filter_date'] ?? date('Y-m-d');
    $sum_q = mysqli_query($con, "SELECT SUM(amount) as total FROM withdraw_requests WHERE mode='Manual' AND DATE(created_at) = '$filter_date'");
    $sum_data = mysqli_fetch_assoc($sum_q);
    ?>
    <div class="total-bar">Total Withdraw :: <?php echo (float)$sum_data['total']; ?></div>

    <!-- History Table -->
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>username</th>
                    <th>Date</th>
                    <th>Msg</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // $history = mysqli_query($con, "SELECT wr.*, u.name as u_name FROM withdraw_requests wr LEFT JOIN users u ON wr.mobile = u.mobile WHERE wr.mode='Manual' AND DATE(wr.created_at) = '$filter_date' ORDER BY wr.sn DESC LIMIT 20");
                if($selected_mobile){
                        $history = mysqli_query($con, "
                            SELECT wr.*, u.name as u_name 
                            FROM withdraw_requests wr 
                            LEFT JOIN users u ON wr.mobile = u.mobile 
                            WHERE wr.mode='Manual' 
                            AND wr.mobile='$selected_mobile'
                            AND DATE(wr.created_at) = '$filter_date' 
                            ORDER BY wr.sn DESC 
                            LIMIT 20
                        ");
                    } else {
                        $history = mysqli_query($con, "
                            SELECT wr.*, u.name as u_name 
                            FROM withdraw_requests wr 
                            LEFT JOIN users u ON wr.mobile = u.mobile 
                            WHERE wr.mode='Manual' 
                            AND DATE(wr.created_at) = '$filter_date' 
                            ORDER BY wr.sn DESC 
                            LIMIT 20
                        ");
                    }
                while($h = mysqli_fetch_assoc($history)) {
                    $dt = strtotime($h['created_at']);
                ?>
                <tr>
                    <td style="text-align:left; padding-left:10px;"><?php echo strtolower($h['u_name'] ?? $h['holder']); ?></td>
                    <td class="date-stack" style="cursor:pointer;" onclick="openSimpleEdit('<?php echo $h['sn']; ?>', '<?php echo addslashes($h['u_name'] ?? $h['holder']); ?>', '<?php echo addslashes($h['info']); ?>', '<?php echo date('H:i', $dt); ?>')">
                        <?php echo date('d-m-Y', $dt); ?><br>
                        <?php echo date('h:i:s a', $dt); ?>
                    </td>
                    <td><?php echo $h['info']; ?></td>
                    <td style="font-weight:bold; color:#d9435e;">-<?php echo $h['amount']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<!-- SIMPLE EDIT MODAL -->
<div class="modal fade" id="simpleEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0">
                <h5 class="modal-title">Edit Withdraw: <span id="edit_name" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="row_sn" id="edit_sn">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Edit Time</label>
                        <input type="time" name="new_time" id="edit_time" class="form-control rounded-box" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Edit Message (Reason)</label>
                        <textarea name="new_message" id="edit_msg" class="form-control" style="border-radius:15px; height:80px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:20px; padding: 6px 25px;">Cancel</button>
                    <button type="submit" name="update_withdraw_btn" class="btn btn-primary" style="border-radius:20px; padding: 6px 25px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.select2bs4').select2({ theme: 'bootstrap4' });
});

function openSimpleEdit(sn, name, msg, time24) {
    $('#edit_sn').val(sn);
    $('#edit_name').text(name);
    $('#edit_msg').val(msg);
    $('#edit_time').val(time24); // Sets the time input (e.g., 14:30)
    $('#simpleEditModal').modal('show');
}
</script>

<?php include('footer.php'); ?>