<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "con.php";

$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';

if (empty($mobile)) {
    echo json_encode(['success' => '0', 'error' => 'Mobile number is required']);
    exit();
}

$data = ['success' => '0', 'auto_deposits' => []];

$query = "SELECT * FROM auto_deposits WHERE mobile = ? AND status = 0";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data['auto_deposits'] = $result->fetch_all(MYSQLI_ASSOC);
        $data['success'] = '1';
    }

    $stmt->close();
} else {
    $data['error'] = 'Error preparing SQL statement: ' . $con->error;
}

$con->close();

echo json_encode($data);
?>
