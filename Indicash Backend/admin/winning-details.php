<?php include('header.php'); 
if (in_array(8, $HiddenProducts)){ ?>

<style>
    /* Full Page App Look */
    body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
    .full-page-app { padding-bottom: 60px; }

    /* Filter Section Styling */
    .filter-container { padding: 12px; background: #fff; border-bottom: 1px solid #ddd; }
    .filter-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
    .filter-item { flex: 1; min-width: calc(50% - 10px); }
    .filter-label { font-size: 11px; color: #666; font-weight: 600; margin-bottom: 2px; display: block; }
    .app-input { 
        width: 100%; border-radius: 10px; border: 1px solid #d1d9e6; 
        padding: 6px 10px; background: #f0f4f8; font-size: 13px; height: 36px;
    }
    
    .btn-filter { 
        background-color: #007bff; color: white; border: none; border-radius: 20px; 
        padding: 8px 30px; font-weight: bold; width: 100%; margin-top: 5px; 
    }

    /* Status Pills - Scrollable */
    .pill-container { 
        display: flex; overflow-x: auto; gap: 8px; padding: 10px; 
        white-space: nowrap; -webkit-overflow-scrolling: touch; 
    }
    .pill-container::-webkit-scrollbar { display: none; }
    .pill { padding: 6px 18px; border-radius: 20px; color: white; font-size: 11px; font-weight: 600; border: none; flex-shrink: 0; }
    .pill-all { background-color: #007bff; }
    .pill-win { background-color: #28a745; }
    .pill-loose { background-color: #dc3545; }
    .pill-pending { background-color: #ffc107; color: #333; }
    .pill-cancelled { background-color: #17a2b8; }

    /* Orange App Header with Darker Dividers */
    .app-table-header {
        background-color: #fd7e14; display: flex; color: white; font-weight: bold;
        padding: 10px 0; text-align: center; font-size: 11px;
    }
    .header-col { 
        flex: 1; 
        border-right: 1.5px solid rgba(255,255,255,0.6); /* Darker dividers */
        line-height: 1.2; display: flex; align-items: center; justify-content: center; 
    }
    .header-col:last-child { border-right: none; }

    /* Row Styling */
    .data-row {
        background: white; display: flex; padding: 10px 0;
        border-bottom: 1px solid #eee; text-align: center;
        font-size: 11px; align-items: center;
    }
    .data-col { flex: 1; word-break: break-word; padding: 0 4px; }

    /* Fixed Bottom Navigation */
    /* Fixed Bottom Navigation Styling */
    .app-pagination {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 60px;
        background: #fff; /* White background for the bar */
        display: flex;
        justify-content: center;
        align-items: center;
        gap:15px;
        padding: 0 10px;
        z-index: 1000;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }

    .pagination-btn {
        background-color: #007bff;
        color: white;
        padding: 8px 25px;
        border-radius: 25px; /* Capsule shape */
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
        min-width: 100px;
        text-align: center;
    }

    .page-indicator {
        background: #6c757d; /* Grey background for the middle part */
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: bold;
        min-width: 60px;
        text-align: center;
    }
    
</style>

<div class="full-page-app">
    <form method="GET">
    <div class="filter-container">
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">Game Type</span>
                <select name="game_type" class="app-input">
                    <option value="">Game Type</option>
                    <option value="Single Ank">Single Ank</option>
                    <option value="Jodi">Jodi</option>
                    <option value="Single Pana">Single Pana</option>
                    <option value="Double Pana">Double Pana</option>
                    <option value="Triple Pana">Triple Pana</option>
                    <option value="Half Sangam">Half Sangam</option>
                    <option value="Full Sangam">Full Sangam</option>
                </select>
            </div>
            <div class="filter-item">
                <span class="filter-label">Game List</span>
                <select name="game_name" class="app-input">
                    <option value="">All Game</option>
                    <?php 
                    // Dynamic Game Fetching
                    $games_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games ORDER BY bazar ASC");
                    while($g_list = mysqli_fetch_assoc($games_q)){
                        echo '<option value="'.$g_list['bazar'].'">'.$g_list['bazar'].'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">Date</span>
                <input type="date" name="date" class="app-input" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="filter-item">
                <span class="filter-label">Open / Close</span>
                <select name="session" class="app-input">
                    <option value="">Both</option>
                    <option value="Open">Open</option>
                    <option value="Close">Close</option>
                </select>
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">User</span>
                <select name="user_mobile" class="app-input select2">
                    <option value="">Search for a user</option>
                    <?php 
                    // Dynamic User Fetching
                    $users_q = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");
                    while($u_list = mysqli_fetch_assoc($users_q)){
                        echo '<option value="'.$u_list['mobile'].'">'.$u_list['name'].' ('.$u_list['mobile'].')</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="filter-item">
                <span class="filter-label">Number</span>
                <input type="number" name="bid_no" class="app-input" placeholder="">
            </div>
        </div>
        <button type="submit" class="btn-filter">Filter</button>
    </div>
    </form>

    <div class="pill-container">
        <button class="pill pill-all">All</button>
        <button class="pill pill-win">Win</button>
        <button class="pill pill-loose">loose</button>
        <button class="pill pill-pending">pending</button>
        <button class="pill pill-cancelled">cancelled</button>
    </div>

    <div class="table-responsive-wrapper">
        <div class="min-width-table">
            <div class="app-table-header">
                <div class="header-col">Username<br>Phone</div>
                <div class="header-col">Market<br>Type<br>Time</div>
                <div class="header-col">Status</div>
                <div class="header-col">No.</div>
                <div class="header-col">Amount</div>
                <div class="header-col">Win Amt</div>
            </div>

            <div class="app-data-body">
                <?php
                $num_results_on_page = 10;
                $page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
                $start_from = ($page - 1) * $num_results_on_page;

                $result = mysqli_query($con, "SELECT * FROM transactions WHERE remark LIKE '%winning%' ORDER BY sn DESC LIMIT $start_from, $num_results_on_page");
                
                $count_res = mysqli_query($con, "SELECT COUNT(*) as total FROM transactions WHERE remark LIKE '%winning%'");
                $count_row = mysqli_fetch_assoc($count_res);
                $total_records = $count_row['total'] ?? 0;

                while ($row = mysqli_fetch_array($result)) {
                    $userID = htmlspecialchars($row['user']);
                    $user_q = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile` = '$userID' ");
                    $fetch = mysqli_fetch_array($user_q);

                    $gameID = $row['game_id'];
                    $game_q = mysqli_query($con, "SELECT * FROM `games` WHERE `sn` = '$gameID' ");
                    $g = mysqli_fetch_array($game_q);

                    if (!empty($fetch['name'])) {
                ?>
                    <div class="data-row">
                        <div class="data-col">
                            <strong><?php echo htmlspecialchars($fetch['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($fetch['mobile']); ?></small>
                        </div>
                        <div class="data-col">
                            <?php echo htmlspecialchars($g['bazar'] ?? 'N/A'); ?><br>
                            <small><?php echo htmlspecialchars($g['game'] ?? 'N/A'); ?></small><br>
                            <?php echo date('h:i A', $row['created_at']); ?>
                        </div>
                        <div class="data-col">Win</div>
                        <div class="data-col"><?php echo htmlspecialchars($g['number'] ?? '-'); ?></div>
                        <div class="data-col"><?php echo htmlspecialchars($g['amount'] ?? '0'); ?></div>
                        <div class="data-col" style="color: #28a745; font-weight: bold;">
                            <?php echo htmlspecialchars($row['amount']); ?>
                        </div>
                    </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="app-pagination">
        <div class="pagination-btn" onclick="window.location.href='?page=<?php echo max(1, $page-1); ?>'">PREVIOUS</div>
        
        <div class="page-indicator">
            <?php echo $page; ?> / <?php echo ceil($total_records / $num_results_on_page); ?>
        </div>
        
        <div class="pagination-btn" onclick="window.location.href='?page=<?php echo $page+1; ?>'">NEXT</div>
    </div>

<?php } else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); ?>