<?php 
include('header.php');

// 1. Permission Check
if (!in_array(15, $HiddenProducts)){
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

$selected_mobile = $_GET['mobile'] ?? '';
$user_name_for_display = '';

if ($selected_mobile) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$selected_mobile'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

if(isset($_GET['success'])){
    echo "<script>alert('Game Cancelled Successfully');</script>";
}

// --- FETCH DATA FOR DROPDOWNS ---
$user_list_query = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");
$game_dropdown_query = mysqli_query($con, "SELECT market FROM gametime_manual UNION SELECT market FROM gametime_new ORDER BY market ASC");

/* ---------------- GET FILTER VALUES ---------------- */
$game_filter   = mysqli_real_escape_string($con, $_GET['game'] ?? '');
$date_filter   = mysqli_real_escape_string($con, $_GET['date'] ?? date('Y-m-d'));
$status_filter = $_GET['status'] ?? 'Both';
$user_filter   = mysqli_real_escape_string($con, $_GET['user'] ?? $selected_mobile);

// Build SQL with Date Conversion
$query = "SELECT g.*, u.name as user_name FROM games g
          LEFT JOIN users u ON u.mobile = g.user
          WHERE STR_TO_DATE(g.date,'%d/%m/%Y') = '$date_filter' AND g.status = 0 ";

// Market Filter (Handles MILAN NIGHT vs MILAN_NIGHT)
if($game_filter != ''){
    $market_db = strtoupper(str_replace(" ", "_", trim($game_filter)));
    $query .= " AND (g.bazar = '$game_filter' OR g.bazar = '$market_db' OR g.bazar LIKE '$market_db%')";
}

// Session Filter
if($status_filter != 'Both'){
    $session_val = strtoupper($status_filter);
    $query .= " AND g.game_type='$session_val'";
}

// User Filter
if($user_filter != ''){
    $query .= " AND g.user='$user_filter'";
}

$query .= " ORDER BY g.sn DESC";
$result = mysqli_query($con, $query);

// ---- GROUP ROWS: same user + same bazar + same game_type = one card ----
$grouped = [];
if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $key = $row['user'] . '||' . $row['bazar'] . '||' . strtoupper($row['game_type'] ?? 'OPEN');
        if(!isset($grouped[$key])){
            $grouped[$key] = [
                'user'       => $row['user'],
                'user_name'  => $row['user_name'] ?? $row['user'],
                'bazar'      => $row['bazar'],
                'game_type'  => $row['game_type'] ?? 'OPEN',
                'created_at' => $row['created_at'] ?? '',
                'total'      => 0,
                'bids'       => [],
                'sn_list'    => [],
            ];
        }
        $grouped[$key]['bids'][]    = $row['number'] . '(' . $row['amount'] . ')';
        $grouped[$key]['total']    += (int)$row['amount'];
        $grouped[$key]['sn_list'][] = $row['sn'];
        if(empty($grouped[$key]['created_at']) && !empty($row['created_at'])){
            $grouped[$key]['created_at'] = $row['created_at'];
        }
    }
}

// --- HANDLE CANCELLATION (Universal Logic) ---
if(isset($_POST['cancel_selected']) || isset($_POST['cancel_single'])){
    $ids_string = isset($_POST['cancel_selected']) ? implode(',', $_POST['bets'] ?? []) : $_POST['cancel_single'];
    $ids = explode(',', $ids_string);
    $count = 0;

    foreach($ids as $bet_id){
        $bet_id = trim(mysqli_real_escape_string($con, $bet_id));
        if($bet_id === '') continue;

        $bet_res = mysqli_query($con, "SELECT * FROM games WHERE sn='$bet_id' AND status=0 LIMIT 1");
        $bet = mysqli_fetch_assoc($bet_res);
        
        if($bet) {
            $amount = (float)$bet['amount'];
            $mobile = $bet['user'];
            $bazar  = $bet['bazar'];
            $g_type = $bet['game'];
            $num    = $bet['number'];

            // 1. Get current wallet before update
            $user_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' LIMIT 1");
            $u_data = mysqli_fetch_assoc($user_q);
            $wallet_before = (float)($u_data['wallet'] ?? 0);
            $wallet_after  = $wallet_before + $amount;

            // 2. Perform Updates
            $wallet_update = mysqli_query($con, "UPDATE users SET wallet = '$wallet_after' WHERE mobile='$mobile'");
            $status_update = mysqli_query($con, "UPDATE games SET status=2 WHERE sn='$bet_id'");

            if($wallet_update && $status_update){
                $remark = "Game: $g_type | Market: $bazar | Number: $num | Bet Cancel Refund";
                // 3. Log Transaction with Full Balance Tracking
                mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `owner`, `created_at`, `game_id`) 
                                   VALUES ('$mobile', '$amount', '$wallet_before', '$wallet_after', '1', '$remark', 'admin', UNIX_TIMESTAMP(), '$bet_id')");
                $count++;
            }
        }
    }
    if($count > 0) {
        echo "<script>alert('Successfully cancelled $count bets and refunded users.'); window.location.href='game-cancel.php?success=1';</script>";
        exit;
    }
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    * { box-sizing: border-box; }
    .interface-wrapper { background-color: #f0f0f0; min-height: 100vh; padding: 12px; font-family: 'Arial', sans-serif; }
    .ui-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin: 0 0 4px 8px; }
    .ui-field { border-radius: 25px !important; border: 1px solid #ccc !important; padding: 10px 15px !important; height: 46px !important; width: 100%; background-color: #fff; margin-bottom: 12px; font-size: 13px; display: block; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 10px; }
    .btn-blue-history { background-color: #007bff; color: white; border-radius: 15px; padding: 10px 14px; border: none; font-size: 14px; width: 100%; margin-bottom: 12px; cursor: pointer; }
    .btn-filter-dark { background-color: #003366; color: white; border-radius: 25px; padding: 12px 0; border: none; font-size: 15px; width: 100%; font-weight: bold; cursor: pointer; margin-bottom: 12px; }
    .btn-red-cancel { background-color: #d9434e; color: white; border-radius: 20px; padding: 9px 20px; border: none; font-size: 14px; font-weight: bold; cursor: pointer; }
    .footer-row { display: flex; justify-content: space-between; align-items: center; margin: 16px 0 14px; padding: 0 4px; }
    .select-all-wrap { font-size: 15px; font-weight: bold; display: flex; align-items: center; gap: 8px; }
    .select-all-wrap input { width: 20px; height: 20px; cursor: pointer; }
    .bet-card { background: #e8f5f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
    .bet-card-top { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .user-pill { background: #2196f3; color: white; border-radius: 20px; padding: 5px 16px; font-size: 14px; font-weight: 600; }
    .bet-card-mid { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
    .bet-card-game { font-size: 15px; font-weight: 700; color: #111; line-height: 1.4; }
    .bet-card-time { font-size: 13px; color: #333; text-align: right; min-width: 130px; }
    .bet-card-bid { font-size: 14px; color: #333; margin-bottom: 10px; }
    .bet-card-footer { display: flex; justify-content: space-between; align-items: center; }
    .amount-pill { background: #2e7d32; color: white; border-radius: 20px; padding: 6px 16px; font-size: 14px; font-weight: 600; }
    .btn-bid-cancel { background: #d9434e; color: white; border: none; border-radius: 20px; padding: 8px 20px; font-size: 14px; font-weight: 600; cursor: pointer; }
</style>

<div class="interface-wrapper">
    <form method="GET">
        <div class="two-col">
            <div><a href="game-cancel-history.php" class="btn-blue-history shadow-sm d-block text-center" style="text-decoration:none;">Game Cancel<br>History</a></div>
            <div>
                <label class="ui-label">Game List</label>
                <select name="game" class="ui-field shadow-sm">
                    <option value="">All Game</option>
                    <?php while($g = mysqli_fetch_assoc($game_dropdown_query)): ?>
                        <option value="<?= $g['market'] ?>" <?= ($game_filter == $g['market']) ? 'selected' : '' ?>><?= $g['market'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="two-col">
            <div><label class="ui-label">Date</label><input type="date" name="date" class="ui-field shadow-sm" value="<?= $date_filter ?>"></div>
            <div>
                <label class="ui-label">Open / Close</label>
                <select name="status" class="ui-field shadow-sm">
                    <option value="Both" <?= ($status_filter == 'Both') ? 'selected' : '' ?>>Both</option>
                    <option value="Open" <?= ($status_filter == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Close" <?= ($status_filter == 'Close') ? 'selected' : '' ?>>Close</option>
                </select>
            </div>
        </div>
        <div class="two-col">
            <div>
                <label class="ui-label">User</label>
                <select name="user" class="ui-field shadow-sm">
                    <option value="">Search for a user</option>
                    <?php if($selected_mobile) echo '<option value="'.$selected_mobile.'" selected>'.$user_name_for_display.'</option>'; ?>
                    <?php while($u = mysqli_fetch_assoc($user_list_query)): ?>
                        <option value="<?= $u['mobile'] ?>" <?= ($user_filter == $u['mobile']) ? 'selected' : '' ?>><?= $u['name'] ?> (<?= $u['mobile'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display:flex; align-items:flex-end;"><button type="submit" class="btn-filter-dark shadow">Filter</button></div>
        </div>
    </form>

    <form method="POST">
        <div class="footer-row">
            <div class="select-all-wrap">Select All <input type="checkbox" id="selectAll"></div>
            <div><button type="submit" name="cancel_selected" class="btn-red-cancel shadow">Select Cancel</button></div>
        </div>

        <?php if(!empty($grouped)): ?>
            <?php foreach($grouped as $group): 
                $snJoined = implode(',', $group['sn_list']);
                $timestamp = !empty($group['created_at']) ? date('h:i:s A d-m-Y', is_numeric($group['created_at']) ? $group['created_at'] : strtotime($group['created_at'])) : '';
            ?>
            <div class="bet-card">
                <div class="bet-card-top">
                    <input type="checkbox" name="bets[]" value="<?= $snJoined ?>" class="bet-check" style="width:20px;height:20px;">
                    <span class="user-pill"><?= htmlspecialchars($group['user_name']) ?></span>
                </div>
                <div class="bet-card-mid">
                    <div class="bet-card-game"><strong>Game : <?= strtoupper($group['bazar']) ?><br><?= strtoupper($group['game_type']) ?></strong></div>
                    <div class="bet-card-time"><?= $timestamp ?></div>
                </div>
                <div class="bet-card-bid">Bid : <?= implode(', ', $group['bids']) ?></div>
                <div class="bet-card-footer">
                    <span class="amount-pill"><?= $group['total'] ?> Rs.</span>
                    <button type="submit" name="cancel_single" value="<?= $snJoined ?>" class="btn-bid-cancel">Bid Cancel</button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;padding:20px;color:#666;">No Pending Games Found</p>
        <?php endif; ?>
    </form>
</div>

<script>
document.getElementById("selectAll").addEventListener("change", function(){
    let checkboxes = document.querySelectorAll(".bet-check");
    checkboxes.forEach(function(cb){ cb.checked = document.getElementById("selectAll").checked; });
});
</script>
<?php include('footer.php'); ?>