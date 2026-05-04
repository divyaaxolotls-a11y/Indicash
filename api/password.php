<?php
include "con.php";

// Use POST for security (avoid $_REQUEST)
$mobile = $_POST['mobile'] ?? '';
$pass = $_POST['pass'] ?? '';

if (empty($mobile) || empty($pass)) {
    echo json_encode(['success' => 0, 'message' => 'Mobile and password are required.']);
    exit;
}

// Hash the password using MD5 (not recommended for production, but using as per your request)
$hashedPassword = md5($pass);

// Use prepared statements to prevent SQL injection
$stmt = $con->prepare("UPDATE users SET password = ? WHERE mobile = ?");
$stmt->bind_param("ss", $hashedPassword, $mobile);

if ($stmt->execute()) {
    echo json_encode(['success' => 1]);
} else {
    echo json_encode(['success' => 0, 'message' => 'Update failed.']);
}

$stmt->close();
?>
