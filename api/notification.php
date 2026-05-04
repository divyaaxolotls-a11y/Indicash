<?php
// Always return JSON
header("Content-Type: application/json");

// Error reporting (turn OFF in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/db.php";



// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

/* ================= INPUT HANDLING ================= */
$notice_to     = $_POST['notice_to'] ?? '';
$specific_user = $_POST['specific_user'] ?? '';
$title         = trim($_POST['title'] ?? '');
$message       = trim($_POST['message'] ?? '');
$view_notice   = isset($_POST['view_notice']) && $_POST['view_notice'] == 1 ? 1 : 0;

/* ================= VALIDATION ================= */
if ($notice_to === '' || $title === '' || $message === '') {
    echo json_encode([
        "status" => false,
        "message" => "Required fields are missing"
    ]);
    exit;
}

if ($notice_to === 'USERNAME' && $specific_user === '') {
    echo json_encode([
        "status" => false,
        "message" => "specific_user is required when notice_to is USERNAME"
    ]);
    exit;
}

/* ================= SANITIZATION ================= */
$title   = mysqli_real_escape_string($con, $title);
$message = mysqli_real_escape_string($con, $message);

/* ================= RECIPIENT LOGIC ================= */
$final_recipient = ($notice_to === 'USERNAME') ? $specific_user : 'ALL';

/* ================= INSERT QUERY ================= */
$query = "
    INSERT INTO personal_notice
    (notice_to, view_notice, title, message, created_at)
    VALUES
    ('$final_recipient', '$view_notice', '$title', '$message', CURDATE())
";

if (mysqli_query($con, $query)) {

    echo json_encode([
        "status" => true,
        "message" => "Notification created successfully",
        "data" => [
            "notice_to" => $final_recipient,
            "title" => $title,
            "view_notice" => $view_notice
        ]
    ]);

} else {

    echo json_encode([
        "status" => false,
        "message" => "Database error",
        "error" => mysqli_error($con)
    ]);
}
