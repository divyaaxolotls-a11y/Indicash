<?php 
include('config.php'); 

// Initialize counts
$withdrawalCount = 0;
$depositCount = 0;

// Query to get pending withdrawals count
$sqlWithdrawals = "SELECT COUNT(*) as count FROM withdraw_requests WHERE status = '0'";
$resultWithdrawals = $con->query($sqlWithdrawals);

if ($resultWithdrawals && $row = $resultWithdrawals->fetch_assoc()) {
    $withdrawalCount = $row['count'];
}

// Query to get pending deposits count
$sqlDeposits = "SELECT COUNT(*) as count FROM auto_deposits WHERE status = '0'";
$resultDeposits = $con->query($sqlDeposits);

if ($resultDeposits && $row = $resultDeposits->fetch_assoc()) {
    $depositCount = $row['count'];
}

// Output the counts as JSON
echo json_encode([
    'withdrawals' => $withdrawalCount,
    'deposits' => $depositCount
]);

$con->close();
?>
