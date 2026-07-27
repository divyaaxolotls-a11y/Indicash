<?php 
include('header.php'); 

// 1. DATA LOGIC
// $current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 
// $db_date = date('d/m/Y', strtotime($current_date));
// $display_date = date('d-M-Y', strtotime($current_date));

// $selected_market = isset($_GET['game_name']) ? $_GET['game_name'] : '';
// $selected_session = isset($_GET['session']) ? $_GET['session'] : 'Open';
// $selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';

// $total_amt = 0;
// $total_count = 0;
// $all_bets = [];

// $query = "SELECT * FROM games WHERE date='$db_date'";
// if ($selected_market != '') {
//     // With this:
//   $db_bazar = str_replace(" ", "_", strtoupper(trim($selected_market)));
//   $query .= " AND bazar='$db_bazar'";
// }
// if(isset($_GET['user_search']) && $_GET['user_search'] != '') {
//     $u_search = mysqli_real_escape_string($con, $_GET['user_search']);
//     $query .= " AND user='$u_search'";
// }
// $query .= " ORDER BY sn DESC";
// $res = mysqli_query($con, $query);

// 1. DATA LOGIC
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 
$db_date = date('d/m/Y', strtotime($current_date));
$display_date = date('d-M-Y', strtotime($current_date));

// FIX 1: Use 'market' to match your URL
$selected_market = isset($_GET['market']) ? $_GET['market'] : (isset($_GET['game_name']) ? $_GET['game_name'] : '');
$selected_session = isset($_GET['session']) ? strtoupper($_GET['session']) : 'OPEN';
$selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';

$total_amt = 0;
$total_count = 0;
$all_bets = [];

$query = "SELECT * FROM games WHERE date='$db_date'";

if ($selected_market != '') {
    $market_raw = mysqli_real_escape_string($con, trim($selected_market));
    
    // Create versions with both space and underscore to be safe
    $m_space = strtoupper(str_replace("_", " ", $market_raw));
    $m_under = strtoupper(str_replace(" ", "_", $market_raw));
    
    // FIX 2 & 4: Search for the market name OR market name + session suffix
    $query .= " AND (
        bazar = '$m_space' OR 
        bazar = '$m_under' OR 
        bazar = '{$m_under}_{$selected_session}' OR 
        bazar = '{$m_space}_{$selected_session}'
    )";
}

if(isset($_GET['user_search']) && $_GET['user_search'] != '') {
    $u_search = mysqli_real_escape_string($con, $_GET['user_search']);
    $query .= " AND user='$u_search'";
}

$query .= " ORDER BY sn DESC";
        // print_r($query);

$res = mysqli_query($con, $query);

// while($d = mysqli_fetch_assoc($check2)){
//     echo $d['bazar'] . "<br>";
// }
// echo "</pre>";
// if($res) {
//     while($row = mysqli_fetch_assoc($res)) {
//         // print_r($row);
//         $all_bets[] = $row;
//         $total_amt += (int)$row['amount'];
//         $total_count++;
//     }
// }

if($res) {
    while($row = mysqli_fetch_assoc($res)) {
        // Derive clean values for grouping
        $oc = (strpos(strtoupper($row['bazar']), '_CLOSE') !== false) ? 'close' : 'open';
        $market_name = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $row['bazar']));
        $g_type = $row['game'];
        $num = $row['number'];

        // Create unique key: Market|Type|Session|Number
        $group_key = $market_name . "|" . $g_type . "|" . $oc . "|" . $num;

        if (isset($all_bets[$group_key])) {
            // Sum the amount if already exists
            $all_bets[$group_key]['amount'] += (int)$row['amount'];
        } else {
            // Initialize new row
            $all_bets[$group_key] = [
                'market'    => $market_name,
                'game_type' => $g_type,
                'session'   => $oc,
                'number'    => $num,
                'amount'    => (int)$row['amount'],
                'raw_game'  => $row['game'] // For mapping filter
            ];
        }
        
        $total_amt += (int)$row['amount'];
        $total_count++;
    }
    
     uasort($all_bets, function($a, $b) {
        return (int)$a['number'] <=> (int)$b['number'];
    });
}

$game_types_list = ['Single Ank', 'Jodi', 'Single Pana', 'Double Pana', 'Triple Pana', 'Half Sangam', 'Full Sangam'];
$db_map = [
    'Single Ank'  => 'single',
    'Jodi'        => 'jodi',
    'Single Pana' => 'singlepatti',
    'Double Pana' => 'doublepatti',
    'Triple Pana' => 'triplepatti',
    'Half Sangam' => 'halfsangam',
    'Full Sangam' => 'fullsangam'
];

// ADD THIS AT THE BOTTOM OF THE TOP PHP SECTION
$user_display_info = "";
if(isset($_GET['user_search']) && $_GET['user_search'] != '') {
    $safe_u = mysqli_real_escape_string($con, $_GET['user_search']);
    $u_info = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM users WHERE mobile='$safe_u'"));
    $user_display_info = ($u_info['name'] ?? 'User') . " ($safe_u)";
}
?>

<style>
    body { background-color: #fff; font-family: 'Segoe UI', sans-serif; color: #333; }

    .content-wrapper { overflow-x: hidden; }

    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
        .container-fluid  { padding-left: 6px !important; padding-right: 6px !important; }
    }

    .main-wrapper {
        width: 100%;
        padding: 12px 10px;
        box-sizing: border-box;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    @media (min-width: 992px) {
        .form-grid { grid-template-columns: repeat(4, 1fr); }
        .main-wrapper { padding: 16px 20px; }
    }

    .filter-label {
        font-size: 12px;
        color: #555;
        margin-bottom: 3px;
        display: block;
        font-weight: 500;
    }

    .app-input {
        width: 100%;
        border-radius: 20px;
        border: 1px solid #ccc;
        padding: 7px 12px;
        font-size: 13px;
        height: 38px;
        box-sizing: border-box;
        background: #fff;
    }

    .search-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        margin-top: 6px;
    }

    .search-container { flex: 1; min-width: 0; }

    .btn-filter {
        background-color: #03a9f4;
        color: white;
        border: none;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        height: 38px;
        padding: 0 20px;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .orange-divider {
        background-color: #ff9800;
        border-radius: 10px;
        margin: 14px 0 10px;
        padding: 10px 16px;
        color: white;
        font-weight: bold;
        font-size: 14px;
        letter-spacing: 0.5px;
        text-align: center;
        word-break: break-word;
    }

    .stats-container {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
        margin: 10px 0;
    }

    .stat-box {
        background: #f8f9fa;
        padding: 7px 16px;
        border-radius: 20px;
        border: 1px solid #ddd;
        font-weight: bold;
        font-size: 13px;
    }

    .copy-btn-main {
        background-color: #03a9f4;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 10px 40px;
        font-size: 14px;
        font-weight: 600;
        display: block;
        margin: 0 auto 10px;
        cursor: pointer;
    }

    .copy-btn-sub {
        background-color: #03a9f4;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 18px;
        font-size: 13px;
        margin: 14px 0 6px;
        display: inline-block;
        cursor: pointer;
    }

    .table-container {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
    }

    .app-table-header {
        background-color: #ff9800;
        display: flex;
        color: white;
        font-weight: bold;
        padding: 9px 0;
        text-align: center;
        font-size: 12px;
    }

    .header-col {
        flex: 1;
        border-right: 1px solid rgba(255,255,255,0.3);
        padding: 0 2px;
    }

    .header-col:last-child { border-right: none; }

    .data-row {
        display: flex;
        text-align: center;
        padding: 9px 0;
        border-bottom: 1px solid #eee;
        background: white;
        font-size: 13px;
    }

    .data-row:last-child { border-bottom: none; }

    .col-item {
        flex: 1;
        padding: 0 2px;
        word-break: break-word;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ccc !important;
        border-radius: 20px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 12px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow  { height: 36px !important; }

    @media (max-width: 480px) {
        .app-table-header { font-size: 11px; }
        .data-row         { font-size: 11px; }
        .copy-btn-main    { padding: 9px 28px; font-size: 13px; }
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
    <form method="GET" id="filterForm">

        <div class="form-grid">
            <div>
                <label class="filter-label">Game Type</label>
                <select name="game_type" class="app-input">
                    <option value="">Game Type</option>
                    <?php foreach($game_types_list as $gt) {
                        $s = ($selected_type == $gt) ? 'selected' : '';
                        echo "<option value='$gt' $s>$gt</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Game List</label>
                <select name="game_name" id="game_list_select" class="app-input">
                    <option value="">All Game</option>
                    <?php
                    $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");
                    $seen_markets = [];
                    while($g = mysqli_fetch_assoc($g_q)){
                        $clean = str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']);
                        $clean = trim($clean);
                        if(!in_array($clean, $seen_markets) && $clean != ""){
                            $seen_markets[] = $clean;
                            $sel = (strtoupper($selected_market) == strtoupper($clean)) ? 'selected' : '';
                            echo '<option value="'.$clean.'" '.$sel.'>'.$clean.'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Date</label>
                <input type="date" name="date" value="<?php echo $current_date; ?>" class="app-input" />
            </div>
            <div>
                <label class="filter-label">Open / Close</label>
                <select name="session" class="app-input">
                    <option value="Open"  <?php if($selected_session == 'Open')  echo 'selected'; ?>>Open</option>
                    <option value="Close" <?php if($selected_session == 'Close') echo 'selected'; ?>>Close</option>
                </select>
            </div>
        </div>

        <div class="search-filter-row">
            <div class="search-container">
                <label class="filter-label">Search User (Name or Mobile)</label>
                <select name="user_search" id="user_search_ajax" class="app-input">
                    <?php if(isset($_GET['user_search']) && $_GET['user_search'] != ''): ?>
                        <option value="<?php echo htmlspecialchars($_GET['user_search']); ?>" selected>
                            <?php echo htmlspecialchars($user_display_info); ?>
                        </option>
                    <?php else: ?>
                        <option value="">Type Mobile or Name...</option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>

    </form>

    <!-- Market + Date bar -->
    <div class="orange-divider">
        <?php
        if($selected_market != '') {
            echo strtoupper($selected_market) . " (" . strtoupper($selected_session) . ") | " . $display_date;
        } else {
            echo $display_date;
        }
        ?>
    </div>

    <!-- Copy all -->
    <button class="copy-btn-main">Copy All Bids</button>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-box">Total Bids: <?php echo $total_count; ?></div>
        <div class="stat-box">Total Amount: <?php echo number_format($total_amt); ?></div>
    </div>

    <!-- Per-type tables -->
    <?php foreach ($game_types_list as $type_label): ?>
        <?php if($selected_type == '' || $selected_type == $type_label): ?>

            <button class="copy-btn-sub">Copy <?php echo $type_label; ?></button>

            <div class="table-container">
                <div class="app-table-header">
                    <div class="header-col">Game</div>
                    <div class="header-col">Type</div>
                    <div class="header-col">Open Close</div>
                    <div class="header-col">No.</div>
                    <div class="header-col">Bet</div>
                </div>

                <?php
                $found = false;
                // foreach($all_bets as $bet) {
                //   if(strtolower($bet['game_type']) == strtolower($db_map[$type_label]) || strtolower($bet['game']) == strtolower($db_map[$type_label])) {
                //         $found = true;
                //         // Derive open/close from bazar field
                //         $oc = (strpos(strtoupper($bet['bazar']), '_CLOSE') !== false) ? 'close' : 'open';
                //         // Derive market name from bazar field
                //         $market_name = str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $bet['bazar']);
                //         $market_name = trim($market_name);
                //         echo '<div class="data-row">';
                //         echo '<div class="col-item">'.$market_name.'</div>';
                //         echo '<div class="col-item">'.$bet['game_type'].'</div>';
                //         echo '<div class="col-item">'.$oc.'</div>';
                //         echo '<div class="col-item">'.$bet['number'].'</div>';
                //         echo '<div class="col-item">'.$bet['amount'].'</div>';
                //         echo '</div>';
                //     }
                // }
                foreach($all_bets as $bet) {
                        // Check if current bet matches the table type (Single, Jodi, etc.)
                        if(strtolower($bet['game_type']) == strtolower($db_map[$type_label]) || strtolower($bet['raw_game']) == strtolower($db_map[$type_label])) {
                            $found = true;
                            echo '<div class="data-row">';
                            echo '<div class="col-item">'.$bet['market'].'</div>';
                            echo '<div class="col-item">'.$bet['game_type'].'</div>';
                            echo '<div class="col-item">'.strtoupper($bet['session']).'</div>';
                            echo '<div class="col-item" style="font-weight:bold; color:blue;">'.$bet['number'].'</div>';
                            echo '<div class="col-item" style="font-weight:bold; color:green;">'.$bet['amount'].'</div>';
                            echo '</div>';
                        }
                    }
                if(!$found): ?>
                    <div class="data-row" style="justify-content:center; color:#999;">No data found</div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#user_search_ajax').select2({
        width: '100%',
        placeholder: "Search for a user...",
        minimumInputLength: 3, // Starts searching after 3 characters
        ajax: {
            url: 'user-search-live.php', // Using your existing live search file
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });
});

$(document).ready(function() {
    // Function to copy text to clipboard
    function copyToClipboard(text) {
        var $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
        alert("Bids copied to clipboard!");
    }

    // Copy All Bids
    $('.copy-btn-main').click(function() {
        let text = "";
        $('.data-row').each(function() {
            if($(this).text().trim() !== "No data found") {
                text += $(this).text().replace(/\s+/g, ' ').trim() + "\n";
            }
        });
        copyToClipboard(text);
    });

    // Copy Specific Type
    $('.copy-btn-sub').click(function() {
        let text = "";
        let table = $(this).next('.table-container');
        table.find('.data-row').each(function() {
            if($(this).text().trim() !== "No data found") {
                text += $(this).text().replace(/\s+/g, ' ').trim() + "\n";
            }
        });
        copyToClipboard(text);
    });
});
</script>