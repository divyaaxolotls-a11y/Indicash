<?php
header("Content-Type: application/json");

// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "con.php";

// Get parameter (mobile number)
$mobile = $_REQUEST['mobile'] ?? '';
// Validation
if (empty($mobile)) {
    echo json_encode([
        "success" => "0",
        "message" => "Mobile number is required"
    ]);
    exit;
}

// Default response
$response = [
    "success" => "0",
    "withdraw_requests" => []
];

// Prepare query
$sql = "SELECT * FROM withdraw_requests WHERE mobile = ? ORDER BY sn DESC";
$stmt = $con->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => "0",
        "message" => "Query preparation failed"
    ]);
    exit;
}

$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data
if ($result->num_rows > 0) {
    $response['withdraw_requests'] = $result->fetch_all(MYSQLI_ASSOC);
    $response['success'] = "1";
}

$stmt->close();
$con->close();

// Output
echo json_encode($response);





