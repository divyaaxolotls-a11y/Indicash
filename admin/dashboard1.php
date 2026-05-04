<?php 
include('header.php');

// 1. SECURITY & LOGIC HANDLING
if (in_array(1, $HiddenProducts)){
    
    // --- Handle Deposit 'complete' request ---
    if(isset($_REQUEST['complete'])){
        $sn = htmlspecialchars($_REQUEST['complete'], ENT_QUOTES, 'UTF-8');
        if (is_numeric($sn)) {
            $info = mysqli_fetch_array(mysqli_query($con, "SELECT user, amount FROM upi_verification WHERE sn='$sn'"));
            if ($info) {
                $mobile = $info['user'];
                $amount = $info['amount'];
                $createdAt = date("Y-m-d H:i:s");
                
                $stmt = $con->prepare("DELETE FROM upi_verification WHERE sn=?");
                $stmt->bind_param("i", $sn);
                $stmt->execute();
                $stmt->close();

                $stmt = $con->prepare("UPDATE users SET wallet = wallet + ? WHERE mobile = ?");
                $stmt->bind_param("ds", $amount, $mobile);
                $stmt->execute();
                $stmt->close();

                $stmt = $con->prepare("INSERT INTO transactions (user, amount, type, remark, owner, created_at) VALUES (?, ?, '1', 'Deposit', 'user', ?)");
                $stmt->bind_param("sds", $mobile, $amount, $createdAt);
                $stmt->execute();
                $stmt->close();

                header('location:dashboard.php');
                exit;
            }
        }
    }

    // --- Handle Deposit 'cancel' request ---
    if(isset($_REQUEST['cancel'])){
        $sn = htmlspecialchars($_REQUEST['cancel'], ENT_QUOTES, 'UTF-8');
        if (is_numeric($sn)) {
            $stmt = $con->prepare("DELETE FROM upi_verification WHERE sn=?");
            $stmt->bind_param("i", $sn);
            $stmt->execute();
            $stmt->close();
            header('location:dashboard.php');
            exit;
        }
    }

    // 2. FETCH DATA FOR DASHBOARD
    // $currentDate = date('d/m/Y');
    // $dbDate = date('Y-m-d');
    $selectedDateRaw = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Format for 'games' table (d/m/Y)
    $currentDate = date('d/m/Y', strtotime($selectedDateRaw));
    
    // Format for HTML date input and SQL DATE() functions (Y-m-d)
    $dbDate = date('Y-m-d', strtotime($selectedDateRaw));

    $userQuery = mysqli_query($con, "SELECT * FROM `users`");
    $totalUsers = mysqli_num_rows($userQuery);

    // $walletQuery = mysqli_query($con, "SELECT SUM(amount) AS total_amount FROM `games`");
    $walletQuery = mysqli_query($con, "SELECT SUM(wallet) AS total_amount FROM `users` ");
    $walletRow = mysqli_fetch_array($walletQuery);
    $walletAmount = (isset($walletRow['total_amount']) ? $walletRow['total_amount'] : 0);

    $withdrawCount = 0;

    $matkaPlayQuery = mysqli_query($con, "SELECT SUM(amount) AS TotalPoints FROM `games` WHERE `date`='$currentDate' ");
    $matkaFetch = mysqli_fetch_array($matkaPlayQuery);
    $matkaPlay = ($matkaFetch['TotalPoints'] != '') ? $matkaFetch['TotalPoints'] : 0;

    $starlinePlayQuery = mysqli_query($con, "SELECT SUM(amount) AS TotalPoints FROM `starline_games` WHERE `date`='$currentDate' ");
    $starlineFetch = mysqli_fetch_array($starlinePlayQuery);
    $starlinePlay = ($starlineFetch['TotalPoints'] != '') ? $starlineFetch['TotalPoints'] : 0;
    $jackpotPlayQuery = mysqli_query($con, "SELECT SUM(amount) AS TotalPoints FROM `jackpot_games` WHERE `date`='$currentDate' ");
    $jackpotFetch = mysqli_fetch_array($jackpotPlayQuery);
    $jackpotPlay = ($jackpotFetch['TotalPoints'] != '') ? $jackpotFetch['TotalPoints'] : 0;
    
    // Grand Total
    $totalPlay = $matkaPlay + $starlinePlay + $jackpotPlay;
    // $totalPlay = $matkaPlay + $starlinePlay;

    // $matkaWin = 0;
    // $totalWin = $matkaWin;

    // $matkaProfit = $matkaPlay - $matkaWin;
    // $totalProfit = $totalPlay - $totalWin;
     // Fetch Matka Wins
    // $matkaWinQuery = mysqli_query($con, "SELECT SUM(amount * 9) AS TotalWin FROM `games` WHERE `date`='$currentDate' AND (status='1' OR status='win')");
    // $matkaWinFetch = mysqli_fetch_array($matkaWinQuery);
    // $matkaWin = ($matkaWinFetch['TotalWin'] != '') ? $matkaWinFetch['TotalWin'] : 0;

    // // Fetch Starline Wins
    // $starlineWinQuery = mysqli_query($con, "SELECT SUM(win_amount) AS TotalWin FROM `starline_games` WHERE `date`='$currentDate' ");
    // $starlineWinFetch = mysqli_fetch_array($starlineWinQuery);
    // $starlineWin = ($starlineWinFetch['TotalWin'] != '') ? $starlineWinFetch['TotalWin'] : 0;

    // // Fetch Jackpot Wins
    // $jackpotWinQuery = mysqli_query($con, "SELECT SUM(win_amount) AS TotalWin FROM `jackpot_games` WHERE `date`='$currentDate' ");
    // $jackpotWinFetch = mysqli_fetch_array($jackpotWinQuery);
    // $jackpotWin = ($jackpotWinFetch['TotalWin'] != '') ? $jackpotWinFetch['TotalWin'] : 0;

    // // Calculate Totals
    // $totalWin = $matkaWin + $starlineWin + $jackpotWin;

    // // Calculate Profits (Play minus Win)
    // $matkaProfit    = $matkaPlay - $matkaWin;
    // $starlineProfit = $starlinePlay - $starlineWin;
    // $jackpotProfit  = $jackpotPlay - $jackpotWin;

    // // Grand Total Profit
    // $totalProfit = $totalPlay - $totalWin;
    // --- 1. Matka Wins (From 'transactions' table for 100% accuracy) ---
// This only counts money actually credited to users after you click 'Declare Result'
$matkaWinQuery = mysqli_query($con, "SELECT SUM(amount) AS TotalWin FROM `transactions` 
    WHERE DATE(created_at) = '$dbDate' 
    AND type = '1' 
    AND (remark LIKE '%Result Win%' OR remark LIKE '%Winning%')");
$matkaWinFetch = mysqli_fetch_array($matkaWinQuery);
$matkaWin = ($matkaWinFetch['TotalWin'] != '') ? $matkaWinFetch['TotalWin'] : 0;

// --- 2. Starline Wins (Using 'win_amount' column from your screenshot) ---
$starlineWinQuery = mysqli_query($con, "SELECT SUM(win_amount) AS TotalWin FROM `starline_games` 
    WHERE `date` = '$currentDate' AND win_amount > 0");
$starlineWinFetch = mysqli_fetch_array($starlineWinQuery);
$starlineWin = ($starlineWinFetch['TotalWin'] != '') ? $starlineWinFetch['TotalWin'] : 0;

// --- 3. Jackpot Wins (Using 'win_amount' column) ---
$jackpotWinQuery = mysqli_query($con, "SELECT SUM(win_amount) AS TotalWin FROM `jackpot_games` 
    WHERE `date` = '$currentDate' AND win_amount > 0");
$jackpotWinFetch = mysqli_fetch_array($jackpotWinQuery);
$jackpotWin = ($jackpotWinFetch['TotalWin'] != '') ? $jackpotWinFetch['TotalWin'] : 0;

// --- 4. Calculate Totals & Profit ---
$totalWin = $matkaWin + $starlineWin + $jackpotWin;

// --- Check if results are actually declared for today ---
$matkaResCheck = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `manual_market_results` WHERE `date`='$currentDate'"))['total'];
$starlineResCheck = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `starline_results` WHERE `date`='$currentDate'"))['total'];
$jackpotResCheck = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `jackpot_results` WHERE `date`='$currentDate'"))['total'];

// --- Calculate Profits ONLY if a result exists, else show 0 ---
// Matka Profit
if ($matkaResCheck > 0) {
    $matkaProfit = $matkaPlay - $matkaWin;
} else {
    $matkaProfit = 0; // Show 0 if no results declared yet
}

// Starline Profit
if ($starlineResCheck > 0) {
    $starlineProfit = $starlinePlay - $starlineWin;
} else {
    $starlineProfit = 0;
}

// Jackpot Profit
if ($jackpotResCheck > 0) {
    $jackpotProfit = $jackpotPlay - $jackpotWin;
} else {
    $jackpotProfit = 0;
}

// Grand Total Profit
$totalProfit = $matkaProfit + $starlineProfit + $jackpotProfit;

// $matkaProfit    = $matkaPlay - $matkaWin;
// $starlineProfit = $starlinePlay - $starlineWin;
// $jackpotProfit  = $jackpotPlay - $jackpotWin;
// $totalProfit    = $totalPlay - $totalWin;
    // (Placeholders as per original)
    // $add_upi = 0; $add_gateway = 0; $add_manual = 0;
    // $add_total = $add_upi + $add_gateway + $add_manual;
    // $wd_request = 0; $wd_manual = 0;
    // $wd_total = $wd_request + $wd_manual;
    // $reject_add_money = 0; $reject_withdraw_money = 0;
    // $bonus_welcome = 0; $bonus_recharge = 0;
    // --- 1. Real-time Pending Withdraw Count (Top Black Box) ---
      // Counts all requests where status is 0 (Pending)
    $pWdQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM `withdraw_requests` WHERE `status` = 0");
    $pWdRow = mysqli_fetch_assoc($pWdQuery);
    $withdrawCount = $pWdRow['total'];

    // --- 2. Add Money Statistics (Today Only) ---
    // Assuming table name is 'payments'. 
    // Manual additions usually have an empty payment_id.
    $addStatsQuery = mysqli_query($con, "SELECT 
        SUM(CASE WHEN (payment_id != '' AND payment_id IS NOT NULL) THEN amount ELSE 0 END) AS gateway_total,
        SUM(CASE WHEN (payment_id = '' OR payment_id IS NULL) THEN amount ELSE 0 END) AS manual_total
        FROM payments 
        WHERE status='SUCCESS' AND DATE(created_at) = CURRENT_DATE()");
    
    $asRow = mysqli_fetch_assoc($addStatsQuery);
    $add_upi     = 0; // Set this if you have a specific 'UPI' mode column
    $add_gateway = $asRow['gateway_total'] ?? 0;
    $add_manual  = $asRow['manual_total'] ?? 0;
    $add_total   = $add_gateway + $add_manual;

    // --- 3. Withdraw Money Statistics (Today Only) ---
    // Sum of amounts for requests approved (status=1) today
    $wdStatsQuery = mysqli_query($con, "SELECT 
        SUM(CASE WHEN mode != 'manual' THEN amount ELSE 0 END) AS req_total,
        SUM(CASE WHEN mode = 'manual' THEN amount ELSE 0 END) AS man_total
        FROM withdraw_requests 
        WHERE status = 1 AND DATE(created_at) = CURRENT_DATE()");
        
    $wsRow = mysqli_fetch_assoc($wdStatsQuery);
    $wd_request = $wsRow['req_total'] ?? 0;
    $wd_manual  = $wsRow['man_total'] ?? 0;
    $wd_total   = $wd_request + $wd_manual;

    // --- 4. Reject Statistics (Today Only) ---
    // Assuming status 2 is Rejected
    $rejectAddQ = mysqli_query($con, "SELECT COUNT(*) as total FROM payments WHERE status='REJECTED' AND DATE(created_at) = CURRENT_DATE()");
    $reject_add_money = mysqli_fetch_assoc($rejectAddQ)['total'] ?? 0;

    $rejectWdQ = mysqli_query($con, "SELECT COUNT(*) as total FROM withdraw_requests WHERE status = 2 AND DATE(created_at) = CURRENT_DATE()");
    $reject_withdraw_money = mysqli_fetch_assoc($rejectWdQ)['total'] ?? 0;
?>

<style>
    body { background-color: #f4f6f9; font-family: 'Source Sans Pro', sans-serif; }
    
    .dashboard-box { border-radius: 10px; color: white; padding: 15px; margin-bottom: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .box-title { font-size: 16px; font-weight: 500; margin-bottom: 5px; }
    .box-value { font-size: 20px; font-weight: bold; }

    .box-black { background-color: #000; padding: 20px; border-radius: 10px; text-align: center; color: white; margin-bottom: 20px; border: 1px solid #333; }
    .box-black h3 { font-size: 18px; margin: 0; font-weight: 400; }
    .box-black h2 { font-size: 24px; margin: 5px 0 0 0; font-weight: bold; }
    
    .box-blue { background-color: #3498db; }
    .box-pink { background-color: #c03978; }
    .box-green { background-color: #0d5138; } 
    .box-orange { background-color: #ff5733; } 

    .game-status-card { background: white; border-radius: 30px; padding: 10px 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 15px; display: flex; justify-content: center; align-items: center; font-size: 18px; font-weight: 600; color: #1a4d6e; }
    .status-badge { background-color: #28a745; color: white; padding: 2px 15px; border-radius: 15px; font-size: 14px; margin-left: 10px; cursor: pointer; }
    .status-badge.badge-deactive { background-color: #dc3545; }

    .date-picker-card {
        background: white;
        border-radius: 30px;
        padding: 5px 5px 5px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        border: 1px solid #eee;
    }
    .custom-date-input {
        border: none;
        outline: none;
        font-size: 17px;
        color: #444;
        background: transparent;
        flex-grow: 1;
        width: 100%;
    }
    .btn-set-date {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 8px 22px;
        font-weight: 500;
        font-size: 16px;
        white-space: nowrap;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-set-date:hover { background-color: #0056b3; }

    .list-card { border-radius: 5px; color: #ffffff; padding: 0; margin-bottom: 20px; overflow: hidden; }
    .list-header { text-align: center; font-size: 20px; padding: 10px; border-bottom: 2px solid white; font-weight: 600; color: white; }
    .list-item { display: flex; justify-content: space-between; padding: 8px 15px; border-bottom: 1px solid white; font-size: 15px; }
    .list-item.total { font-size: 18px; font-weight: bold; color: white; }
    .card-addmoney { background-color: #073b65; }
    .card-withdraw { background-color: #a31313; }
    .card-reject   { background-color: #00bcd4; }
    .card-bonus    { background-color: #00c853; }
    .profit-card { background: white; border-radius: 5px; padding: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .profit-header { text-align: center; font-size: 20px; color: #333; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
    .profit-row { display: flex; justify-content: space-between; font-size: 16px; color: green; padding: 5px 0; }
    .stats-header { font-size: 20px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid white; font-weight: 600; color: white; }
    .stats-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 16px; }
    .stats-row.total { font-weight: bold; font-size: 18px; margin-top: 5px; border-top: 1px solid white; padding-top: 5px; }
    
    /* ── Mobile fixes ── */
    @media (max-width: 576px) {
        /* Remove Bootstrap's default container gutters on mobile */
        .container-fluid {
            padding-left: 8px;
            padding-right: 8px;
        }
        /* Tighten row gutters */
        .row {
            margin-left: -6px;
            margin-right: -6px;
        }
        .row > [class*="col-"] {
            padding-left: 6px;
            padding-right: 6px;
        }
        /* content-header wrapper */
        .content-header {
            padding-left: 0;
            padding-right: 0;
        }
        /* Cards */
        .box-black { padding: 14px 10px; }
        .box-black h3 { font-size: 15px; }
        .box-black h2 { font-size: 20px; }

        .dashboard-box { padding: 12px 8px; margin-bottom: 12px; }
        .box-title { font-size: 13px; }
        .box-value { font-size: 17px; }

        .game-status-card { font-size: 15px; padding: 9px 14px; border-radius: 25px; }
        .status-badge { font-size: 12px; padding: 2px 10px; }

        .date-picker-card { padding: 4px 4px 4px 12px; border-radius: 25px; }
        .custom-date-input { font-size: 14px; }
        .btn-set-date { font-size: 14px; padding: 7px 16px; }

        .list-header { font-size: 17px; padding: 8px; }
        .list-item { padding: 7px 12px; font-size: 14px; }
        .list-item.total { font-size: 16px; }

        .profit-header { font-size: 17px; }
        .profit-row { font-size: 14px; }

        .stats-header { font-size: 17px; }
        .stats-row { font-size: 14px; }
        .stats-row.total { font-size: 16px; }
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-12">
                <a href="withdraw-points-pending.php" style="text-decoration:none;color:inherit;">
                    <div class="box-black" style="cursor:pointer;">
                        <h3>Withdraw Request</h3>
                        <h2><?php echo $withdrawCount; ?></h2>
                    </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-6 col-md-4">
                <!--<div class="dashboard-box box-blue">-->
                <!--    <div class="box-title">Users</div>-->
                <!--    <div class="box-value"><?php echo $totalUsers; ?></div>-->
                <!--</div>-->
                <a href="users_old.php" style="text-decoration:none;">
                    <div class="dashboard-box box-blue" style="cursor:pointer;">
                        <div class="box-title">Users</div>
                        <div class="box-value"><?php echo $totalUsers; ?></div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="user-statistics.php" style="text-decoration:none;">
                <div class="dashboard-box box-pink" style="cursor:pointer;">
                    <div class="box-title">Wallet Amount</div>
                    <div class="box-value"><?php echo number_format($walletAmount, 2); ?></div>
                </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <?php 
                // $currStat = 1; 
               $statusQuery = mysqli_query($con,"SELECT COUNT(*) as total FROM gametime_manual WHERE active=1");
               $statusRow = mysqli_fetch_assoc($statusQuery);
            
               $currStat = ($statusRow['total'] > 0) ? 1 : 0;
                ?>
                <div class="game-status-card">
                    Game Status : 
                    <span id="statusBtn" class="status-badge <?php echo ($currStat == 1) ? 'badge-active' : 'badge-deactive'; ?>"
                          data-status="<?php echo $currStat; ?>">
                        <?php echo ($currStat == 1) ? 'Active' : 'Deactive'; ?>
                    </span>
                </div>
                
                <div class="date-picker-card">
                    <input type="date" id="resultDate" value="<?php echo $dbDate; ?>" class="custom-date-input">
                    <button class="btn-set-date" id="setDateBtn">Set Date</button>
                </div>
                <br>
            </div>
        </div>

        <!--<div class="row justify-content-center">-->
        <!--    <div class="col-12 col-md-8">-->
        <!--        <a href="win-play-page.php" style="text-decoration:none;">-->
        <!--            <div class="dashboard-box box-green" style="cursor:pointer;">-->
                    
        <!--                <div class="stats-header">Play</div>-->
                    
        <!--                <div class="stats-row">-->
        <!--                    <span>Matka Play</span>-->
        <!--                    <span><?php echo $matkaPlay; ?></span>-->
        <!--                </div>-->
                    
        <!--                <div class="stats-row total">-->
        <!--                    <span>Total Play</span>-->
        <!--                    <span><?php echo $totalPlay; ?></span>-->
        <!--                </div>-->
                    
        <!--            </div>-->
        <!--        </a>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <a href="win-play-page.php" style="text-decoration:none;">
                    <div class="dashboard-box box-green" style="cursor:pointer;">
                    
                        <div class="stats-header">Play Statistics</div>
                    
                        <div class="stats-row" style= "border-bottom: 1px solid white;">
                            <span>Matka Play</span>
                            <span><?php echo number_format($matkaPlay, 2); ?></span>
                        </div>
        
                        <div class="stats-row" style= "border-bottom: 1px solid white;">
                            <span>Starline Play</span>
                            <span><?php echo number_format($starlinePlay, 2); ?></span>
                        </div>
        
                        <div class="stats-row">
                            <span>Jackpot Play</span>
                            <span><?php echo number_format($jackpotPlay, 2); ?></span>
                        </div>
                    
                        <div class="stats-row total">
                            <span>Total Play</span>
                            <span><?php echo number_format($totalPlay, 2); ?></span>
                        </div>
                    
                    </div>
                </a>
            </div>
        </div>

        <!--<div class="row justify-content-center">-->
        <!--    <div class="col-12 col-md-8">-->
        <!--        <a href="win-play-page.php" style="text-decoration:none;">-->
        <!--        <div class="dashboard-box box-orange" style="cursor:pointer;">-->
        <!--            <div class="stats-header">Win</div>-->
        <!--            <div class="stats-row"><span>Matka Win</span><span><?php echo $matkaWin; ?></span></div>-->
        <!--            <div class="stats-row total"><span>Total Win</span><span><?php echo $totalWin; ?></span></div>-->
        <!--        </div>-->
        <!--        </a>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <a href="win-play-page.php" style="text-decoration:none;">
                    <div class="dashboard-box box-orange" style="cursor:pointer;">
                        <div class="stats-header">Win Statistics</div>
                        
                        <div class="stats-row" style= "border-bottom: 1px solid white;">
                            <span>Matka Win</span>
                            <span><?php echo number_format($matkaWin, 2); ?></span>
                        </div>
        
                        <div class="stats-row" style= "border-bottom: 1px solid white;">
                            <span>Starline Win</span>
                            <span><?php echo number_format($starlineWin, 2); ?></span>
                        </div>
        
                        <div class="stats-row">
                            <span>Jackpot Win</span>
                            <span><?php echo number_format($jackpotWin, 2); ?></span>
                        </div>
                        
                        <div class="stats-row total">
                            <span>Total Win</span>
                            <span><?php echo number_format($totalWin, 2); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!--<div class="row justify-content-center">-->
        <!--    <div class="col-12 col-md-8">-->
        <!--        <div class="profit-card">-->
        <!--            <div class="profit-header">Profit - Loss</div>-->
        <!--            <div class="profit-row"><span>matka Profit</span><span><?php echo $matkaProfit; ?></span></div>-->
        <!--            <div class="profit-row total"><span>total Profit</span><span><?php echo $totalProfit; ?></span></div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        
        <div class="row justify-content-center">
                <div class="col-12 col-md-8">
                    <div class="profit-card">
                        <div class="profit-header">Profit - Loss Analysis</div>
                        
                        <div class="profit-row" style= "border-bottom: 1px solid #eee;">
                            <span>Matka Profit</span>
                            <span style="color: <?php echo ($matkaProfit >= 0) ? 'green' : 'red'; ?>">
                                <?php echo number_format($matkaProfit, 2); ?>
                            </span>
                        </div>
            
                        <div class="profit-row" style= "border-bottom: 1px solid #eee;">
                            <span>Starline Profit</span>
                            <span style="color: <?php echo ($starlineProfit >= 0) ? 'green' : 'red'; ?>">
                                <?php echo number_format($starlineProfit, 2); ?>
                            </span>
                        </div>
            
                        <div class="profit-row">
                            <span>Jackpot Profit</span>
                            <span style="color: <?php echo ($jackpotProfit >= 0) ? 'green' : 'red'; ?>">
                                <?php echo number_format($jackpotProfit, 2); ?>
                            </span>
                        </div>
            
                        <div class="profit-row total" style="border-top: 2px solid #eee; margin-top: 5px; font-weight: bold;">
                            <span>Total Profit</span>
                            <span style="color: <?php echo ($totalProfit >= 0) ? 'green' : 'red'; ?>">
                                <?php echo number_format($totalProfit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <!--<div class="list-card card-addmoney">-->
                <!--    <div class="list-header">Total Add Money</div>-->
                <!--    <div class="list-item"><span>UPI</span><span><?php echo $add_upi; ?></span></div>-->
                <!--    <div class="list-item"><span>Gateway</span><span><?php echo $add_gateway; ?></span></div>-->
                <!--    <div class="list-item"><span>Manually</span><span><?php echo $add_manual; ?></span></div>-->
                <!--    <div class="list-item total"><span>Total</span><span><?php echo $add_total; ?></span></div>-->
                <!--</div>-->
                <!--<a href="add-points-user-wallet.php" style="text-decoration:none;color:inherit;">-->
                <a href="add-points-user-wallet.php?date_filter=<?php echo date('Y-m-d'); ?>" style="text-decoration:none;color:inherit;">
                <div class="list-card card-addmoney" style="cursor:pointer;">
                    <div class="list-header">Total Add Money</div>
                    <div class="list-item"><span>UPI</span><span><?php echo $add_upi; ?></span></div>
                    <div class="list-item"><span>Gateway</span><span><?php echo $add_gateway; ?></span></div>
                    <div class="list-item"><span>Manually</span><span><?php echo $add_manual; ?></span></div>
                    <div class="list-item total"><span>Total</span><span><?php echo $add_total; ?></span></div>
                </div>
            </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <!--<a href="withdraw-points-request.php" style="text-decoration:none;color:inherit;">-->
                <a href="withdraw-points-request.php?date_filter=<?php echo date('Y-m-d'); ?>" style="text-decoration:none;color:inherit;">
                    <div class="list-card card-withdraw" style="cursor:pointer;">
                        <div class="list-header">Total Withdraw Money</div>
                        <div class="list-item"><span>Request</span><span><?php echo $wd_request; ?></span></div>
                        <div class="list-item"><span>Manually</span><span><?php echo $wd_manual; ?></span></div>
                        <div class="list-item total"><span>Total</span><span><?php echo $wd_total; ?></span></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <a href="refund-history.php" style="text-decoration:none; color:inherit;">
                <div class="list-card card-reject">
                    <div class="list-header">Reject Money Request</div>
                    <div class="list-item"><span>Add Money Reject</span><span><?php echo $reject_add_money; ?></span></div>
                    <div class="list-item"><span>Withdraw Money Reject</span><span><?php echo $reject_withdraw_money; ?></span></div>
                </div>
                </a>
            </div>
        </div>

    </div>
</div>

<?php 
} else {
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); 
?>

<script>
$(document).ready(function() {
    $('#setDateBtn').click(function(){
        var selectedDate = $('#resultDate').val();
      //  window.location.href = 'dashboard.php?date=' + selectedDate;
    });

    // $('#statusBtn').click(function() {
    //     var currentStatus = $(this).attr('data-status');
    //     var newStatus = (currentStatus == 1) ? 0 : 1;
    //     $.ajax({
    //         type: "POST",
    //         url: "update_game_status.php", 
    //         data: { status: newStatus },
    //         success: function(response) {
    //             location.reload();
    //         }
    //     });
    // });
     $('#setDateBtn').click(function(){
        var selectedDate = $('#resultDate').val();
        if(selectedDate != ""){
            // This reloads the page with the new date in the URL
            window.location.href = 'dashboard1.php?date=' + selectedDate;
        }
    });
    $('#statusBtn').click(function() {

        var currentStatus = $(this).attr('data-status');
        var newStatus = (currentStatus == 1) ? 0 : 1;
    
        var message = (newStatus == 1) 
            ? "Do you want to ACTIVATE the game?" 
            : "Do you want to SUSPEND the game?";
    
        if(confirm(message)){
    
            $.ajax({
                type: "POST",
                url: "update_game_status.php",
                data: { status: newStatus },
    
                success: function(response){
    
                    console.log(response);   // see response in console
                    // alert(response);         // popup response
    
                    location.reload();       // refresh after response
                },
    
                error: function(xhr){
                    alert("Error: " + xhr.responseText);
                }
    
            });
    
        }
    
    });
});
</script>