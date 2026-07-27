<?php 
include('header.php'); 

// ✅ Permission Check
if (in_array(12, $HiddenProducts)) {

    // ✅ Get mobile from URL
    $mobile = $_GET['mobile'] ?? '';
    $user_name = "User";
    $user_email = "";

    if (!empty($mobile)) {
        $safe_mobile = mysqli_real_escape_string($con, $mobile);
        $u_query = mysqli_query($con, "SELECT name, email FROM users WHERE mobile='$safe_mobile'");
        
        if ($u_row = mysqli_fetch_assoc($u_query)) {
            $user_name  = $u_row['name'];
            $user_email = $u_row['email'];
        }
    }
?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    body {
        background-color: #f1f3f4;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .page-wrap {
        padding: 15px;
        /*max-width: 550px;*/
        margin: auto;
    }

    /* Header */
    .username-card {
        background: #fff;
        border: 1px solid #ccc;
        font-size: 20px;
        font-weight: 600;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 15px;
    }

    /* Log Card */
    .activity-card {
        background: #fff;
        border: 1px solid #d1d1d1;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .act-remark {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .act-details {
        font-size: 14px;
        color: #444;
        white-space: pre-wrap;
    }

    .act-footer {
        font-size: 13px;
        color: #777;
        margin-top: 8px;
    }
</style>

<div class="page-wrap">

    <!-- ✅ Username -->
    <div class="username-card">
        Username : <?php echo htmlspecialchars($user_name); ?>
    </div>

    <!-- ✅ Logs -->
    <div class="logs-container">

        <?php 
        if (!empty($user_email)) {

            $safe_email = mysqli_real_escape_string($con, $user_email);

            $sql = "SELECT * FROM login_logs 
                    WHERE user_email = '$safe_email' 
                    ORDER BY log_id DESC";

            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

                    // ✅ Timestamp fallback
                    $display_time = ($row['login_timestamp'] != '0000-00-00 00:00:00')
                                    ? $row['login_timestamp']
                                    : $row['created_at'];
        ?>

            <div class="activity-card">

                <div class="act-remark">
                    <?php echo htmlspecialchars($row['remark']); ?>
                </div>

                <div class="act-details">
                    <?php echo htmlspecialchars($row['user_agent']); ?>
                </div>

                <div class="act-footer">
                    <?php echo date('d M Y, h:i A', strtotime($display_time)); ?>
                </div>

            </div>

        <?php 
                }

            } else {
                echo "<div style='padding:20px;background:#fff;border-radius:10px;text-align:center;'>No activity logs found.</div>";
            }

        } else {
            echo "<div style='padding:20px;background:#fff;border-radius:10px;text-align:center;'>Invalid user.</div>";
        }
        ?>

    </div>

</div>

<?php 
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); 
?>