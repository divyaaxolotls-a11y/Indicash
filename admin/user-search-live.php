<?php
include('config.php'); // Ensure this points to your DB connection file
// session_start();

// Security check: Only allowed if logged in (matching your main file logic)
// if (!isset($_SESSION['userID'])) {
//     exit;
// }

$searchTerm = "";
if (isset($_GET['q'])) {
    $searchTerm = mysqli_real_escape_string($con, $_GET['q']);
}

$results = [];

if (!empty($searchTerm)) {
    // Search by name or mobile
    $query = "SELECT mobile, name FROM users 
              WHERE name LIKE '%$searchTerm%' 
              OR mobile LIKE '%$searchTerm%' 
              LIMIT 20";
    
    $res = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'id' => $row['mobile'], 
            'text' => $row['name'] . " (" . $row['mobile'] . ")"
        ];
    }
}

echo json_encode($results);
?>