<?php
header("Content-Type: application/json");

// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "con.php";

// Get mobile from request
$mobile = $_GET['mobile'] ?? '';

// Validation
if (empty($mobile)) {
    echo json_encode([
        "success" => false,
        "message" => "Mobile number is required"
    ]);
    exit;
}

// Prepare response
$response = [
    "success" => true,
    "notifications" => []
];

// Prepare SQL
$sql = "
    SELECT id, notice_to, username, mobile, view_notice, title, message, created_at
    FROM personal_notice
    WHERE notice_to = 'ALL'
       OR (notice_to = 'USERNAME' AND mobile = ?)
    ORDER BY id DESC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed"
    ]);
    exit;
}

$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response['notifications'] = $result->fetch_all(MYSQLI_ASSOC);
}

$stmt->close();
$con->close();

// Output JSON
echo json_encode($response);
