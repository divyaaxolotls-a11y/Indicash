<?php
include('header.php');

// 1. DATA LOGIC
$current_date = (isset($_GET['date']) && $_GET['date'] != '') 
    ? date('d/m/Y', strtotime($_GET['date'])) 
    : date('d/m/Y');

$selected_market = isset($_GET['game_name']) ? $_GET['game_name'] : '';
$selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';
$search_num = isset($_GET['num_search']) ? $_GET['num_search'] : '';
$u_search = isset($_GET['user_search']) ? mysqli_real_escape_string($con, $_GET['user_search']) : '';
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// User display info for Select2 sticky value
$user_display_info = "";
if($u_search != "") {
    $u_info_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$u_search'");
    $u_info_data = mysqli_fetch_assoc($u_info_res);
    $user_display_info = ($u_info_data['name'] ?? 'User') . " ($u_search)";
}

$all_records = [];

// Base Query
$query = "SELECT sg.*, u.name as username 
          FROM starline_games sg
          LEFT JOIN users u ON sg.user = u.mobile
          WHERE sg.date='$current_date'";

// Apply Filters
if ($selected_market != '') {
    $query .= " AND sg.bazar = '".mysqli_real_escape_string($con, $selected_market)."'";
}
if ($u_search != '') {
    $query .= " AND sg.user = '$u_search'";
}
if ($search_num != '') {
    $query .= " AND sg.number = '$search_num'";
}
if ($selected_type != '') {
    $query .= " AND sg.game = '".mysqli_real_escape_string($con, $selected_type)."'";
}

// ✅ Status Logic from Pill Buttons
if($status_filter == 'win') { $query .= " AND sg.status='1'"; }
elseif($status_filter == 'loss') { $query .= " AND sg.is_loss='1'"; }
elseif($status_filter == 'pending') { $query .= " AND sg.status='0' AND sg.is_loss='0'"; }
elseif($status_filter == 'cancelled') { $query .= " AND sg.status='2'"; }

$query .= " ORDER BY sg.sn DESC";

$res = mysqli_query($con, $query);
if($res) {
    while($row = mysqli_fetch_assoc($res)) { $all_records[] = $row; }
}

$game_types_list = ['single' => 'Single Ank', 'singlepatti' => 'Single Pana', 'doublepatti' => 'Double Pana', 'triplepatti' => 'Triple Pana'];
$total_records = count($all_records);
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; margin: 0; }
    .main-wrapper { width: 100%; padding: 10px 8px; box-sizing: border-box; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 8px; }
    @media (min-width: 992px) { .form-grid { grid-template-columns: repeat(3, 1fr); } }
    .filter-label { font-size: 12px; color: #555; margin-bottom: 3px; display: block; font-weight: 600; }
    .app-input { width: 100%; border-radius: 20px; border: 1px solid #ccc; padding: 7px 12px; height: 38px; font-size: 13px; background: #fff; box-sizing: border-box; }
    
    /* Select2 UI */
    .select2-container .select2-selection--single { height: 38px !important; border-radius: 20px !important; border: 1px solid #ccc !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }

    .search-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .search-row > div { flex: 1; min-width: 0; }
    .btn-filter { background-color: #03a9f4; color: white; border: none; border-radius: 20px; font-weight: 600; font-size: 13px; padding: 10px 30px; cursor: pointer; display: inline-block; margin-bottom: 14px; }
    
    /* Status Pills */
    .status-container { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .status-btn { text-decoration: none; border-radius: 20px; padding: 6px 14px; font-size: 12px; color: white; font-weight: bold; white-space: nowrap; transition: 0.2s; }
    .status-btn:hover { opacity: 0.8; color: white; }
    .st-all { background: #03a9f4; }
    .st-win { background: #28a745; }
    .st-loose { background: #e91e63; }
    .st-pending { background: #ffc107; color: #333; }
    .st-cancelled { background: #6c757d; }

    .total-record-banner { background-color: #000; color: #ff9800; padding: 9px 14px; border-radius: 8px; font-weight: bold; text-align: center; margin-bottom: 10px; font-size: 14px; }
    
    /* Table UI */
    .table-section { border-radius: 8px; overflow: hidden; border: 1px solid #ccc; width: 100%; }
    .bet-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .bet-table thead tr th { background-color: #ff9800; color: white; font-weight: bold; font-size: 11px; padding: 10px 4px; text-align: center; border: 1px solid rgba(255,255,255,0.4); vertical-align: middle; }
    .bet-table tbody tr td { font-size: 12px; padding: 10px 4px; text-align: center; border: 1px solid #ddd; background: white; vertical-align: middle; word-break: break-word; }
    .bet-table tbody tr:nth-child(even) td { background: #fafafa; }
    
    .lbl-win { color: #28a745; font-weight: bold; }
    .lbl-loss { color: #e91e63; font-weight: bold; }
    .lbl-pending { color: #ff9800; font-weight: bold; }
    .lbl-cancelled { color: #6c757d; font-weight: bold; }
</style>

<div class="main-wrapper">
    <h4 class="mb-3 font-weight-bold">Starline Bid History</h4>

    <form method="GET">
        <div class="form-grid">
            <div>
                <label class="filter-label">Market</label>
                <select name="game_name" class="app-input">
                    <option value="">All Markets</option>
                    <?php
                    $m_q = mysqli_query($con, "SELECT name FROM starline_markets WHERE active=1 ORDER BY name ASC");
                    while($m = mysqli_fetch_assoc($m_q)){
                        echo "<option value='".$m['name']."' ".($selected_market==$m['name']?'selected':'').">".$m['name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Game Type</label>
                <select name="game_type" class="app-input">
                    <option value="">All Types</option>
                    <?php foreach($game_types_list as $key => $val) {
                        echo "<option value='$key' ".($selected_type==$key?'selected':'').">$val</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Date</label>
                <input type="date" name="date" class="app-input"
                       value="<?php echo (isset($_GET['date']) && $_GET['date'] != '') ? $_GET['date'] : date('Y-m-d'); ?>">
            </div>
        </div>

        <div class="search-row">
            <div style="flex: 2;">
                <label class="filter-label">Search User</label>
                <select name="user_search" id="user_search_ajax" class="app-input">
                    <?php if($u_search != ""): ?>
                        <option value="<?php echo $u_search; ?>" selected><?php echo $user_display_info; ?></option>
                    <?php else: ?>
                        <option value="">Search Mobile or Name...</option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Digit</label>
                <input type="text" name="num_search" class="app-input" placeholder="Number" value="<?php echo $search_num; ?>">
            </div>
        </div>

        <button type="submit" class="btn-filter">Apply Filter</button>
    </form>

    <!-- ✅ STATUS FILTER PILLS ADDED HERE -->
    <div class="status-container">
        <?php
        $current_params = $_GET;
        unset($current_params['filter']);
        $base = "?" . http_build_query($current_params);
        ?>
        <a href="<?php echo $base; ?>&filter=all"       class="status-btn st-all">All</a>
        <a href="<?php echo $base; ?>&filter=win"       class="status-btn st-win">Win</a>
        <a href="<?php echo $base; ?>&filter=loss"      class="status-btn st-loose">Loss</a>
        <a href="<?php echo $base; ?>&filter=pending"   class="status-btn st-pending">Pending</a>
        <a href="<?php echo $base; ?>&filter=cancelled" class="status-btn st-cancelled">Cancelled</a>
    </div>

    <div class="total-record-banner">Records Found: <?php echo $total_records; ?> (<?php echo strtoupper($status_filter); ?>)</div>

    <div class="table-section">
        <table class="bet-table">
            <thead>
                <tr>
                    <th style="width:25%;">User</th>
                    <th style="width:25%;">Market / Type</th>
                    <th style="width:12%;">Digit</th>
                    <th style="width:18%;">Bid / Win</th>
                    <th style="width:20%;">Status / Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!empty($all_records)): ?>
                <?php foreach($all_records as $row): ?>
                    <?php
                        if($row['status'] == 1)          { $lbl = 'Win';       $cls = 'lbl-win'; }
                        elseif($row['status'] == 2)       { $lbl = 'Cancelled'; $cls = 'lbl-cancelled'; }
                        elseif($row['is_loss'] == 1)      { $lbl = 'Loss';      $cls = 'lbl-loss'; }
                        else                              { $lbl = 'Pending';   $cls = 'lbl-pending'; }
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:bold;"><?php echo !empty($row['username']) ? $row['username'] : '-'; ?></div>
                            <small style="color:#666;"><?php echo $row['user']; ?></small>
                        </td>
                        <td>
                            <div style="font-weight:bold;"><?php echo $row['bazar']; ?></div>
                            <small><?php echo strtoupper($row['game']); ?></small>
                        </td>
                        <td><strong><?php echo $row['number']; ?></strong></td>
                        <td>
                            Bid: <?php echo $row['amount']; ?><br>
                            <span class="lbl-win">Win: <?php echo $row['win_amount']; ?></span>
                        </td>
                        <td>
                            <div class="<?php echo $cls; ?>"><?php echo $lbl; ?></div>
                            <small><?php echo $row['date']; ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding:40px; text-align:center; color:#999;">No records found for the selected filters.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#user_search_ajax').select2({
        width: '100%',
        placeholder: "Search Mobile or Name...",
        minimumInputLength: 3,
        ajax: {
            url: 'user-search-live.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });
});
</script>

<?php include('footer.php'); ?>