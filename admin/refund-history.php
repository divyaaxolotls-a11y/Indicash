<?php 
include('header.php');

// 1. Check Permissions (Using same logic as your other files)
if (in_array(1, $HiddenProducts)){

// 2. Capture Filters
$f_date = $_GET['date'] ?? date('Y-m-d');
$f_fund = $_GET['fund_type'] ?? ''; // 'add' or 'withdraw'
$f_status = $_GET['status'] ?? 'reject';

$total_amount = 0;
$results = [];

// 3. Database Logic
if ($f_fund == 'add') {
    // Check payments table for rejected deposits
    $search_date = date('d/m/Y', strtotime($f_date));
    $sql = "SELECT p.*, u.name FROM payments p 
            LEFT JOIN users u ON p.user = u.mobile 
            WHERE p.status = 'REJECTED' AND p.created_at LIKE '%$search_date%'";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'name' => $row['name'],
            'amount' => $row['amount'],
            'time' => $row['created_at'],
            'remark' => 'Deposit Refunded'
        ];
        $total_amount += $row['amount'];
    }
} elseif ($f_fund == 'withdraw') {
    // Check withdraw_requests for rejected (Status 2 usually means Rejected)
    $search_date = date('d/m/Y', strtotime($f_date));
    $sql = "SELECT wr.*, u.name FROM withdraw_requests wr 
            LEFT JOIN users u ON wr.mobile = u.mobile 
            WHERE wr.status = 2 AND wr.created_at LIKE '%$search_date%'";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'name' => $row['name'],
            'amount' => $row['amount'],
            'time' => $row['created_at'],
            'remark' => 'Withdrawal Refunded'
        ];
        $total_amount += $row['amount'];
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
                            <td>
                                <span class="text-name"><?php echo $row['name']; ?></span>
                                <span class="text-time"><?php echo $row['time']; ?></span>
                            </td>
                            <td><?php echo $row['remark']; ?></td>
                            <td style="font-weight:bold; color:#dc3545;">
                                <?php echo number_format($row['amount'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="p-4">No records found. Please select Fund Type and Filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
} else { echo "<script>window.location.href = 'unauthorized.php';</script>"; exit(); }
include('footer.php'); 
?>