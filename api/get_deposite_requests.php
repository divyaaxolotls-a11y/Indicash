<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "con.php";

// Extract request parameters
$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';

// Validate input
if (empty($mobile)) {
    echo json_encode(['success' => '0', 'error' => 'Mobile number is required']);
    exit();
}

// Initialize response data
$data = ['success' => '0', 'auto_deposits' => []];

// Prepare a statement to prevent SQL injection
$query = "SELECT * FROM payments WHERE mobile = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    // If auto deposits exist
    if ($result->num_rows > 0) {
        $data['auto_deposits'] = $result->fetch_all(MYSQLI_ASSOC);
        $data['success'] = '1';
    }

    // Close statement
    $stmt->close();
} else {
    $data['error'] = 'Error preparing SQL statement: ' . $con->error;
}

// Close connection
$con->close();

// Output JSON
echo json_encode($data);
?>
