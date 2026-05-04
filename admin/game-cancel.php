<?php 
include('header.php');
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

// 1. Permission Check
if (!in_array(15, $HiddenProducts)){
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

// --- FETCH USERS ---
$user_list_query = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");

// --- FETCH GAMES ---
$game_dropdown_query = mysqli_query($con, "SELECT market FROM gametime_manual UNION SELECT market FROM gametime_new ORDER BY market ASC");

/* ---------------- GET FILTER VALUES ---------------- */
$game   = $_GET['game']   ?? '';
$date   = $_GET['date']   ?? date('Y-m-d');
$status = $_GET['status'] ?? 'Both';
$user   = $_GET['user']   ?? '';

$query = "SELECT g.*, u.name as user_name FROM games g
          LEFT JOIN users u ON u.mobile = g.user
          WHERE STR_TO_DATE(g.date,'%d/%m/%Y') = '$date' AND g.status = 0 ";

if($game != ''){
    $query .= " AND g.bazar='$game'";
}

if(!empty($status) && $status != 'Both'){
    $query .= " AND g.game_type='".strtoupper($status)."'";
}

if($user != ''){
    $query .= " AND g.user='$user'";
}

$query .= " ORDER BY g.sn DESC";

$result = mysqli_query($con, $query);

// ---- GROUP ROWS: same user + same bazar + same game_type = one card ----
$grouped = [];
if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $key = $row['user'] . '||' . $row['bazar'] . '||' . strtoupper($row['game_type'] ?? '');
        if(!isset($grouped[$key])){
            $grouped[$key] = [
                'user'       => $row['user'],
                'user_name'  => $row['user_name'] ?? $row['user'],
                'bazar'      => $row['bazar'],
                'game_type'  => $row['game_type'] ?? '',
                'created_at' => $row['created_at'] ?? '',
                'total'      => 0,
                'bids'       => [],
                'sn_list'    => [],
            ];
        }
        $grouped[$key]['bids'][]    = $row['number'] . '(' . $row['amount'] . ')';
        $grouped[$key]['total']    += (int)$row['amount'];
        $grouped[$key]['sn_list'][] = $row['sn'];
        // Keep earliest timestamp for the group
        if(empty($grouped[$key]['created_at']) && !empty($row['created_at'])){
            $grouped[$key]['created_at'] = $row['created_at'];
        }
    }
}

// Handle bulk cancel
if(isset($_POST['cancel_selected'])){
    if(!empty($_POST['bets'])){
        $count = 0;
        $ids = explode(',', implode(',', $_POST['bets'])); // bets[] may be comma-joined groups
        foreach($ids as $bet_id){
            $bet_id = trim(mysqli_real_escape_string($con, $bet_id));
            if($bet_id === '') continue;
            $bet_res = mysqli_query($con, "SELECT * FROM games WHERE sn='$bet_id'");
            $bet = mysqli_fetch_assoc($bet_res);
            if(!$bet) continue;
            if($bet['status'] == 2) continue;

            $amount = $bet['amount'];
            $mobile = $bet['user'];
            $bazar  = $bet['bazar'];
             $game_type = $bet['game']; // e.g., single, jodi, etc.
            $number = $bet['number'];

            // 1. Get current wallet before update
            $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' LIMIT 1");
            $wallet_row = mysqli_fetch_assoc($wallet_q);
            $wallet_before = (float)$wallet_row['wallet'];
            $wallet_after  = $wallet_before + $amount;

            $wallet_update = mysqli_query($con, "UPDATE users SET wallet = wallet + $amount WHERE mobile='$mobile'");
            $status_update = mysqli_query($con, "UPDATE games SET status=2 WHERE sn='$bet_id'");

            if($wallet_update && $status_update) {
                // $remark = "Bet Cancel Refund ($bazar)";
                $remark = "Game: $game_type | Market: $bazar | Number: $number | Bet Cancel Refund";

                // mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `type`, `remark`, `owner`, `created_at`) 
                //                   VALUES ('$mobile', '$amount', '1', '$remark', 'admin', NOW())");
                mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `owner`, `created_at`, `game_id`) 
                                   VALUES ('$mobile', '$amount', '$wallet_before', '$wallet_after', '1', '$remark', 'admin', NOW(), '$bet_id')");
                $count++;
            }
        }

        // if($count > 0) {
        //     header("Location: game-cancel.php?success=1");
        //     exit();
        // }
        echo "<script>alert('Game Cancelled Successfully'); window.location.href='game-cancel.php?success=1';</script>";

    } else {
        echo "<script>alert('Please select at least one bet to cancel');</script>";
    }
}

// Handle single/group cancel via Bid Cancel button
if(isset($_POST['cancel_single'])){
    $ids = explode(',', $_POST['cancel_single']);
    $count = 0;
    foreach($ids as $bet_id){
        $bet_id = trim(mysqli_real_escape_string($con, $bet_id));
        if($bet_id === '') continue;
        $bet_res = mysqli_query($con, "SELECT * FROM games WHERE sn='$bet_id'");
        $bet = mysqli_fetch_assoc($bet_res);
        if(!$bet || $bet['status'] == 2) continue;

        $amount = $bet['amount'];
        $mobile = $bet['user'];
        $bazar  = $bet['bazar'];
        $game_type = $bet['game'];
        $number = $bet['number'];

        // 1. Get current wallet before update
        $wallet_q = mysqli_query($con, "SELECT wallet FROM users WHERE mobile='$mobile' LIMIT 1");
        $wallet_row = mysqli_fetch_assoc($wallet_q);
        $wallet_before = (float)$wallet_row['wallet'];
        $wallet_after  = $wallet_before + $amount;

        $wallet_update = mysqli_query($con, "UPDATE users SET wallet = wallet + $amount WHERE mobile='$mobile'");
        $status_update = mysqli_query($con, "UPDATE games SET status=2 WHERE sn='$bet_id'");

        if($wallet_update && $status_update){
            // $remark = "Bet Cancel Refund ($bazar)";
            // mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `type`, `remark`, `owner`, `created_at`) 
            //                   VALUES ('$mobile', '$amount', '1', '$remark', 'admin', NOW())");
            $remark = "Game: $game_type | Market: $bazar | Number: $number | Bet Cancel Refund";
            
            // 4. Insert transaction with wallet tracking
            mysqli_query($con, "INSERT INTO `transactions` (`user`, `amount`, `wallet_before`, `wallet_after`, `type`, `remark`, `owner`, `created_at`, `game_id`) 
                               VALUES ('$mobile', '$amount', '$wallet_before', '$wallet_after', '1', '$remark', 'admin', NOW(), '$bet_id')");
            $count++;
        }
    }
    // if($count > 0){
    //     header("Location: game-cancel.php?success=1");
    //     exit();
    // }
    echo "<script>alert('Game Cancelled Successfully'); window.location.href='game-cancel.php?success=1';</script>";
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    * { box-sizing: border-box; }

    .interface-wrapper {
        background-color: #f0f0f0;
        min-height: 100vh;
        padding: 12px;
        font-family: 'Arial', sans-serif;
    }

    .ui-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
        margin-left: 8px;
    }

    .ui-field {
        border-radius: 25px !important;
        border: 1px solid #ccc !important;
        padding: 10px 15px !important;
        height: 46px !important;
        width: 100%;
        background-color: #fff;
        margin-bottom: 12px;
        font-size: 13px;
        display: block;
    }

    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 10px;
    }

    .btn-blue-history {
        background-color: #007bff;
        color: white;
        border-radius: 15px;
        padding: 10px 14px;
        border: none;
        font-size: 14px;
        line-height: 1.3;
        width: 100%;
        text-align: center;
        margin-bottom: 12px;
        cursor: pointer;
    }

    .btn-filter-dark {
        background-color: #003366;
        color: white;
        border-radius: 25px;
        padding: 12px 0;
        border: none;
        font-size: 15px;
        width: 100%;
        font-weight: bold;
        cursor: pointer;
        margin-bottom: 12px;
    }

    .btn-red-cancel {
        background-color: #d9434e;
        color: white;
        border-radius: 20px;
        padding: 9px 20px;
        border: none;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
    }

    .footer-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 16px;
        padding: 0 4px;
        margin-bottom: 14px;
    }

    .select-all-wrap {
        font-size: 15px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .select-all-wrap input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .filter-cell {
        display: flex;
        align-items: flex-end;
    }

    /* Bet Cards */
    .bet-card {
        background: #e8f5f0;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }

    .bet-card-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .bet-card-check {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        cursor: pointer;
    }

    .user-pill {
        background: #2196f3;
        color: white;
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 14px;
        font-weight: 600;
    }

    .bet-card-mid {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }

    .bet-card-game {
        font-size: 15px;
        font-weight: 700;
        color: #111;
        line-height: 1.4;
    }

    .bet-card-time {
        font-size: 13px;
        color: #333;
        text-align: right;
        min-width: 130px;
        line-height: 1.4;
    }

    .bet-card-bid {
        font-size: 14px;
        color: #333;
        margin-bottom: 10px;
    }

    .bet-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .amount-pill {
        background: #2e7d32;
        color: white;
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-bid-cancel {
        background: #d9434e;
        color: white;
        border: none;
        border-radius: 20px;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div class="interface-wrapper">

    <!-- Filter Form -->
    <form method="GET" action="">

        <!-- Row 1: History button | Game List -->
        <div class="two-col">
            <div>
                <a href="game-cancel-history.php" class="btn-blue-history shadow-sm d-block text-center" style="text-decoration:none;">
                    Game Cancel<br>History
                </a>
            </div>
            <div>
                <label class="ui-label">Game List</label>
                <select name="game" class="ui-field shadow-sm">
                    <option value="">All Game</option>
                    <?php while($g = mysqli_fetch_assoc($game_dropdown_query)): ?>
                        <option value="<?= $g['market'] ?>" <?= ($game == $g['market']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['market']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- Row 2: Date | Open/Close -->
        <div class="two-col">
            <div>
                <label class="ui-label">Date</label>
                <input type="date" name="date" class="ui-field shadow-sm" value="<?= htmlspecialchars($date) ?>">
            </div>
            <div>
                <label class="ui-label">Open / Close</label>
                <select name="status" class="ui-field shadow-sm">
                    <option value="Both" <?= ($status == 'Both') ? 'selected' : '' ?>>Both</option>
                    <option value="Open"  <?= ($status == 'Open')  ? 'selected' : '' ?>>Open</option>
                    <option value="Close" <?= ($status == 'Close') ? 'selected' : '' ?>>Close</option>
                </select>
            </div>
        </div>

        <!-- Row 3: User | Filter button -->
        <div class="two-col">
            <div>
                <label class="ui-label">User</label>
                <?php if ($selected_mobile): ?>
                    <input type="text" class="ui-field shadow-sm" value="<?= htmlspecialchars($user_name_for_display) ?>" readonly>
                    <input type="hidden" name="user" value="<?= htmlspecialchars($selected_mobile) ?>">
                <?php else: ?>
                    <select name="user" class="ui-field shadow-sm">
                        <option value="">Search for a user</option>
                        <?php while($u = mysqli_fetch_assoc($user_list_query)): ?>
                            <option value="<?= $u['mobile'] ?>" <?= ($user == $u['mobile']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['mobile']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="filter-cell">
                <button type="submit" class="btn-filter-dark shadow">Filter</button>
            </div>
        </div>

    </form>

    <!-- Cancel Form -->
    <form method="POST" action="">

        <!-- Select All / Select Cancel -->
        <div class="footer-row">
            <div class="select-all-wrap">
                Select All
                <input type="checkbox" id="selectAll">
            </div>
            <div>
                <button type="submit" name="cancel_selected" class="btn-red-cancel shadow">
                    Select Cancel
                </button>
            </div>
        </div>

        <!-- Grouped Bet Cards -->
        <?php if(!empty($grouped)): ?>
            <?php foreach($grouped as $group): 
                $snJoined  = implode(',', $group['sn_list']);
                $bidString = implode(', ', $group['bids']);
                $timestamp = '';
                if(!empty($group['created_at'])){
                    $timestamp = date('h:i:s A d-m-Y', strtotime($group['created_at']));
                }
            ?>
            <div class="bet-card">

                <div class="bet-card-top">
                    <!-- One checkbox carries ALL sn ids for this group as comma-separated value -->
                    <input type="checkbox" name="bets[]" value="<?= htmlspecialchars($snJoined) ?>" class="bet-check bet-card-check">
                    <span class="user-pill"><?= htmlspecialchars($group['user_name']) ?></span>
                </div>

                <div class="bet-card-mid">
                    <div class="bet-card-game">
                        <strong>Game : <?= htmlspecialchars($group['bazar']) ?><br><?= strtolower(htmlspecialchars($group['game_type'])) ?></strong>
                    </div>
                    <?php if($timestamp): ?>
                    <div class="bet-card-time"><?= $timestamp ?></div>
                    <?php endif; ?>
                </div>

                <div class="bet-card-bid">Bid : <?= htmlspecialchars($bidString) ?></div>

                <div class="bet-card-footer">
                    <span class="amount-pill"><?= $group['total'] ?> Rs.</span>
                    <button type="submit" name="cancel_single" value="<?= htmlspecialchars($snJoined) ?>" class="btn-bid-cancel">
                        Bid Cancel
                    </button>
                </div>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;padding:20px;color:#666;">No Games Found</p>
        <?php endif; ?>

    </form>

</div>

<script>
document.getElementById("selectAll").addEventListener("change", function(){
    let checkboxes = document.querySelectorAll(".bet-check");
    checkboxes.forEach(function(cb){
        cb.checked = document.getElementById("selectAll").checked;
    });
});
</script>

<?php include('footer.php'); ?>