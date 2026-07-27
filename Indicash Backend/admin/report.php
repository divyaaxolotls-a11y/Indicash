<?php 
include('header.php'); 

// 1. CONFIGURATION ARRAYS
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

// 2. DATA LOGIC
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 
$db_date = date('d/m/Y', strtotime($current_date));
$display_date = date('d-M-Y', strtotime($current_date));

$selected_market = isset($_GET['game_name']) ? $_GET['game_name'] : '';
$selected_session = isset($_GET['session']) ? strtoupper($_GET['session']) : 'OPEN';
$selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';

$user_display_info = "";
if(isset($_GET['user_search']) && $_GET['user_search'] != '') {
    $safe_u = mysqli_real_escape_string($con, $_GET['user_search']);
    $u_info = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM users WHERE mobile='$safe_u'"));
    $user_display_info = ($u_info['name'] ?? 'User') . " ($safe_u)";
}

$total_amt = 0;
$total_count = 0;
$all_bets = [];

// ONLY RUN QUERY IF MARKET IS SELECTED
if ($selected_market != '') {
    $query = "SELECT * FROM games WHERE date='$db_date'";
    
    // $market_raw = mysqli_real_escape_string($con, trim($selected_market));
    // $m_space = strtoupper(str_replace("_", " ", $market_raw));
    // $m_under = strtoupper(str_replace(" ", "_", $market_raw));
    
    // $query .= " AND (
    //     bazar = '$m_space' OR 
    //     bazar = '$m_under' OR 
    //     bazar = '{$m_under}_{$selected_session}' OR 
    //     bazar = '{$m_space}_{$selected_session}'
    // )";

    // if(isset($_GET['user_search']) && $_GET['user_search'] != '') {
    //     $u_search = mysqli_real_escape_string($con, $_GET['user_search']);
    //     $query .= " AND user='$u_search'";
    // }
    
    $market_db = strtoupper(str_replace(" ", "_", trim($selected_market)));

    // SESSION FILTERING LOGIC
    // if ($selected_session == 'OPEN') {
    //     // Only show Open session bets, skip Jodi
    //     $query .= " AND bazar = '{$market_db}_OPEN' AND game != 'jodi'";
    // } else {
    //     // Show Close session bets AND the Jodi bets for this market
    //     $query .= " AND (bazar = '{$market_db}_CLOSE' OR (bazar = '$market_db' AND game = 'jodi'))";
    // //   $query .= " AND ((g.game_type = 'CLOSE' OR g.bazar LIKE '%_CLOSE') OR g.game = 'jodi') AND g.game != 'halfsangam'";
    // }
    
    $query .= " AND (bazar = '$market_db' OR bazar LIKE '{$market_db}_%')";

    // 2. Filter by Session using the 'game_type' column directly
    if ($selected_session != 'Both') {
        $session_val = strtoupper($selected_session); // "OPEN" or "CLOSE"
        $query .= " AND game_type = '$session_val'";
    }

    $query .= " ORDER BY sn DESC";
    $res = mysqli_query($con, $query);

    if($res) {
        while($row = mysqli_fetch_assoc($res)) {
            // $oc = (strpos(strtoupper($row['bazar']), '_CLOSE') !== false) ? 'close' : 'open';
            $oc = strtolower($row['game_type'] ?? 'open');

            $market_name = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $row['bazar']));
            $g_type = $row['game'];
            $num = $row['number'];

            $group_key = $market_name . "|" . $g_type . "|" . $oc . "|" . $num;

            if (isset($all_bets[$group_key])) {
                $all_bets[$group_key]['amount'] += (int)$row['amount'];
            } else {
                $all_bets[$group_key] = [
                    'market'    => $market_name,
                    'game_type' => $g_type,
                    'session'   => $oc,
                    'number'    => $num,
                    'amount'    => (int)$row['amount'],
                    'raw_game'  => $row['game']
                ];
            }
            $total_amt += (int)$row['amount'];
            $total_count++;
        }
        uasort($all_bets, function($a, $b) {
            return (int)$a['number'] <=> (int)$b['number'];
        });
    }
}
?>

<style>
    body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }
    .main-wrapper { width: 100%; padding: 12px 10px; box-sizing: border-box; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 10px; }
    @media (min-width: 992px) { .form-grid { grid-template-columns: repeat(4, 1fr); } }
    .filter-label { font-size: 12px; color: #555; margin-bottom: 3px; display: block; font-weight: 500; }
    .app-input { width: 100%; border-radius: 20px; border: 1px solid #ccc; padding: 7px 12px; font-size: 13px; height: 38px; box-sizing: border-box; background: #fff; }
    .search-filter-row { display: flex; align-items: flex-end; gap: 8px; margin-top: 6px; }
    .search-container { flex: 1; min-width: 0; }
    .btn-filter { background-color: #03a9f4; color: white; border: none; border-radius: 20px; font-weight: 600; font-size: 14px; height: 38px; padding: 0 20px; cursor: pointer; white-space: nowrap; }
    .orange-divider { background-color: #ff9800; border-radius: 10px; margin: 14px 0 10px; padding: 10px 16px; color: white; font-weight: bold; font-size: 14px; text-align: center; }
    .stats-container { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin: 10px 0; }
    .stat-box { background: #fff; padding: 7px 16px; border-radius: 20px; border: 1px solid #ddd; font-weight: bold; font-size: 13px; }
    .copy-btn-main { background-color: #03a9f4; color: white; border: none; border-radius: 25px; padding: 10px 40px; font-size: 14px; font-weight: 600; display: block; margin: 0 auto 10px; cursor: pointer; }
    .copy-btn-sub { background-color: #03a9f4; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 13px; margin: 14px 0 6px; display: inline-block; cursor: pointer; }
    .table-container { margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; background: #fff;}
    .app-table-header { background-color: #ff9800; display: flex; color: white; font-weight: bold; padding: 9px 0; text-align: center; font-size: 12px; }
    .header-col { flex: 1; border-right: 1px solid rgba(255,255,255,0.3); }
    .header-col:last-child { border-right: none; }
    .data-row { display: flex; text-align: center; padding: 9px 0; border-bottom: 1px solid #eee; font-size: 13px; }
    .col-item { flex: 1; padding: 0 2px; }
    /* Center all table text */
.app-table-header, .data-row {
    display: flex;
    justify-content: center;
    align-items: center;
}

.header-col, .col-item {
    flex: 1;
    text-align: center !important;
    vertical-align: middle;
}

/* UI Formatting for the result list */
.data-row {
    border-bottom: 1px solid #eee;
    padding: 12px 0;
}
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ccc !important; border-radius: 20px !important; }
</style>

<div class="content-wrapper">
    <div class="main-wrapper">
        <form method="GET" id="filterForm">
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
                <!--        <option value="">Select Game</option>-->
                <!--        <?php-->
                <!--        $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");-->
                <!--        $seen_markets = [];-->
                <!--        while($g = mysqli_fetch_assoc($g_q)){-->
                <!--            $clean = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']));-->
                <!--            if(!in_array($clean, $seen_markets) && $clean != ""){-->
                <!--                $seen_markets[] = $clean;-->
                <!--                echo "<option value='$clean' ".($selected_market==$clean?'selected':'').">$clean</option>";-->
                <!--            }-->
                <!--        }-->
                <!--        ?>-->
                <!--    </select>-->
                <!--</div>-->
                <div>
                    <label class="filter-label">Game List</label>
                    <select name="game_name" class="app-input">
                        <option value="">Select Game</option>
                        <?php
                        // MODIFIED: Fetching from the master game list table
                        $g_q = mysqli_query($con, "SELECT market FROM gametime_manual WHERE active=1 ORDER BY market ASC");
                        while($g = mysqli_fetch_assoc($g_q)){
                            $m_name = $g['market'];
                            $is_sel = ($selected_market == $m_name) ? 'selected' : '';
                            echo "<option value='$m_name' $is_sel>$m_name</option>";
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
                        <option value="Open"  <?php echo ($selected_session == 'OPEN')?'selected':''; ?>>Open</option>
                        <option value="Close" <?php echo ($selected_session == 'CLOSE')?'selected':''; ?>>Close</option>
                    </select>
                </div>
            </div>

            <div class="search-filter-row">
                <div class="search-container">
                    <label class="filter-label">Search User</label>
                    <select name="user_search" id="user_search_ajax" class="app-input">
                        <?php if($user_display_info != ""): ?>
                            <option value="<?php echo $_GET['user_search']; ?>" selected><?php echo $user_display_info; ?></option>
                        <?php else: ?>
                            <option value="">Type Mobile or Name...</option>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </form>

        <div class="orange-divider">
            <?php echo ($selected_market != '') ? strtoupper($selected_market)." (".strtoupper($selected_session).") | ".$display_date : "SELECT A GAME TO VIEW RECORDS"; ?>
        </div>

        <?php if ($selected_market == ''): ?>
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <i class="fas fa-hand-pointer" style="font-size: 40px; margin-bottom: 15px;"></i>
                <p>Please select a <b>Game</b> from the list and click <b>Filter</b> to show bids.</p>
            </div>
        <?php else: ?>
            
            <button class="copy-btn-main">Copy All Bids</button>

            <div class="stats-container">
                <div class="stat-box">Total Bids: <?php echo $total_count; ?></div>
                <div class="stat-box">Total Amount: <?php echo number_format($total_amt); ?></div>
            </div>

            <?php foreach ($game_types_list as $type_label): ?>
                <?php if($selected_type == '' || $selected_type == $type_label): ?>
                    <button class="copy-btn-sub">Copy <?php echo $type_label; ?></button>
                    <div class="table-container">
                        <div class="app-table-header">
                            <div class="header-col">Game</div>
                            <div class="header-col">Type</div>
                            <div class="header-col">Session</div>
                            <div class="header-col">No.</div>
                            <div class="header-col">Bet</div>
                        </div>
                        <?php
                        $found = false;
                        // foreach($all_bets as $bet) {
                        //     if(strtolower($bet['game_type']) == strtolower($db_map[$type_label]) || strtolower($bet['raw_game']) == strtolower($db_map[$type_label])) {
                        //         $found = true;
                        //         echo '<div class="data-row">';
                        //         echo '<div class="col-item">'.$bet['market'].'</div>';
                        //         echo '<div class="col-item">'.$bet['game_type'].'</div>';
                        //         echo '<div class="col-item">'.strtoupper($bet['session']).'</div>';
                        //         echo '<div class="col-item" style="font-weight:bold; color:blue;">'.$bet['number'].'</div>';
                        //         echo '<div class="col-item" style="font-weight:bold; color:green;">'.$bet['amount'].'</div>';
                        //         echo '</div>';
                        //     }
                        // }
                        foreach($all_bets as $bet) {
                            if(strtolower($bet['game_type']) == strtolower($db_map[$type_label])) {
                                $found = true;
                                echo '<div class="data-row">';
                                echo '<div class="col-item">'.strtoupper($bet['market']).'</div>';
                                echo '<div class="col-item">'.strtoupper($bet['game_type']).'</div>';
                                // Display the specific session (OPEN/CLOSE)
                                echo '<div class="col-item" style="text-transform:uppercase;">'.$bet['session'].'</div>';
                                echo '<div class="col-item" style="font-weight:bold; color:blue;">'.$bet['number'].'</div>';
                                echo '<div class="col-item" style="font-weight:bold; color:green;">'.$bet['amount'].'</div>';
                                echo '</div>';
                            }
                        }
                        if(!$found) echo '<div class="data-row" style="justify-content:center; color:#999;">No bids found</div>';
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // 1. SELECT2 AJAX SEARCH
    $('#user_search_ajax').select2({
        width: '100%',
        ajax: {
            url: 'user-search-live.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data }; }
        }
    });

    // 2. ROBUST SIDEBAR TOGGLE (Desktop & Mobile)
    $('[data-widget="pushmenu"]').on('click', function(e) {
        e.preventDefault();
        if ($(window).width() < 992) {
            $('body').toggleClass('sidebar-open').toggleClass('sidebar-closed');
        } else {
            $('body').toggleClass('sidebar-collapse');
        }
    });

    // 3. COPY CLIPBOARD LOGIC
    // function copyToClipboard(text) {
    //     var $temp = $("<textarea>");
    //     $("body").append($temp);
    //     $temp.val(text).select();
    //     document.execCommand("copy");
    //     $temp.remove();
    //     alert("Bids copied to clipboard!");
    // }

    // $('.copy-btn-main').click(function() {
    //     let text = "";
    //     $('.data-row').each(function() {
    //         if($(this).text().trim() !== "No bids found") {
    //             text += $(this).text().replace(/\s+/g, ' ').trim() + "\n";
    //         }
    //     });
    //     copyToClipboard(text);
    // });

    // $('.copy-btn-sub').click(function() {
    //     let text = "";
    //     $(this).next('.table-container').find('.data-row').each(function() {
    //         if($(this).text().trim() !== "No bids found") {
    //             text += $(this).text().replace(/\s+/g, ' ').trim() + "\n";
    //         }
    //     });
    //     copyToClipboard(text);
    // });
    
     function copyToClipboard(text) {
        var $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
        alert("Bids copied to clipboard!");
    }

    // 1. Logic for "Copy Specific Type" (Single Ank, Jodi, etc.)
    $('.copy-btn-sub').click(function() {
        let typeLabel = $(this).text().replace('Copy ', ''); // e.g. "Single Ank"
        let market = "<?php echo strtoupper($selected_market); ?>";
        let date = "<?php echo $current_date; ?>";
        let session = "<?php echo strtolower($selected_session); ?>";

        let text = "TIME " + market + "\n";
        text += typeLabel + "-" + session + " Date :" + date + "\n";

        let total = 0;
        let table = $(this).next('.table-container');

        table.find('.data-row').each(function() {
            // Get columns: Number is index 3, Amount is index 4
            let cols = $(this).find('.col-item');
            let num = $(cols[3]).text().trim();
            let amt = $(cols[4]).text().trim();

            if (num !== "" && num !== "No bids found") {
                text += num + " == " + amt + "\n";
                total += parseInt(amt.replace(/,/g, '')) || 0;
            }
        });

        text += "Total amount: " + total;
        copyToClipboard(text);
    });

    // 2. Logic for "Copy All Bids" (Combines all tables)
    $('.copy-btn-main').click(function() {
        let fullText = "";
        let market = "<?php echo strtoupper($selected_market); ?>";
        let date = "<?php echo $current_date; ?>";
        let session = "<?php echo strtolower($selected_session); ?>";

        // Loop through each table container
        $('.table-container').each(function() {
            let table = $(this);
            let typeLabel = table.prev('.copy-btn-sub').text().replace('Copy ', '');
            
            // Check if there is data in this table
            if (table.find('.data-row').first().text().trim() !== "No bids found") {
                fullText += "TIME " + market + "\n";
                fullText += typeLabel + "-" + session + " Date :" + date + "\n";
                
                let subTotal = 0;
                table.find('.data-row').each(function() {
                    let cols = $(this).find('.col-item');
                    let num = $(cols[3]).text().trim();
                    let amt = $(cols[4]).text().trim();
                    fullText += num + " == " + amt + "\n";
                    subTotal += parseInt(amt.replace(/,/g, '')) || 0;
                });
                
                fullText += "Total amount: " + subTotal + "\n\n";
            }
        });

        if (fullText === "") {
            alert("No bids to copy!");
        } else {
            copyToClipboard(fullText.trim());
        }
    });
});
</script>

<?php include('footer.php'); ?>