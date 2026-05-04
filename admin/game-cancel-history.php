<?php 
include('header.php');

// 1. Permission Check
if (!in_array(15, $HiddenProducts)){
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

/* ---------------- GET FILTER VALUES ---------------- */
$game   = $_GET['game']   ?? '';
$date   = $_GET['date']   ?? date('Y-m-d');
$status = $_GET['status'] ?? 'Both';
$user   = $_GET['user']   ?? '';

// --- DATABASE QUERY (Filtering for Status = 2 which is Cancelled) ---
$query = "SELECT * FROM games WHERE status = 2";

if($date != ''){
    $query .= " AND STR_TO_DATE(date,'%d/%m/%Y') = '$date'";
}
if($game != ''){
    $query .= " AND bazar='$game'";
}
if(!empty($status) && $status != 'Both'){
    $query .= " AND game_type='".strtoupper($status)."'";
}
if($user != ''){
    $query .= " AND user='$user'";
}

$query .= " ORDER BY sn DESC";
$result = mysqli_query($con, $query);

// Fetch dropdown data
$user_list_query = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");
$game_dropdown_query = mysqli_query($con, "SELECT market FROM gametime_manual UNION SELECT market FROM gametime_new ORDER BY market ASC");
?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    * { box-sizing: border-box; }
    .interface-wrapper { background-color: #f0f0f0; min-height: 100vh; padding: 12px; font-family: 'Arial', sans-serif; }
    .page-title { text-align: center; font-size: 24px; color: #333; margin-bottom: 20px; font-weight: 500; }
    .ui-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 4px; margin-left: 8px; }
    .ui-field { border-radius: 25px !important; border: 1px solid #ccc !important; padding: 10px 15px !important; height: 46px !important; width: 100%; background-color: #fff; margin-bottom: 12px; font-size: 13px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 10px; }
    
    .btn-blue-action { background-color: #007bff; color: white; border-radius: 20px; padding: 10px 20px; border: none; font-size: 14px; cursor: pointer; text-decoration: none !important; display: inline-block; }
    .btn-filter-dark { background-color: #004a99; color: white; border-radius: 25px; padding: 12px 0; border: none; font-size: 15px; width: 100%; font-weight: bold; cursor: pointer; }
    
    .pagination-row { display: flex; justify-content: space-around; margin: 20px 0; }
    .btn-page { background-color: #007bff; color: white; border-radius: 20px; padding: 8px 30px; border: none; font-weight: bold; min-width: 100px; }
    
    .no-record { text-align: center; font-size: 18px; font-weight: bold; margin-top: 30px; color: #333; }

    /* Table Styling */
    .table-card { background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); margin-top:20px; overflow:hidden; }
    .bet-table { width:100%; border-collapse:collapse; font-size:13px; }
    .bet-table th { background:#007bff; color:white; padding:12px; text-align:center; }
    .bet-table td { padding:10px; text-align:center; border-bottom:1px solid #eee; }
</style>

<div class="interface-wrapper">
    <h2 class="page-title">Bid Cancel History</h2>

    <form method="GET" action="">
        <!-- Row 1: Cancel Bid Button | Game List -->
        <div class="two-col">
            <div>
                <a href="game-cancel.php" class="btn-blue-action shadow">Cancel Bid</a>
            </div>
            <div>
                <label class="ui-label">Game List</label>
                <select name="game" class="ui-field shadow-sm">
                    <option value="">All Game</option>
                    <?php while($g = mysqli_fetch_assoc($game_dropdown_query)): ?>
                        <option value="<?= $g['market'] ?>" <?= ($game == $g['market']) ? 'selected' : '' ?>><?= $g['market'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- Row 2: Date | Open/Close -->
        <div class="two-col">
            <div>
                <label class="ui-label">Date</label>
                <input type="date" name="date" class="ui-field shadow-sm" value="<?= $date ?>">
            </div>
            <div>
                <label class="ui-label">Open / Close</label>
                <select name="status" class="ui-field shadow-sm">
                    <option value="Both" <?= ($status == 'Both') ? 'selected' : '' ?>>Both</option>
                    <option value="Open" <?= ($status == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Close" <?= ($status == 'Close') ? 'selected' : '' ?>>Close</option>
                </select>
            </div>
        </div>

        <!-- Row 3: User | Filter Button -->
        <div class="two-col">
            <div>
                <label class="ui-label">User</label>
                <select name="user" class="ui-field shadow-sm">
                    <option value="">Search for a user</option>
                    <?php while($u = mysqli_fetch_assoc($user_list_query)): ?>
                        <option value="<?= $u['mobile'] ?>" <?= ($user == $u['mobile']) ? 'selected' : '' ?>>
                            <?= $u['name'] ?> (<?= $u['mobile'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-filter-dark shadow">Filter</button>
            </div>
        </div>
    </form>

    <!-- Pagination Buttons -->
    <div class="pagination-row">
        <button class="btn-page shadow">Back</button>
        <button class="btn-page shadow">Next</button>
    </div>

    <!-- Results Area -->
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="table-card">
            <table class="bet-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Bazar</th>
                        <th>Game</th>
                        <th>No.</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['user'] ?></td>
                        <td><?= $row['bazar'] ?></td>
                        <td><?= $row['game'] ?></td>
                        <td><?= $row['number'] ?></td>
                        <td><?= $row['amount'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-record">No Record found.....</div>
    <?php endif; ?>

</div>

<?php include('footer.php'); ?>