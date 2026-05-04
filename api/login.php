<?php
include "con.php";

extract($_REQUEST);

date_default_timezone_set("Asia/Kolkata");

$brand = isset($_REQUEST['brand']) ? mysqli_real_escape_string($con, $_REQUEST['brand']) : '';
$model = isset($_REQUEST['model']) ? mysqli_real_escape_string($con, $_REQUEST['model']) : '';
$dev_id = isset($_REQUEST['device_id']) ? mysqli_real_escape_string($con, $_REQUEST['device_id']) : '';
$login_time = date("d/m/Y h:i A"); // Formats like 26/08/2025 01:07 PM

$data = []; // Initialize the response array

// Query to check if the user exists with the provided mobile number
$check = mysqli_query($con, "SELECT * FROM users WHERE mobile='$mobile'");

if (mysqli_num_rows($check) > 0) {
    // User exists, now check the password
    $cs = mysqli_fetch_array($check);

    if ($cs['password'] === md5($pass)) {
        // Password matches, check if the account is active
        if ($cs['active'] == 1) {
            // Update the user's session
            // mysqli_query($con, "UPDATE users SET session='$session' WHERE mobile='$mobile'");
            mysqli_query($con, "UPDATE users SET 
                session='$session', 
                device_brand='$brand', 
                device_model='$model', 
                device_id='$dev_id', 
                last_login_time='$login_time' 
                WHERE mobile='$mobile'");
            
            // Prepare the success response
            $data = $cs;
            $data['success'] = "1";
            $data['msg'] = "Login successful";
        } else {
            // Account is banned
            $data['success'] = "0";
            $data['msg'] = "Your account is temporarily banned";
        }
    } else {
        // Password does not match
        $data['success'] = "0";
        $data['msg'] = "Incorrect password. Please try again.";
    }
} else {
    // Mobile number does not exist
    $data['success'] = "0";
    $data['msg'] = "Mobile number does not exist. Please register.";
}

echo json_encode($data);
?>
