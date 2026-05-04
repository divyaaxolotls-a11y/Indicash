<?php
include "con.php";

// Extract request parameters
$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';

// Validate input
if (empty($mobile)) {
    echo json_encode(['success' => '0', 'error' => 'Mobile number is required']);
    exit();
}

// Initialize response data
$data = ['success' => '0', 'withdraw_requests' => []];

// Prepare a statement to prevent SQL injection
$stmt = $con->prepare("SELECT * FROM withdraw_requests WHERE user = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

// If withdrawal requests exist
if ($result->num_rows > 0) {
    $data['withdraw_requests'] = $result->fetch_all(MYSQLI_ASSOC);
    $data['success'] = '1';
}

// Output JSON
echo json_encode($data);

// Close statement and connection
$stmt->close();
$con->close();
?>
