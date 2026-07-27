<?php
include "con.php";
date_default_timezone_set("Asia/Kolkata");
header('Content-Type: application/json');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => 0,
        'msg' => 'Only POST request allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

$mobile = $input['mobile'] ?? '';
$date   = $input['date'] ?? '';

if ($mobile == '') {
    echo json_encode([
        'success' => 0,
        'msg' => 'Mobile required'
    ]);
    exit;
}

$data = [];

if ($date != '') {

    $q = mysqli_query($con,"
        SELECT * FROM jackpot_games
        WHERE user='$mobile' AND date='$date'
        ORDER BY created_at DESC
    ");

} else {

    $q = mysqli_query($con,"
        SELECT * FROM jackpot_games
        WHERE user='$mobile'
        ORDER BY created_at DESC
    ");

}

while ($row = mysqli_fetch_assoc($q)) {
    $data[] = $row;
}

echo json_encode([
    'success' => 1,
    'data' => $data
]);