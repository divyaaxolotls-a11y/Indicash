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

if ($selected_market != '') {
    
    $query = "SELECT g.*, u.name 
              FROM games g
              LEFT JOIN users u ON g.user = u.mobile
              WHERE g.date='$current_date'";

    $market_clean = strtoupper(str_replace(" ", "_", trim($selected_market)));
     $query .= " AND g.bazar LIKE '{$market_clean}%'";

    if ($selected_session == 'Both') {
        $query .= " AND bazar LIKE '{$market_clean}%'";
    } else {
        // $session_suffix = strtoupper($selected_session);
        // $query .= " AND (game_type = '$session_suffix' AND bazar LIKE '%$market_clean')";
        $session_val = strtoupper($selected_session); // 'OPEN' or 'CLOSE'
        
        if ($session_val == 'OPEN') {
            // Rule: Show Open session bets, but HIDE Jodi
            // $query .= " AND (g.game_type = 'OPEN' OR g.bazar LIKE '%_OPEN') AND g.game != 'jodi'";
            //  $query .= " AND g.game_type = 'OPEN' AND g.game != 'jodi'";
            $query .= " AND g.game_type = 'OPEN' AND g.game NOT IN ('jodi', 'halfsangam', 'fullsangam')";
        } else {
            // Rule: Show Close session bets AND include ALL Jodi bets for this market
            // $query .= " AND ((g.game_type = 'CLOSE' OR g.bazar LIKE '%_CLOSE') OR g.game = 'jodi')";
            $query .= " AND (g.game_type = 'CLOSE' OR g.game IN ('jodi', 'halfsangam', 'fullsangam'))";
        }
    }

    if($u_search != '') { $query .= " AND user LIKE '%$u_search%'"; }
    if($search_num != '') { $query .= " AND number='$search_num'"; }
    if($selected_type != '') {
        // 1. Convert "Single Ank" -> "single", "Jodi" -> "jodi", etc.
        // to match your database 'game' column values
        $type_map = [
            'Single Ank'  => 'single',
            'Jodi'        => 'jodi',
            'Single Pana'  => 'singlepatti',
            'Double Pana'  => 'doublepatti',
            'Triple Pana'  => 'triplepatti',
            'Half Sangam'  => 'halfsangam',
            'Full Sangam'  => 'fullsangam'
        ];
        
        $db_value = $type_map[$selected_type] ?? strtolower($selected_type);
        
        // 2. Query the 'game' column instead of 'game_type'
        $query .= " AND game='$db_value'";
    }
    if($status_filter == 'win') { $query .= " AND status='1' AND is_loss='0' "; }
    elseif($status_filter == 'loss') { $query .= " AND is_loss='1' AND status='1'"; }
    elseif($status_filter == 'pending') { $query .= " AND status='0' AND is_loss='0'"; }
    elseif($status_filter == 'cancelled') { $query .= " AND status='2'"; }

    $query .= " ORDER BY sn DESC";
    // echo $query;
    $res = mysqli_query($con, $query);
    if($res) {
        while($row = mysqli_fetch_assoc($res)) { 
            $all_bets[] = $row; 
        }
    }
}
// print_r($all_bets);

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

   /* Table Layout for Card-style rows */
    .table-section { border: none; background: transparent; }
    .bet-table { 
        border-collapse: separate; 
        border-spacing: 0 10px; /* Space between rows */
        width: 100%;
    }
    
    /* Header Styling */
    .bet-table thead tr th {
        background-color: #ff9800;
        color: #000;
        font-weight: bold;
        border: none;
        padding: 12px 5px;
    }
    .bet-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .bet-table thead th:last-child { border-radius: 0 10px 10px 0; }
    
    /* Base Cell Styling */
    .bet-table tbody tr td {
        border: none !important;
        padding: 10px 5px;
        color: #fff; /* White text for cards */
        font-size: 13px;
        vertical-align: middle;
    }
    
    /* Rounded corners for the "Card" */
    .bet-table tbody tr td:first-child { border-radius: 10px 0 0 10px; }
    .bet-table tbody tr td:last-child { border-radius: 0 10px 10px 0; }
    
    /* Status Colors Based on Screenshot */
    .row-pending td   { background-color: #5c7a8c !important; } /* Bluish-Grey */
    .row-win td       { background-color: #28a745 !important; } /* Green */
    .row-loss td      { background-color: #e91e63 !important; } /* Pink/Red */
    .row-cancelled td { background-color: #6c757d !important; } /* Grey */
    
    /* Specific text formatting */
    .user-cell { text-align: left !important; padding-left: 15px !important; line-height: 1.4; }
    .market-cell { line-height: 1.3; }
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
    /* White vertical lines between columns */
    .bet-table tbody tr td:not(:last-child), 
    .bet-table thead tr th:not(:last-child) {
        border-right: 1px solid rgba(255, 255, 255, 0.5) !important;
    }
    
    /* Ensure table cells align perfectly with the lines */
    .bet-table thead th, .bet-table tbody td {
        border-top: none !important;
        border-bottom: none !important;
        border-left: none !important;
    }
    
    /* Optional: Make the header text black to pop against the white lines */
    .bet-table thead tr th {
        color: #000 !important;
    }
    
    /* Center all table headers and data cells */
    .bet-table thead tr th, 
    .bet-table tbody tr td {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 10px 4px !important; /* Balanced padding for centering */
    }
    
    /* Update User Cell (previously left-aligned) to be centered */
    .user-cell { 
        text-align: center !important; 
        padding-left: 4px !important; /* Removed the 15px left padding */
    }
    
    /* Ensure white lines are visible and cells stay aligned */
    .bet-table tbody tr td:not(:last-child), 
    .bet-table thead tr th:not(:last-child) {
        border-right: 1px solid rgba(255, 255, 255, 0.5) !important;
    }
    @media (max-width: 380px) {
        .bet-table thead tr th,
        .bet-table tbody tr td { font-size: 10px; padding: 6px 2px; }
        .status-btn { font-size: 11px; padding: 5px 10px; }
    }
     body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; margin: 0; }
    .main-wrapper { width: 100%; padding: 10px 8px; box-sizing: border-box; }

    /* Table Layout for Cards */
    .table-section { border: none; background: transparent; }
    .bet-table { 
        border-collapse: separate; 
        border-spacing: 0 12px; /* Gap between rows */
        width: 100%;
    }

    /* Orange Header */
    .bet-table thead tr th {
        background-color: #ff9800 !important;
        color: #000 !important;
        font-weight: bold;
        padding: 12px 5px;
        text-align: center;
        border: none !important;
    }
    .bet-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .bet-table thead th:last-child { border-radius: 0 10px 10px 0; }

    /* White vertical lines between columns */
    .bet-table tbody tr td:not(:last-child), 
    .bet-table thead tr th:not(:last-child) {
        border-right: 1px solid rgba(255, 255, 255, 0.3) !important;
    }

    /* Rounded corners for the cards */
    .bet-table tbody tr td:first-child { border-radius: 10px 0 0 10px; }
    .bet-table tbody tr td:last-child { border-radius: 0 10px 10px 0; }

    /* Text formatting for mobile look */
    .bet-table small { display: block; opacity: 0.8; font-size: 11px; margin-top: 2px; }
    .bold-text { font-weight: bold; font-size: 14px; }
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
            <!--<div>-->
            <!--    <label class="filter-label">Game List</label>-->
            <!--    <select name="game_name" class="app-input">-->
            <!--        <option value="">All Games</option>-->
            <!--        <?php-->
            <!--        $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");-->
            <!--        $seen = [];-->
            <!--        while($g = mysqli_fetch_assoc($g_q)){-->
            <!--            $clean = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']));-->
            <!--            if(!in_array($clean, $seen) && $clean != ""){-->
            <!--                echo "<option value='$clean' ".($selected_market==$clean?'selected':'').">$clean</option>";-->
            <!--                $seen[] = $clean;-->
            <!--            }-->
            <!--        }-->
            <!--        ?>-->
            <!--    </select>-->
            <!--</div>-->
            
            <div>
                <label class="filter-label">Game List</label>
                <select name="game_name" class="app-input">
                    <option value="">All Games</option>
                    <?php
                    // Fetching from the master game list table instead of the games table
                    $g_q = mysqli_query($con, "SELECT market FROM gametime_manual WHERE active=1 ORDER BY market ASC");
                    while($g = mysqli_fetch_assoc($g_q)){
                        $market_name = $g['market'];
                        $selected = ($selected_market == $market_name) ? 'selected' : '';
                        echo "<option value='$market_name' $selected>$market_name</option>";
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
                    <th class="w-market">Market<br>Type <br> Time</th>
                    <th class="w-status">Status</th>
                    <th class="w-no">No.</th>
                    <th class="w-amt">Amt</th>
                    <th class="w-win">Win Amt</th>
                </tr>
            </thead>
<tbody>
<?php if(!empty($all_bets)): ?>
    <?php 
    $i = 0; 
    $display_map = [
        'single' => 'SingleAnk',
        'jodi' => 'Jodi',
        'singlepatti' => 'SinglePana',
        'doublepatti' => 'DoublePana',
        'triplepatti' => 'TriplePana',
        'halfsangam' => 'HalfSangam',
        'fullsangam' => 'FullSangam'
    ];

    foreach($all_bets as $bet): 
        $i++;
         $pretty_game = $display_map[$bet['game']] ?? $bet['game'];
        $bet_time = isset($bet['created_at']) ? date('h:i:s A d-m-Y', $bet['created_at']) : date('h:i:s A d-m-Y');

        // --- IMPROVED DYNAMIC STATUS & COLOR LOGIC ---
        // We use intval and trim to prevent data type errors
        $db_status = intval(trim($bet['status']));
        $db_loss   = intval(trim($bet['is_loss']));

        $row_class = 'row-pending'; // Default background (Bluish-Grey)
        $lbl = 'pending';           // Default text

        if($db_status === 1 && $db_loss === 0){
            $lbl = 'win';
            $row_class = 'row-win'; // Green
        }
        elseif($db_status === 1 && $db_loss === 1){
            $lbl = 'loose';
            $row_class = 'row-loss'; // Pinkish-Red
        }
        elseif($db_status === 2){
            $lbl = 'cancelled';
            $row_class = 'row-cancelled'; // Grey
        }
    ?>
    <!--<tr>-->
        <!-- Column 1: Index-Name and Phone -->
    <!--    <td>-->
    <!--        <div class="bold-text"><?php echo $i; ?>- <?php echo htmlspecialchars($bet['name'] ?? 'User'); ?></div>-->
    <!--        <div style="font-size:12px;"><?php echo $bet['user']; ?></div>-->
    <!--    </td>-->
        
        <!-- Column 2: Market, Type-Session, and Date -->
    <!--    <td>-->
    <!--        <div><?php echo strtoupper(str_replace(['_OPEN', '_CLOSE'], '', $bet['bazar'])); ?>,</div>-->
    <!--        <div style="font-weight:600;"><?php echo $pretty_game; ?>-<?php echo strtolower($bet['game_type'] ?? 'open'); ?></div>-->
    <!--        <div style="font-size: 11px;"><?php echo $bet_time; ?></div>-->
    <!--    </td>-->

        <!-- Column 3: Status -->
    <!--    <td>pending</td>-->

        <!-- Column 4: No. -->
    <!--    <td class="bold-text"><?php echo $bet['number']; ?></td>-->

        <!-- Column 5: Amount -->
    <!--    <td class="bold-text"><?php echo $bet['amount']; ?></td>-->

        <!-- Column 6: Win Amt -->
    <!--    <td><?php echo ($bet['win_amount'] ?? '0'); ?></td>-->
    <!--</tr>-->
     <tr class="<?php echo $row_class; ?>">
        <!-- Column 1: Index-Name and Phone -->
        <td>
            <div class="bold-text"><?php echo $i; ?>- <?php echo htmlspecialchars($bet['name'] ?? 'User'); ?></div>
            <div style="font-size:12px;"><?php echo $bet['user']; ?></div>
        </td>
        
        <!-- Column 2: Market, Type-Session, and Date -->
        <td>
            <div><?php echo strtoupper(str_replace(['_OPEN', '_CLOSE'], '', $bet['bazar'])); ?>,</div>
            <div style="font-weight:600;"><?php echo $pretty_game; ?>-<?php echo strtolower($bet['game_type'] ?? 'open'); ?></div>
            <div style="font-size: 11px;"><?php echo $bet_time; ?></div>
        </td>

        <!-- Column 3: Status (MODIFIED: Now Dynamic) -->
        <td style="text-transform: lowercase;"><?php echo $lbl; ?></td>

        <!-- Column 4: No. -->
        <td class="bold-text"><?php echo $bet['number']; ?></td>

        <!-- Column 5: Amount -->
        <td class="bold-text"><?php echo $bet['amount']; ?></td>

        <!-- Column 6: Win Amt -->
        <td><?php echo ($bet['win_amount'] ?? '0'); ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" style="padding:60px; text-align:center; color:#999; background: white; border-radius: 15px;">
            No Data Found.
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>
    </div>

</div>

<!-- User Action Modal (Issue #19 Reference) -->
<div class="modal fade" id="userActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; color: #333;">
            <div class="modal-header">
                <h5 class="modal-title" id="userNameHeader" style="font-weight:bold;">Name : </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="#" id="modalProfileBtn" class="btn btn-success rounded-pill m-1" style="min-width:110px;">Profile</a>
                    <a href="#" id="modalTransBtn" class="btn btn-info rounded-pill m-1" style="background-color: #17a2b8; border:none; min-width:110px;">Transaction</a>
                    <a href="#" id="modalCallBtn" class="btn btn-danger rounded-pill m-1" style="min-width:110px;">Call</a>
                    <a href="#" id="modalWhatsappBtn" class="btn btn-warning rounded-pill m-1" style="background-color: #ffc107; color: black; min-width:110px;">WhatsApp</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="border-radius: 12px;" data-dismiss="modal">Close</button>
            </div>
        </div>
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

$(document).ready(function() {
    // Logic to open User Action Modal
    $('.open-user-modal').on('click', function() {
        var name = $(this).data('name');
        var mobile = $(this).data('mobile');

        // 1. Set the Title
        $('#userNameHeader').text('Name : ' + name);

        // 2. Update the Links (Ensuring correct filenames)
        $('#modalProfileBtn').attr('href', 'user-profile.php?userID=' + mobile);
        $('#modalTransBtn').attr('href', 'user-wallet-history.php?user_mobile=' + mobile);
        $('#modalCallBtn').attr('href', 'tel:' + mobile);
        
        // Ensure India country code for WhatsApp Business/Messenger
        let waMobile = mobile;
        if (waMobile.length === 10) waMobile = '91' + waMobile;
        $('#modalWhatsappBtn').attr('href', 'https://api.whatsapp.com/send?phone=' + waMobile);

        // 3. Show the Modal
        $('#userActionModal').modal('show');
    });
});
</script>
    <?php include('footer.php'); ?>

