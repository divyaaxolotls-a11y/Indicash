<?php 
include('header.php'); 
$selected_mobile = $_GET['mobile'] ?? '';
$user_name_for_display = '';

if ($selected_mobile) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$selected_mobile'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

if (isset($_POST['update_record_btn'])) {
    $row_id = mysqli_real_escape_string($con, $_POST['row_id']);
    $new_msg = mysqli_real_escape_string($con, $_POST['new_message']);
    $new_time = $_POST['new_time']; // Format: 14:30

    // Get the existing date to keep the same Day/Month/Year
    $res = mysqli_query($con, "SELECT created_at FROM payments WHERE id = '$row_id'");
    $row = mysqli_fetch_assoc($res);
    $existing_date = date('Y-m-d', strtotime($row['created_at']));
    
    // Combine old date + new simple time
    $new_datetime = $existing_date . ' ' . $new_time . ':00';

    $update_q = "UPDATE `payments` SET `remark` = '$new_msg', `created_at` = '$new_datetime' WHERE `id` = '$row_id'";
    if (mysqli_query($con, $update_q)) {
        echo "<script>alert('Updated successfully!'); window.location.href='add-money-manual.php';</script>";
    }
}
// --- 1. HANDLE FORM SUBMISSION ---
// if (isset($_POST['submit_money'])) {
//     $mobile = mysqli_real_escape_string($con, $_POST['user_mobile']);
//     $amount = (float)$_POST['amount'];
//     $msg = mysqli_real_escape_string($con, $_POST['message']);
//     $time = $_POST['time']; 
//     $date = date('Y-m-d');
//     $datetime = $date . ' ' . date('H:i:s', strtotime($time));

//     // Get User Name
//     $u_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$mobile'");
//     $u_data = mysqli_fetch_assoc($u_res);
//     $name = $u_data['name'];

//     // Insert into payments table (as manual)
//     $q1 = "INSERT INTO `payments` (`name`, `mobile`, `amount`, `status`, `payment_id`, `created_at`, `updated_at`, `remark`) 
//           VALUES ('$name', '$mobile', '$amount', 'SUCCESS', '', '$datetime', '$datetime', '$msg')";
    
//     if (mysqli_query($con, $q1)) {
//         // Update User Wallet
//         mysqli_query($con, "UPDATE users SET wallet = wallet + $amount WHERE mobile = '$mobile'");
        
//         // Log Transaction
//         mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `type`, `remark`, `owner`, `created_at`) 
//                           VALUES ('$mobile', '$amount', '1', 'Added Manually: $msg', 'admin', '$datetime')");
        
//         echo "<script>alert('Money added successfully!'); window.location.href='add-money-manual.php';</script>";
//     }
// }

if (isset($_POST['submit_money'])) {
    $mobile = mysqli_real_escape_string($con, $_POST['user_mobile']);
    $amount = (float)$_POST['amount'];
    $msg = mysqli_real_escape_string($con, $_POST['message']);
    $time = $_POST['time']; 
    $date = date('Y-m-d');
    $datetime = $date . ' ' . date('H:i:s', strtotime($time));

    // 1. Get User Data (Name and CURRENT Wallet balance)
    $u_res = mysqli_query($con, "SELECT name, wallet FROM users WHERE mobile='$mobile' LIMIT 1");
    $u_data = mysqli_fetch_assoc($u_res);
    
    $name = $u_data['name'];
    $wallet_before = (float)$u_data['wallet']; // Store current balance

    // 2. Calculate wallet_after
    $wallet_after = $wallet_before + $amount;

    // 3. Insert into payments table (as manual)
    $q1 = "INSERT INTO `payments` (`name`, `mobile`, `amount`, `status`, `payment_id`, `created_at`, `updated_at`, `remark`) 
           VALUES ('$name', '$mobile', '$amount', 'SUCCESS', '', '$datetime', '$datetime', '$msg')";
    
    if (mysqli_query($con, $q1)) {
        
        // 4. Update User Wallet using the calculated total
        mysqli_query($con, "UPDATE users SET wallet = '$wallet_after' WHERE mobile = '$mobile'");
        
        // 5. Log Transaction with BEFORE and AFTER values
        $trans_q = "INSERT INTO `transactions` (`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `owner`, `created_at`, `dated_on`) 
                    VALUES ('$mobile', '$amount', '$wallet_before', '$wallet_after', '1', 'Added Manually: $msg', 'admin', '$datetime', '$datetime')";
        
        if(mysqli_query($con, $trans_q)) {
            echo "<script>alert('Money added successfully!'); window.location.href='add-money-manual.php';</script>";
        } else {
            echo "Transaction Error: " . mysqli_error($con);
        }
    }
}

?>
<style>
    /* Main Theme Colors */
    body { background-color: #C1FFC1 !important; font-family: 'Source Sans Pro', sans-serif; }
    .content-wrapper { background-color: #C1FFC1 !important; }

    .main-container { padding: 15px; max-width: 500px; margin: auto; }

    /* --- BLACK HEADER BANNER --- */
    .black-title {
        background-color: black; color: white; border-radius: 50px;
        text-align: center; padding: 10px; font-weight: bold;
        font-size: 1.3rem; margin-bottom: 20px;
    }

    /* Balance Info Labels */
    .info-text { font-size: 19px; color: #333; margin-bottom: 8px; font-weight: 500; }
    .input-label { font-size: 19px; color: #333; margin-top: 15px; margin-bottom: 5px; display: block; }

    /* Rounded Box Inputs */
    .rounded-box {
        border-radius: 25px !important; border: 1px solid #ccc;
        height: 45px !important; padding: 0 20px !important; font-size: 16px; width: 100%;
        background-color: #fff !important;
    }
    textarea.rounded-box { height: 100px !important; padding: 15px !important; border-radius: 30px !important; }

    /* Buttons */
    .btn-submit { background-color: #007bff; color: white; border-radius: 25px; border: none; padding: 8px 35px; font-size: 18px; margin-top: 15px; font-weight: 500; }
    .btn-date { background-color: #d9435e; color: white; border-radius: 15px; border: none; padding: 8px 10px; font-weight: bold; width: 100%; font-size: 16px; }

    /* Total Indicator */
    .total-bar { background-color: black; color: white; text-align: center; padding: 6px; font-weight: bold; font-size: 17px; margin-top: 15px; }

    /* --- TABLE UI (MATCHING PINKISH ROWS) --- */
    .custom-table { width: 100%; border-collapse: collapse; margin-top: 0; border: 1.5px solid #000; }
    
    .custom-table thead th { 
        background: #333; color: #fff; padding: 8px; 
        border: 1px solid #777; font-size: 17px; 
        font-weight: normal; text-transform: lowercase;
    }

    .custom-table tbody td { 
        padding: 10px 5px; border: 1px solid #999; 
        text-align: center; 
        background: #FFE4C4; /* This creates the pinkish/peach look from the screenshot */
        font-size: 16px; 
        color: #000; /* Black text */
        vertical-align: middle;
        font-weight: 500;
    }
    
    .date-stack { font-size: 15px; line-height: 1.2; }
    .dtp { 
    z-index: 99999 !important; 
}
.dtp-picker-month { display: none; } /* Hide month if it accidentally appears in time mode */

    /* Search Dropdown styling */
    .select2-container--bootstrap4 .select2-selection--single { border-radius: 25px !important; height: 45px !important; padding-top: 8px !important; }
</style>

<div class="main-container">
    <!-- Header Title -->
    <div class="black-title">Add Money Manually</div>

       <form method="post">
        <label class="input-label">Username</label>
        <?php if ($selected_mobile): ?>
            <!-- From popup -->
            <input type="text" class="form-control rounded-box" value="<?php echo $user_name_for_display; ?>" readonly>
            <input type="hidden" name="user_mobile" value="<?php echo $selected_mobile; ?>">
        
        <?php else: ?>
            <!-- Default -->
            <select name="user_mobile" id="user_select" class="form-control select2bs4 rounded-box" required>
                <option value="">Search for a user</option>
                <?php 
                $users = mysqli_query($con, "SELECT mobile, name, wallet FROM users ORDER BY name ASC");
                while($u = mysqli_fetch_assoc($users)) {
                    echo '<option value="'.$u['mobile'].'" data-name="'.$u['name'].'" data-wallet="'.$u['wallet'].'">'.$u['name'].' ('.$u['mobile'].')</option>';
                }
                ?>
            </select>
        <?php endif; ?>

        <label class="input-label">Time</label>
        <input type="time" name="time" class="form-control rounded-box" value="<?php echo date('H:i'); ?>">

        <label class="input-label">Amount</label>
        <input type="number" name="amount" class="rounded-box" required>

        <label class="input-label">Massage</label>
        <textarea name="message" class="rounded-box"></textarea>

        <button type="submit" name="submit_money" class="btn-submit">Submit</button>
    </form>

    <!-- Date Selection -->
    <form method="POST">
        <div class="row mt-4 align-items-center no-gutters">
            
            <div class="col-7 pr-2">
                <input 
                    type="date" 
                    name="filter_date"
                    class="form-control rounded-box" 
                    style="height:40px !important;" 
                    value="<?php echo $_POST['filter_date'] ?? date('Y-m-d'); ?>"
                >
            </div>
    
            <div class="col-5">
                <button type="submit" name="date_submit" class="btn-date">
                    Date Submit
                </button>
            </div>
    
        </div>
    </form>

    <!-- Today's Total Calculation -->
    <?php 
    $today = date('Y-m-d');
    $sum_q = mysqli_query($con, "SELECT SUM(amount) as total FROM payments WHERE status='SUCCESS' AND payment_id='' AND DATE(created_at) = '$today'");
    $sum_data = mysqli_fetch_assoc($sum_q);
    ?>
    <div class="total-bar">Total :: <?php echo (float)$sum_data['total']; ?></div>

    <!-- Transaction Table -->
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
                if(isset($_POST['filter_date']) && $_POST['filter_date'] != ''){
                    $selected_date = mysqli_real_escape_string($con, $_POST['filter_date']);
                }else{
                    $selected_date = date('Y-m-d'); // default today
                }
                // $history = mysqli_query($con, "SELECT * FROM payments WHERE payment_id='' ORDER BY id DESC LIMIT 15");
                // $history = mysqli_query($con, "
                //     SELECT * FROM payments 
                //     WHERE payment_id='' 
                //     AND DATE(created_at) = '$selected_date'
                //     ORDER BY id DESC 
                //     LIMIT 15
                //     ");
                if($selected_mobile){
                    $history = mysqli_query($con, "
                        SELECT * FROM payments 
                        WHERE payment_id='' 
                        AND mobile='$selected_mobile'
                        AND DATE(created_at) = '$selected_date'
                        ORDER BY id DESC 
                        LIMIT 15
                    ");
                } else {
                    $history = mysqli_query($con, "
                        SELECT * FROM payments 
                        WHERE payment_id='' 
                        AND DATE(created_at) = '$selected_date'
                        ORDER BY id DESC 
                        LIMIT 15
                    ");
                }
                while($h = mysqli_fetch_assoc($history)) {
                    $dt = strtotime($h['created_at']);
                ?>
                <tr>
                    <td style="text-align:left; padding-left:10px;"><?php echo strtolower($h['name']); ?></td>
                    <!--<td class="date-stack">-->
                    <!--    <?php echo date('d-m-Y', $dt); ?><br>-->
                    <!--    <?php echo date('h:i A', $dt); ?>-->
                    <!--</td>-->
                    <td class="date-stack" style="cursor:pointer;" onclick="openSimpleEdit('<?php echo $h['id']; ?>', '<?php echo $h['name']; ?>', '<?php echo addslashes($h['remark']); ?>', '<?php echo date('H:i', $dt); ?>')">
                        <?php echo date('d-m-Y', $dt); ?><br>
                        <?php echo date('h:i:s a', $dt); ?>
                    </td>
                    <td><?php echo $h['remark']; ?></td>
                    <td style="font-weight:bold;"><?php echo $h['amount']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="simpleEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0">
                <h5 class="modal-title">Edit Entry: <span id="edit_name" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="row_id" id="edit_id">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Edit Time</label>
                        <input type="time" name="new_time" id="edit_time" class="form-control rounded-box" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Edit Message</label>
                        <textarea name="new_message" id="edit_msg" class="form-control" style="border-radius:15px; height:80px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:20px;">Cancel</button>
                    <button type="submit" name="update_record_btn" class="btn btn-primary" style="border-radius:20px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Searchable Select
    $('.select2bs4').select2({ theme: 'bootstrap4' });

    // Dynamic Labels Update
    $('#user_select').on('change', function() {
        var selected = $(this).find(':selected');
        var name = selected.data('name');
        var wallet = selected.data('wallet');

        if(name) {
            $('#display_name').text(name);
            $('#display_money').text(wallet);
        } else {
            $('#display_name').text('user_name');
            $('#display_money').text('user_money');
        }
    });
});
function openSimpleEdit(id, name, msg, time24) {
    $('#edit_id').val(id);
    $('#edit_name').text(name);
    $('#edit_msg').val(msg);
    $('#edit_time').val(time24); // Sets the time input (e.g., 14:30)
    $('#simpleEditModal').modal('show');
}
</script>
<?php include('footer.php'); ?>