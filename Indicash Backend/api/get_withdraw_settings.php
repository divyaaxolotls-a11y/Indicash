<?php
include "con.php";

// Set header for JSON response
header('Content-Type: application/json');

// Fetch withdrawal specific fields from the admin table
// We select only the columns used in your form code
$sql = "SELECT 
            min_withdraw, 
            withdraw_with_info, 
            withdraw_start, 
            withdraw_end, 
            withdraw_status, 
            processing, 
            withdraw_count_limit, 
            withdraw_total_limit, 
            max_withdraw 
        FROM admin LIMIT 1";

$query = mysqli_query($con, $sql);

if ($query && mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    
    // Return the data in a clean JSON format
    echo json_encode([
        "status" => "success",
        "withdraw_settings" => $data
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No settings found"
    ]);
}
?>