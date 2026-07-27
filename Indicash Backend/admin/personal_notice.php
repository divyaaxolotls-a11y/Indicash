<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('header.php'); 

/* ================= FETCH USERS LOGIC ================= */
$user_query = "SELECT name, mobile FROM users";
$user_result = mysqli_query($con, $user_query);

/* ================= INSERT NOTICE LOGIC ================= */
if (isset($_POST['submit'])) {

    $notice_to     = $_POST['notice_to'];
    $specific_user = isset($_POST['specific_user']) ? mysqli_real_escape_string($con, $_POST['specific_user']) : '';
    $view_notice   = isset($_POST['view_notice']) ? 1 : 0;

    $title   = mysqli_real_escape_string($con, trim($_POST['title']));
    $message = mysqli_real_escape_string($con, trim($_POST['message']));

    /* ===== Recipient Logic ===== */
    if ($notice_to === 'USERNAME') {

        if (empty($specific_user)) {
            echo "<script>alert('Please select a user');</script>";
            exit;
        }

        // mobile
        $notice_mobile = $specific_user;

        // username fetch from users table
        $u = mysqli_query(
            $con,
            "SELECT name FROM users WHERE mobile = '$notice_mobile' LIMIT 1"
        );
        $urow = mysqli_fetch_assoc($u);

        $notice_username = $urow['name'] ?? NULL;
        $final_recipient = 'USERNAME';

    } else {

        $final_recipient  = 'ALL';
        $notice_mobile    = NULL;
        $notice_username  = NULL;
    }

    if ($notice_to == '' || $title == '' || $message == '') {
        echo "<script>alert('All fields required');</script>";
    } else {

        /* ================= INSERT QUERY ================= */
        $query = "
            INSERT INTO personal_notice
            (notice_to, username, mobile, view_notice, title, message, created_at)
            VALUES (
                '$final_recipient',
                " . ($notice_username ? "'$notice_username'" : "NULL") . ",
                " . ($notice_mobile ? "'$notice_mobile'" : "NULL") . ",
                '$view_notice',
                '$title',
                '$message',
                CURDATE()
            )
        ";

        if (mysqli_query($con, $query)) {
            echo "<script>
                alert('Notice added successfully');
                window.location.href = window.location.href;
            </script>";
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Same CSS Style as before */
.notice-card { background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.notice-card label { font-size: 14px; font-weight: 600; color: #333; display: block; margin-bottom: 5px; margin-top: 10px; }
.notice-card select, .notice-card input[type="text"], .notice-card textarea { width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 12px; font-size: 15px; outline: none; box-sizing: border-box; transition: 0.3s; }
.notice-card input:focus, .notice-card textarea:focus { border-color: #1e88e5; }

/* Select2 Customization */
.select2-container .select2-selection--single { height: 45px !important; border-radius: 8px !important; border: 1px solid #ddd !important; padding: 8px 0; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; }

/* Checkbox */
.view-notice-wrapper { display: flex; align-items: center; gap: 10px; background: #f1f8ff; padding: 10px; border-radius: 8px; margin-top: 15px; margin-bottom: 5px; border: 1px dashed #1e88e5; }
.view-notice-wrapper input[type="checkbox"] { width: 20px !important; height: 20px !important; margin: 0; cursor: pointer; }

/* Button */
.notice-btn { background: #1e88e5; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-size: 16px; width: 100%; margin-top: 20px; cursor: pointer; font-weight: bold; }

/* Display List */
.notice-display { background: #fff; border-left: 5px solid #1e88e5; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.notice-header { display: flex; justify-content: space-between; font-size: 12px; color: #777; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
.notice-title { font-size: 16px; font-weight: bold; color: #222; margin-bottom: 5px; }
.notice-msg { font-size: 14px; color: #555; line-height: 1.5; }
#user_select_box { display: none; margin-top: 10px; }

@media (max-width: 600px) { 
    .notice-card { padding: 15px; } 
    .notice-header { flex-direction: column; gap: 5px; } 
}
</style>

<div class="notice-card">
    <form method="POST" enctype="multipart/form-data"> 

        <label>Select Add Type</label>
        <select name="notice_to" id="notice_type" required onchange="toggleUserSelect()">
            <option value="">Select Option</option>
            <option value="USERNAME">Username</option>
            <option value="ALL">All</option>
        </select>

        <div id="user_select_box">
            <label>Select User (Type to Search)</label>
            <select name="specific_user" id="specific_user" style="width: 100%;">
                <option value="">Select Name</option>
                <?php 
                if (mysqli_num_rows($user_result) > 0) {
                    while ($user = mysqli_fetch_assoc($user_result)) {
                        // UI SAME → name disel, backend la mobile jail
                        echo '<option value="' . $user['mobile'] . '">' . $user['name'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="view-notice-wrapper">
            <input type="checkbox" name="view_notice" id="view_notice" value="1">
            <label for="view_notice">View Notice</label>
        </div>

        <label>Title</label>
        <input type="text" name="title" placeholder="Enter notice title" required>

        <label>Personal Message</label>
        <textarea name="message" rows="4" placeholder="Type your message here..." required></textarea>

        <button type="submit" name="submit" class="notice-btn">Submit Notice</button>

    </form>
</div>

<?php
// Display notices
$result = mysqli_query($con, "SELECT * FROM personal_notice ORDER BY id DESC");
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<div class="notice-display">
    <div class="notice-header">
        <span><i class="fa fa-calendar"></i> <?php echo date('d-m-Y', strtotime($row['created_at'])); ?></span>
        <span>
    To: <strong>
        <?php
        if ($row['notice_to'] === 'USERNAME' && !empty($row['username'])) {
            echo htmlspecialchars($row['username']);
        } else {
            echo htmlspecialchars($row['notice_to']);
        }
        ?>
    </strong>
</span>

        <span><?php echo ($row['view_notice'] == 1) ? '<b style="color:green;">(Visible)</b>' : '<b></b>'; ?></span>
    </div>
    <div class="notice-title"><?php echo htmlspecialchars($row['title']); ?></div>
    <div class="notice-msg"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
</div>
<?php 
    }
} else {
    echo "<p style='text-align:center; color:#777;'>No notices found.</p>";
}
?>

<?php include('footer.php'); ?>

<script>
$(document).ready(function() {
    $('#specific_user').select2({
        placeholder: "Select a User",
        allowClear: true
    });
});

function toggleUserSelect() {
    var type = document.getElementById('notice_type').value;
    var userBox = document.getElementById('user_select_box');
    var userInput = document.getElementById('specific_user');

    if (type === 'USERNAME') {
        userBox.style.display = 'block'; 
        userInput.required = true;       
    } else {
        userBox.style.display = 'none';  
        userInput.required = false;      
        $('#specific_user').val(null).trigger('change');
    }
}
</script>
