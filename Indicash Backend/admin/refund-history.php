<?php 
include('header.php');

// 1. Check Permissions (Using same logic as your other files)
if (in_array(1, $HiddenProducts)){

// 2. Capture Filters
$f_date = $_GET['date'] ?? date('Y-m-d'); 
$f_fund = $_GET['fund_type'] ?? 'withdraw'; 
$f_status = $_GET['status'] ?? 'reject';

$total_amount = 0;
$results = [];

// 3. Database Logic
if ($f_fund == 'add') {
    // Check payments table for rejected deposits
    $sql = "SELECT p.*, u.name as u_name FROM payments p 
            LEFT JOIN users u ON p.mobile = u.mobile 
            WHERE p.status = 'REJECTED' AND DATE(p.created_at) = '$f_date'";
    
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'name' => $row['u_name'] ?? 'Unknown',
            'mobile' => $row['mobile'],
            'amount' => $row['amount'],
            'time' => date('d/m/Y h:i A', strtotime($row['created_at'])),
            'remark' => 'Deposit Rejected'
        ];
        $total_amount += (float)$row['amount'];
    }
} elseif ($f_fund == 'withdraw') {
    // Check withdraw_requests for status 2 (Rejected)
    $sql = "SELECT wr.*, u.name as u_name FROM withdraw_requests wr 
            LEFT JOIN users u ON wr.mobile = u.mobile 
            WHERE wr.status = 2 AND wr.date = '$f_date'";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'name' => $row['u_name'] ?? $row['holder'],
            'mobile' => $row['mobile'],
            'amount' => $row['amount'],
            'time' => date('d/m/Y h:i A', strtotime($row['created_at'])),
            'remark' => 'Withdrawal Rejected'
        ];
        $total_amount += (float)$row['amount'];
    }
}
?>

<style>
    body { background-color: #f4f6f9; font-family: sans-serif; }
    .round-input { border-radius: 25px; border: 1px solid #ccc; height: 42px; padding: 0 15px; width: 100%; margin-bottom: 12px; background: #fff; }
    .btn-submit { background-color: #007bff; color: white; border-radius: 25px; width: 100%; height: 42px; font-weight: bold; border: none; font-size: 16px; }
    
    /* The Black Bar from your screenshot */
    .total-bar { background: black; color: #ffa500; text-align: center; padding: 10px; font-weight: bold; border-radius: 8px; margin: 15px 0; font-size: 18px; }
    
    .table-container { background: #fff; border-radius: 10px; border: 1px solid #ddd; overflow: hidden; margin-top: 10px; }
    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table thead th { background: #ffb100; color: #000; padding: 12px; border: 1px solid #ddd; text-align: center; }
    .custom-table tbody td { padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 14px; }
    .text-name { font-weight: bold; display: block; }
    .text-time { font-size: 11px; color: #666; }
</style>

<div class="container-fluid p-3">
    <!-- Filter Section -->
    <form method="GET">
        <div class="row">
            <div class="col-6">
                <input type="date" name="date" value="<?php echo $f_date; ?>" class="round-input">
            </div>
            <div class="col-6">
                <select name="fund_type" class="round-input">
                    <option value="">Select Fund</option>
                    <option value="add" <?php if($f_fund == 'add') echo 'selected'; ?>>Add</option>
                    <option value="withdraw" <?php if($f_fund == 'withdraw') echo 'selected'; ?>>Withdraw</option>
                </select>
            </div>
            <div class="col-6">
                <select name="status" class="round-input">
                    <option value="reject">Reject</option>
                </select>
            </div>
            <div class="col-6">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </div>
    </form>

    <!-- Total Bar -->
    <div class="total-bar">Total : <?php echo number_format($total_amount, 2); ?></div>

    <!-- Results Table -->
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>User Detail</th>
                    <th>Remark</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td style="text-align: left; padding-left: 20px;">
                            <span class="text-name" style="font-size: 15px;"><?php echo htmlspecialchars($row['name']); ?></span>
                            <small style="color: #007bff; font-weight: bold; display: block;"><?php echo $row['mobile']; ?></small>
                            <span class="text-time" style="font-size: 11px; color: #777;"><?php echo $row['time']; ?></span>
                        </td>
                        <td>
                            <span class="badge badge-danger" style="padding: 6px 12px; border-radius: 20px; font-size: 12px;">
                                <?php echo $row['remark']; ?>
                            </span>
                        </td>
                        <td style="font-weight:bold; color:#000; font-size: 16px; text-align: center;">
                            <?php echo number_format($row['amount'], 2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="p-5 text-center">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i><br>
                        No rejected records found for <b><?php echo date('d-m-Y', strtotime($f_date)); ?></b>.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<?php 
} else { echo "<script>window.location.href = 'unauthorized.php';</script>"; exit(); }
include('footer.php'); 
?>