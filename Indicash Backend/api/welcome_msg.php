<?php
// Include your database connection (Make sure this path is correct)
include "con.php"; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Default Fallbacks
$message = "Welcome to our App!";
$status = "0"; // Default OFF

// Fetch data from database
$query = mysqli_query($con, "SELECT data_key, data FROM settings WHERE data_key IN ('welcome_msg', 'welcome_msg_status')");

while($row = mysqli_fetch_assoc($query)) {
    if($row['data_key'] == 'welcome_msg') {
        $message = $row['data'];
    }
    if($row['data_key'] == 'welcome_msg_status') {
        $status = $row['data'];
    }
}

// Create the JSON response
$response = [
    "status" => ($status == "1") ? "success" : "disabled", // Lets app know if it's turned off
    "active" => $status, // "1" for ON, "0" for OFF
    "message" => $message
];

// Output JSON properly formatted with emojis enabled
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>