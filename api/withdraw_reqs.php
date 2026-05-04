<?php
include "con.php";

extract($_REQUEST);

$data = []; // initialize

$sx = mysqli_query($con, "SELECT * FROM `withdraw_requests` WHERE user='$mobile' AND status='0' ORDER BY created_at DESC");

// Always initialize data['data'] as an array
$data['data'] = [];

while ($x = mysqli_fetch_array($sx)) {
    $x['status'] = "Pending";

    // Add details based on mode
    if ($x['mode'] == "Phonepe") {
        $x['details'] = $x['phonepe'];
    } else if ($x['mode'] == "Paytm") {
        $x['details'] = $x['paytm'];
    } else if ($x['mode'] == "Bank") {
        $x['details'] = "AC - " . $x['ac'];
    }

    // Format date
    $x['date'] = date('d/m/y', $x['created_at']);

    $data['data'][] = $x;
}

// Always returns data['data'] as array (empty if no results)
echo json_encode($data);
?>
