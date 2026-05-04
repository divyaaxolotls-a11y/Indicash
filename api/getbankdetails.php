<?php
include "con.php";

extract($_REQUEST);

// Check if user is authorized
if (mysqli_num_rows(mysqli_query($con, "SELECT sn FROM users WHERE mobile='$mobile' AND session='$session'")) == 0) {
    $data['msg'] = "You are not authorized to access this";
    $data['success'] = "0";
    echo json_encode($data);
    return;
} 

// Fetch user's bank details
$bank_details_query = mysqli_query($con, "SELECT * FROM bank_history WHERE user='$mobile'");

if (mysqli_num_rows($bank_details_query) > 0) {
    $bank_details = mysqli_fetch_assoc($bank_details_query);
    
    // Prepare data to return
    $data['mode'] = $bank_details['mode'];
    $data['ac'] = $bank_details['ac'];
    $data['ifsc'] = $bank_details['ifsc'];
    $data['holder'] = $bank_details['holder'];
    $data['upi'] = $bank_details['upi'];
    
    $data['msg'] = "Bank details retrieved successfully";
    $data['success'] = "1";
} else {
    $data['msg'] = "No bank details found for the user";
    $data['success'] = "0";
}

echo json_encode($data);
