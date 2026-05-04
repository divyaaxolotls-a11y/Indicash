<?php
include('header.php');

// 1. DATA LOGIC
$current_date = (isset($_GET['date']) && $_GET['date'] != '') 
    ? date('d/m/Y', strtotime($_GET['date'])) 
    : date('d/m/Y');

$selected_market = isset($_GET['game_name']) ? $_GET['game_name'] : '';
$selected_session = isset($_GET['session']) ? $_GET['session'] : 'Both';
$selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';
$search_num = isset($_GET['num_search']) ? $_GET['num_search'] : '';
$u_search = isset($_GET['user_search']) ? $_GET['user_search'] : '';
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$user_display_info = "";
if($u_search != "") {
    $u_info_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$u_search'");
    $u_info_data = mysqli_fetch_assoc($u_info_res);
    $user_display_info = ($u_info_data['name'] ?? 'User') . " ($u_search)";
}
$all_bets = [];

$query = "SELECT g.*, u.name 
          FROM games g
          LEFT JOIN users u ON g.user = u.mobile
          WHERE g.date='$current_date'";
if ($selected_market != '') {
    $market_clean = strtoupper(str_replace(" ", "_", trim($selected_market)));
    if ($selected_session == 'Both') {
        $query .= " AND bazar LIKE '{$market_clean}%'";
    } else {
        $session_suffix = "_" . strtoupper($selected_session);
        $query .= " AND bazar = '{$market_clean}{$session_suffix}'";
    }
}

if($u_search != '') { $query .= " AND user LIKE '%$u_search%'"; }
if($search_num != '') { $query .= " AND number='$search_num'"; }
if($selected_type != '') { $query .= " AND game_type='$selected_type'"; }

if($status_filter == 'win') { $query .= " AND status='1' AND is_loss='0' "; }
elseif($status_filter == 'loss') { $query .= " AND is_loss='1' AND status='1'"; }
elseif($status_filter == 'pending') { $query .= " AND status='0' AND is_loss='0'"; }
elseif($status_filter == 'cancelled') { $query .= " AND status='2'"; }

$query .= " ORDER BY sn DESC";
// echo $query;
$res = mysqli_query($con, $query);
if($res) {
    while($row = mysqli_fetch_assoc($res)) { $all_bets[] = $row; }
}

$game_types_list = ['Single Ank', 'Jodi', 'Single Pana', 'Double Pana', 'Triple Pana', 'Half Sangam', 'Full Sangam'];
$total_records = count($all_bets);
?>

<style>
    body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; margin: 0; }

    /* ── Wrapper ── */
    .content-wrapper { overflow-x: hidden; }

    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
        .container-fluid  { padding-left: 6px !important; padding-right: 6px !important; }
    }

    .main-wrapper { width: 100%; padding: 10px 8px; box-sizing: border-box; }

    /* ── Filter grid ── */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 8px;
    }

    @media (min-width: 992px) {
        .form-grid { grid-template-columns: repeat(4, 1fr); }
        .main-wrapper { padding: 14px 16px; }
    }

    .filter-label {
        font-size: 12px;
        color: #555;
        margin-bottom: 3px;
        display: block;
        font-weight: 600;
    }

    .app-input {
        width: 100%;
        border-radius: 20px;
        border: 1px solid #ccc;
        padding: 7px 12px;
        height: 38px;
        font-size: 13px;
        box-sizing: border-box;
        background: #fff;
    }

    /* ── Search row ── */
    .search-row {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
    }

    .search-row > div { flex: 1; min-width: 0; }

    /* ── Filter button: half width ── */
    .btn-filter {
        background-color: #03a9f4;
        color: white;
        border: none;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        padding: 7px 0;
        width: 50%;
        cursor: pointer;
        display: block;
        margin-bottom: 14px;
    }

    /* ── Status pill buttons ── */
    .status-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .status-btn {
        text-decoration: none;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 12px;
        color: white;
        font-weight: bold;
        white-space: nowrap;
    }

    .status-btn:hover { opacity: 0.88; color: white; text-decoration: none; }

    .st-all       { background: #03a9f4; }
    .st-win       { background: #28a745; }
    .st-loose     { background: #e91e63; }
    .st-pending   { background: #ffc107; color: #333; }
    .st-cancelled { background: #6c757d; }

    /* ── Total banner ── */
    .total-record-banner {
        background-color: #000;
        color: #ff9800;
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
        font-size: 14px;
    }

    /* ── Proper HTML table ── */
    .table-section {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ccc;
        width: 100%;
    }

    .bet-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .bet-table thead tr th {
        background-color: #ff9800;
        color: white;
        font-weight: bold;
        font-size: 11px;
        padding: 9px 4px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.4);
        vertical-align: middle;
        line-height: 1.4;
    }

    .bet-table tbody tr td {
        font-size: 11px;
        padding: 8px 4px;
        text-align: center;
        border: 1px solid #ddd;
        vertical-align: middle;
        word-break: break-word;
        background: white;
    }

    .bet-table tbody tr:nth-child(even) td { background: #fafafa; }

    /* Column widths */
    .bet-table .w-user   { width: 22%; }
    .bet-table .w-market { width: 22%; }
    .bet-table .w-status { width: 12%; }
    .bet-table .w-no     { width: 10%; }
    .bet-table .w-amt    { width: 12%; }
    .bet-table .w-win    { width: 12%; }

    /* Status colour labels */
    .lbl-win       { color: #28a745; font-weight: bold; }
    .lbl-loss      { color: #e91e63; font-weight: bold; }
    .lbl-pending   { color: #ff9800; font-weight: bold; }
    .lbl-cancelled { color: #6c757d; font-weight: bold; }
     .select2-container .select2-selection--single { height: 38px !important; border-radius: 20px !important; border: 1px solid #ccc !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    .row-win td {
    background-color: #28a745 !important;
    color: #fff !important;
}
    @media (max-width: 380px) {
        .bet-table thead tr th,
        .bet-table tbody tr td { font-size: 10px; padding: 6px 2px; }
        .status-btn { font-size: 11px; padding: 5px 10px; }
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const btn = document.querySelector('[data-widget="pushmenu"]');

    if(btn){
        btn.addEventListener("click", function(e){
            e.preventDefault();
            document.body.classList.toggle("sidebar-collapse");
        });
    }

});
</script>
<div class="main-wrapper">

    <!-- Filters: free on screen, no card box -->
    <form method="GET">
        <div class="form-grid">
            <div>
                <label class="filter-label">Game Type</label>
                <select name="game_type" class="app-input">
                    <option value="">All Types</option>
                    <?php foreach($game_types_list as $gt) {
                        echo "<option value='$gt' ".($selected_type==$gt?'selected':'').">$gt</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Game List</label>
                <select name="game_name" class="app-input">
                    <option value="">All Games</option>
                    <?php
                    $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");
                    $seen = [];
                    while($g = mysqli_fetch_assoc($g_q)){
                        $clean = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']));
                        if(!in_array($clean, $seen) && $clean != ""){
                            echo "<option value='$clean' ".($selected_market==$clean?'selected':'').">$clean</option>";
                            $seen[] = $clean;
                        }
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Date</label>
                <input type="date" name="date" class="app-input"
                       value="<?php echo (isset($_GET['date']) && $_GET['date'] != '') ? $_GET['date'] : date('Y-m-d'); ?>">
            </div>
            <div>
                <label class="filter-label">Session</label>
                <select name="session" class="app-input">
                    <option value="Both" <?php echo $selected_session=='Both'?'selected':''; ?>>Both</option>
                    <option value="Open"  <?php echo $selected_session=='Open'?'selected':''; ?>>Open</option>
                    <option value="Close" <?php echo $selected_session=='Close'?'selected':''; ?>>Close</option>
                </select>
            </div>
        </div>

         <div class="search-row">
            <div style="flex: 2;">
                <label class="filter-label">Search User (Name or Mobile)</label>
                <!-- UPDATED TO SEARCHABLE DROPDOWN -->
                <select name="user_search" id="user_search_ajax" class="app-input">
                    <?php if($u_search != ""): ?>
                        <option value="<?php echo $u_search; ?>" selected><?php echo $user_display_info; ?></option>
                    <?php else: ?>
                        <option value="">Search Mobile or Name...</option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Number</label>
                <input type="text" name="num_search" class="app-input" placeholder="Number" value="<?php echo $search_num; ?>">
            </div>
        </div>

        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <!-- Status filter pills -->
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

    <!-- Total -->
    <div class="total-record-banner">Records Found: <?php echo $total_records; ?></div>

    <!-- Table: proper HTML table for full column borders -->
    <div class="table-section">
        <table class="bet-table">
            <thead>
                <tr>
                    <th class="w-user">Username<br>Phone</th>
                    <th class="w-market">Market<br>Type</th>
                    <th class="w-status">Status</th>
                    <th class="w-no">No.</th>
                    <th class="w-amt">Amt</th>
                    <th class="w-win">Win Amt</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!empty($all_bets)): ?>
                <?php foreach($all_bets as $bet): ?>
                    <?php
                        // if($bet['status'] == 1)          { $lbl = 'Win';       $cls = 'lbl-win'; }
                        // elseif($bet['status'] == 2)       { $lbl = 'Cancelled'; $cls = 'lbl-cancelled'; }
                        // elseif($bet['is_loss'] == 1)      { $lbl = 'Loss';      $cls = 'lbl-loss'; }
                        // else                              { $lbl = 'Pending';   $cls = 'lbl-pending'; }
                        $row_class = '';

                        if($bet['status'] == 1 && $bet['is_loss'] == 0){
                            $lbl = 'Win';
                            $cls = 'lbl-win';
                            $row_class = 'row-win'; // 👈 NEW
                        }
                        elseif($bet['status'] == 2){
                            $lbl = 'Cancelled';
                            $cls = 'lbl-cancelled';
                        }
                        elseif($bet['is_loss'] == 1){
                            $lbl = 'Loss';
                            $cls = 'lbl-loss';
                        }
                        else{
                            $lbl = 'Pending';
                            $cls = 'lbl-pending';
                        }
                    ?>
                    <!--<tr>-->
                    <tr class="<?php echo $row_class; ?>">
                        <td>
                         <div style="font-weight:bold;">
                         <?php echo !empty($bet['name']) ? $bet['name'] : '-'; ?>
                         </div>
                         <small style="color:#666;">
                         <?php echo $bet['user']; ?>
                        </small>
                     </td>
                        
                        <td>
                            <div><?php echo $bet['bazar']; ?></div>
                            <small><?php echo $bet['game_type']; ?></small>
                        </td>
                        <td class="<?php echo $cls; ?>"><?php echo $lbl; ?></td>
                        <td><?php echo $bet['number']; ?></td>
                        <td><?php echo $bet['amount']; ?></td>
                        <td><?php echo ($bet['win_amount'] ?? '0'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding:40px; text-align:center; color:#999;">
                        No Data Found. Try changing the Date or Market.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize AJAX Searchable Dropdown
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

    const btn = document.querySelector('[data-widget="pushmenu"]');
    if(btn){
        btn.addEventListener("click", function(e){
            e.preventDefault();
            document.body.classList.toggle("sidebar-collapse");
        });
    }
});
</script>
