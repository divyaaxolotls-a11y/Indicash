<?php
include "con.php";

extract($_REQUEST);

// Check if user is authorized
if (mysqli_num_rows(mysqli_query($con, "SELECT sn FROM users WHERE mobile='$mobile' AND session='$session'")) == 0) {
    $data['msg'] = "You are not authorized to use this";
    $data['active'] = "0";
    echo json_encode($data);
    return;
} else {
    $dd = mysqli_query($con, "SELECT active FROM users WHERE mobile='$mobile'");
    $d = mysqli_fetch_array($dd);
    $data['active'] = "1";
}

// Check if bank details already exist for the user
$check_bank_details = mysqli_query($con, "SELECT * FROM bank_history WHERE user='$mobile'");

if (mysqli_num_rows($check_bank_details) > 0) {
    // If bank details exist, update them
    mysqli_query($con, "UPDATE bank_history SET mode='$mode', ac='$ac', ifsc='$ifsc', holder='$holder', upi='$upi' WHERE user='$mobile'");
    $data['msg'] = "Your bank details have been updated successfully.";
} else {
    // If bank details don't exist, insert new record
 $query = "INSERT INTO bank_history(`user`, `mode`, `ac`, `ifsc`, `holder`) 
          VALUES ('$mobile','$mode','$ac','$ifsc','$holder')";

// Execute the query
$result = mysqli_query($con, $query);

    $data['msg'] = "Your bank details have been saved successfully.";

}
$data['success'] = "1";

echo json_encode($data);
