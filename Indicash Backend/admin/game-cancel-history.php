<?php 
include('header.php');

// 1. Permission Check
if (!in_array(15, $HiddenProducts)){
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

/* ---------------- GET FILTER VALUES ---------------- */
$game_filter   = mysqli_real_escape_string($con, $_GET['game'] ?? '');
$date_filter   = mysqli_real_escape_string($con, $_GET['date'] ?? date('Y-m-d'));
$status_filter = $_GET['status'] ?? 'Both';
$user_filter   = mysqli_real_escape_string($con, $_GET['user'] ?? '');

// --- DATABASE QUERY (Filtering for Status = 2 which is Cancelled) ---
// We join with users table to show Names instead of just mobile numbers
$query = "SELECT g.*, u.name as user_name 
          FROM games g 
          LEFT JOIN users u ON g.user = u.mobile 
          WHERE g.status = 2";

// 1. IMPROVED DATE FILTER
if($date_filter != ''){
    $query .= " AND STR_TO_DATE(g.date,'%d/%m/%Y') = '$date_filter'";
}

// 2. IMPROVED MARKET FILTER (Handles Spaces and Underscores)
if($game_filter != ''){
    $market_db = strtoupper(str_replace(" ", "_", trim($game_filter)));
    $query .= " AND (g.bazar = '$game_filter' OR g.bazar = '$market_db' OR g.bazar LIKE '$market_db%')";
}

// 3. IMPROVED SESSION FILTER
if($status_filter != 'Both'){
    $session_val = strtoupper($status_filter);
    $query .= " AND (g.game_type = '$session_val' OR g.bazar LIKE '%_$session_val')";
}

// 4. USER FILTER
if($user_filter != ''){
    $query .= " AND g.user = '$user_filter'";
}

$query .= " ORDER BY g.sn DESC";
$result = mysqli_query($con, $query);

// Fetch dropdown data
$user_list_query = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");
$game_dropdown_query = mysqli_query($con, "SELECT market FROM gametime_manual UNION SELECT market FROM gametime_new ORDER BY market ASC");
?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    * { box-sizing: border-box; }
    .interface-wrapper { background-color: #f0f0f0; min-height: 100vh; padding: 12px; font-family: 'Arial', sans-serif; }
    .page-title { text-align: center; font-size: 24px; color: #333; margin-bottom: 20px; font-weight: 600; }
    .ui-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 4px; margin-left: 8px; }
    .ui-field { border-radius: 25px !important; border: 1px solid #ccc !important; padding: 10px 15px !important; height: 46px !important; width: 100%; background-color: #fff; margin-bottom: 12px; font-size: 13px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 10px; }
    
    .btn-blue-action { background-color: #007bff; color: white; border-radius: 20px; padding: 10px 20px; border: none; font-size: 14px; cursor: pointer; text-decoration: none !important; display: inline-block; font-weight: bold; }
    .btn-filter-dark { background-color: #003366; color: white; border-radius: 25px; padding: 12px 0; border: none; font-size: 15px; width: 100%; font-weight: bold; cursor: pointer; }
    
    .no-record { text-align: center; font-size: 18px; font-weight: bold; margin-top: 30px; color: #666; }

    /* Table Styling */
    .table-card { background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); margin-top:20px; overflow:hidden; }
    .bet-table { width:100%; border-collapse:collapse; font-size:13px; }
    .bet-table th { background:#ff9800; color:black; padding:12px; text-align:center; border: 1px solid #e67e22; }
    .bet-table td { padding:10px; text-align:center; border: 1px solid #eee; color: #333; vertical-align: middle; }
    .bet-table tr:nth-child(even) { background-color: #fafafa; }
    
    .user-info { text-align: left !important; padding-left: 15px !important; }
    .user-info b { display: block; color: #000; }
    .user-info small { color: #007bff; font-weight: bold; }
</style>

<div class="interface-wrapper">
    <h2 class="page-title">Bid Cancel History</h2>

    <form method="GET" action="">
        <div class="two-col">
            <div>
                <a href="game-cancel.php" class="btn-blue-action shadow">← Back to Cancel</a>
            </div>
            <div>
                <label class="ui-label">Game List</label>
                <select name="game" class="ui-field shadow-sm">
                    <option value="">All Game</option>
                    <?php while($g = mysqli_fetch_assoc($game_dropdown_query)): ?>
                        <option value="<?= $g['market'] ?>" <?= ($game_filter == $g['market']) ? 'selected' : '' ?>><?= htmlspecialchars($g['market']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="two-col">
            <div>
                <label class="ui-label">Date</label>
                <input type="date" name="date" class="ui-field shadow-sm" value="<?= $date_filter ?>">
            </div>
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
                    <?php while($u = mysqli_fetch_assoc($user_list_query)): ?>
                        <option value="<?= $u['mobile'] ?>" <?= ($user_filter == $u['mobile']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?> (<?= $u['mobile'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-filter-dark shadow">Filter History</button>
            </div>
        </div>
    </form>

    <!-- Results Area -->
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="table-card">
            <table class="bet-table">
                <thead>
                    <tr>
                        <th>User Detail</th>
                        <th>Bazar / Time</th>
                        <th>Game Type</th>
                        <th>No.</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="user-info">
                            <b><?= htmlspecialchars($row['user_name'] ?? 'Unknown') ?></b>
                            <small><?= $row['user'] ?></small>
                        </td>
                        <td>
                            <div style="font-weight:bold;"><?= strtoupper(str_replace('_', ' ', $row['bazar'])) ?></div>
                            <div style="font-size:11px; color:#666;"><?= $row['date'] ?></div>
                        </td>
                        <td style="text-transform:uppercase; font-weight:bold; color:#d9434e;"><?= $row['game'] ?></td>
                        <td style="font-weight:bold; font-size:15px;"><?= $row['number'] ?></td>
                        <td style="font-weight:bold; color:green;"><?= $row['amount'] ?> Rs.</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-record">No Cancelled Bids found for this selection.</div>
    <?php endif; ?>

</div>

<?php include('footer.php'); ?>