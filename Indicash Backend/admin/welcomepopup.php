<?php
// Start output buffering and include header
ob_start(); 
include('header.php');

$success_msg = "";

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_welcome'])) {
    
    $welcome_msg = mysqli_real_escape_string($con, $_POST['welcome_msg']);
    $status = mysqli_real_escape_string($con, $_POST['popup_status']);
    
    // Update the welcome message in settings table
    $check_msg = mysqli_query($con, "SELECT * FROM settings WHERE data_key='welcome_msg'");
    if(mysqli_num_rows($check_msg) > 0) {
        mysqli_query($con, "UPDATE settings SET data='$welcome_msg' WHERE data_key='welcome_msg'");
    } else {
        mysqli_query($con, "INSERT INTO settings (data_key, data) VALUES ('welcome_msg', '$welcome_msg')");
    }

    // Update the active status in settings table
    $check_status = mysqli_query($con, "SELECT * FROM settings WHERE data_key='welcome_msg_status'");
    if(mysqli_num_rows($check_status) > 0) {
        mysqli_query($con, "UPDATE settings SET data='$status' WHERE data_key='welcome_msg_status'");
    } else {
        mysqli_query($con, "INSERT INTO settings (data_key, data) VALUES ('welcome_msg_status', '$status')");
    }

    $success_msg = "Welcome Popup Settings Updated Successfully!";
}

// --- FETCH CURRENT DATA ---
$current_msg = "";
$current_status = "0";

$q = mysqli_query($con, "SELECT data_key, data FROM settings WHERE data_key IN ('welcome_msg', 'welcome_msg_status')");
while($row = mysqli_fetch_assoc($q)) {
    if($row['data_key'] == 'welcome_msg') {
        $current_msg = $row['data'];
    }
    if($row['data_key'] == 'welcome_msg_status') {
        $current_status = $row['data'];
    }
}

// If it's completely empty (first time load), add the default template
if($current_msg == "") {
    $current_msg = "🎲 Welcome to IndiCash App! 🎲\n\nJoin the exciting world of Matka gaming!\n\n✅ Easy to Play\n✅ Secure Transactions\n✅ Real-time Updates\n✅ Fast Withdrawals\n✅ 24/7 Support\n\nDownload now and get ₹100 welcome bonus!\n\n📲 Download Link: https://your-app-link.com/download";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Welcome Popup Settings</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
        }

        .page-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            padding: 15px 0;
            background: #fff;
            color: #333;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            margin-bottom: 20px;
        }

        .main-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .settings-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-top: 5px solid #ff9800;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
            font-size: 14px;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 250px;
            background-color: #fcfcfc;
        }

        textarea:focus {
            outline: none;
            border-color: #ff9800;
            background-color: #fff;
        }

        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            background-color: #fff;
        }

        .submit-btn {
            background-color: #ff9800;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(255, 152, 0, 0.3);
            transition: all 0.3s ease;
        }

        .submit-btn:hover { background-color: #e68a00; }
        .submit-btn:active { transform: scale(0.98); }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="page-title">Welcome Popup Settings</div>

    <div class="main-content">
        
        <?php if($success_msg != "") { ?>
            <div class="alert-success"><?php echo $success_msg; ?></div>
        <?php } ?>

        <div class="settings-card">
            <form action="" method="POST">
                
                <div class="form-group">
                    <label>Popup Status (Enable / Disable)</label>
                    <select name="popup_status">
                        <option value="1" <?php if($current_status == '1') echo 'selected'; ?>>✅ ON (Show to users)</option>
                        <option value="0" <?php if($current_status == '0') echo 'selected'; ?>>❌ OFF (Hide popup)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Welcome Message & Link</label>
                    <textarea name="welcome_msg" placeholder="Enter your welcome text here..."><?php echo htmlspecialchars($current_msg); ?></textarea>
                    <small style="color: #888; font-size: 12px; margin-top: 5px; display: block;">You can use emojis and multiple lines. The text you write here will be exactly passed to the API.</small>
                </div>

                <button type="submit" name="update_welcome" class="submit-btn">Save Settings</button>

            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>