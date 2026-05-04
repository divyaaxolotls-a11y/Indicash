<?php 
include('header.php'); 

// 1. DATA LOGIC
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 
$db_date = date('d/m/Y', strtotime($current_date));
$display_date = date('d-M-Y', strtotime($current_date));

$selected_market = isset($_GET['market']) ? $_GET['market'] : '';
$selected_type = isset($_GET['game_type']) ? $_GET['game_type'] : '';

// 2. CAPTURE USER SEARCH (MOBILE)
$u_search = isset($_GET['user_search']) ? mysqli_real_escape_string($con, $_GET['user_search']) : '';
$user_display_name = "";
if($u_search != "") {
    $u_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$u_search'");
    $u_data = mysqli_fetch_assoc($u_res);
    $user_display_name = ($u_data['name'] ?? 'Unknown') . " ($u_search)";
}

$total_amt = 0;
$total_count = 0;
$all_bets = [];

$query = "SELECT sg.*, st.name as timing_name 
          FROM starline_games sg 
          LEFT JOIN starline_timings st ON sg.timing_sn = st.sn 
          WHERE sg.date='$db_date'";

if ($selected_market != '') {
    $query .= " AND sg.bazar = '".mysqli_real_escape_string($con, $selected_market)."'";
}

if ($u_search != '') {
    $query .= " AND sg.user = '$u_search'";
}

if ($selected_type != '') {
    $db_map_internal = [
        'Single Ank'  => 'single',
        'Jodi'        => 'jodi',
        'Single Pana' => 'singlepatti',
        'Double Pana' => 'doublepatti',
        'Triple Pana' => 'triplepatti'
    ];
    $query_type = $db_map_internal[$selected_type] ?? '';
    if($query_type) $query .= " AND sg.game = '$query_type'";
}

$query .= " ORDER BY sg.sn DESC";
$res = mysqli_query($con, $query);

if($res) {
    while($row = mysqli_fetch_assoc($res)) {
        $all_bets[] = $row;
        $total_amt += (int)$row['amount'];
        $total_count++;
    }
}

$game_types_list = ['Single Ank', 'Jodi', 'Single Pana', 'Double Pana', 'Triple Pana'];
$db_map = [
    'Single Ank'  => 'single',
    'Jodi'        => 'jodi',
    'Single Pana' => 'singlepatti',
    'Double Pana' => 'doublepatti',
    'Triple Pana' => 'triplepatti'
];
?>

<!-- Add Select2 CSS if not in header -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    body { background-color: #fff; font-family: 'Segoe UI', sans-serif; color: #333; }
    .main-wrapper { width: 100%; padding: 12px 10px; box-sizing: border-box; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 10px; }
    @media (min-width: 992px) { .form-grid { grid-template-columns: repeat(3, 1fr); } }
    .filter-label { font-size: 12px; color: #555; margin-bottom: 3px; display: block; font-weight: 500; }
    .app-input { width: 100%; border-radius: 20px; border: 1px solid #ccc; padding: 7px 12px; font-size: 13px; height: 38px; background: #fff; }
    
    /* Select2 specific styling to match your theme */
    .select2-container .select2-selection--single { height: 38px !important; border-radius: 20px !important; border: 1px solid #ccc !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }

    .search-filter-row { display: flex; align-items: flex-end; gap: 8px; margin-top: 6px; }
    .search-container { flex: 1; min-width: 0; }
    .btn-filter { background-color: #03a9f4; color: white; border: none; border-radius: 20px; font-weight: 600; font-size: 14px; height: 38px; padding: 0 25px; cursor: pointer; }
    .orange-divider { background-color: #ff9800; border-radius: 10px; margin: 14px 0 10px; padding: 10px 16px; color: white; font-weight: bold; text-align: center; }
    .stats-container { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin: 10px 0; }
    .stat-box { background: #f8f9fa; padding: 7px 16px; border-radius: 20px; border: 1px solid #ddd; font-weight: bold; font-size: 13px; }
    .copy-btn-main { background-color: #03a9f4; color: white; border: none; border-radius: 25px; padding: 10px 40px; font-weight: 600; display: block; margin: 0 auto 10px; cursor: pointer; }
    .copy-btn-sub { background-color: #03a9f4; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 13px; margin: 14px 0 6px; display: inline-block; cursor: pointer; }
    .table-container { margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
    .app-table-header { background-color: #ff9800; display: flex; color: white; font-weight: bold; padding: 9px 0; text-align: center; font-size: 12px; }
    .header-col { flex: 1; border-right: 1px solid rgba(255,255,255,0.3); }
    .data-row { display: flex; text-align: center; padding: 9px 0; border-bottom: 1px solid #eee; background: white; font-size: 13px; }
    .col-item { flex: 1; padding: 0 2px; }
</style>

<div class="main-wrapper">
    <h4 class="text-center font-weight-bold">Starline Report Generation</h4>
    <form method="GET" id="filterForm">
        <div class="form-grid">
            <div>
                <label class="filter-label">Market</label>
                <select name="market" class="app-input">
                    <option value="">All Starline Markets</option>
                    <?php
                    $m_q = mysqli_query($con, "SELECT name FROM starline_markets WHERE active=1 ORDER BY name ASC");
                    while($m = mysqli_fetch_assoc($m_q)){
                        $sel = ($selected_market == $m['name']) ? 'selected' : '';
                        echo '<option value="'.$m['name'].'" '.$sel.'>'.$m['name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Game Type</label>
                <select name="game_type" class="app-input">
                    <option value="">All Types</option>
                    <?php foreach($game_types_list as $gt) {
                        $s = ($selected_type == $gt) ? 'selected' : '';
                        echo "<option value='$gt' $s>$gt</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label class="filter-label">Date</label>
                <input type="date" name="date" value="<?php echo $current_date; ?>" class="app-input" />
            </div>
        </div>

        <div class="search-filter-row">
            <div class="search-container">
                <label class="filter-label">Search User (Name or Mobile)</label>
                <!-- Searchable Select2 Dropdown -->
                <select name="user_search" id="user_search_ajax" class="app-input">
                    <?php if($u_search != ""): ?>
                        <option value="<?php echo $u_search; ?>" selected><?php echo $user_display_name; ?></option>
                    <?php else: ?>
                        <option value="">Search Mobile or Name...</option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-filter">Apply Filter</button>
            </div>
        </div>
    </form>

    <div class="orange-divider">
        <?php echo ($selected_market ?: 'ALL MARKETS') . " | " . $display_date; ?>
    </div>

    <button class="copy-btn-main" onclick="copyTableText('all')">Copy All Bids</button>

    <div class="stats-container">
        <div class="stat-box">Total Bids: <?php echo $total_count; ?></div>
        <div class="stat-box">Total Amount: <?php echo number_format($total_amt); ?></div>
    </div>

    <?php foreach ($game_types_list as $type_label): ?>
        <?php if($selected_type == '' || $selected_type == $type_label): ?>
            
            <div class="d-flex justify-content-between align-items-center">
                <button class="copy-btn-sub" onclick="copyTableText('<?php echo str_replace(' ', '', $type_label); ?>')">Copy <?php echo $type_label; ?></button>
                <span class="badge badge-secondary"><?php echo $type_label; ?></span>
            </div>

            <div class="table-container" id="table-<?php echo str_replace(' ', '', $type_label); ?>">
                <div class="app-table-header">
                    <div class="header-col">Market</div>
                    <div class="header-col">Timing</div>
                    <div class="header-col">User</div>
                    <div class="header-col">No.</div>
                    <div class="header-col">Amount</div>
                </div>

                <?php
                $found = false;
                foreach($all_bets as $bet) {
                   if(strtolower($bet['game']) == strtolower($db_map[$type_label])) {
                        $found = true;
                        echo '<div class="data-row">';
                        echo '<div class="col-item">'.$bet['bazar'].'</div>';
                        echo '<div class="col-item">'.($bet['timing_name'] ?: 'N/A').'</div>';
                        echo '<div class="col-item">'.$bet['user'].'</div>';
                        echo '<div class="col-item">'.$bet['number'].'</div>';
                        echo '<div class="col-item">'.$bet['amount'].'</div>';
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

<!-- Add Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Searchable Dropdown with AJAX
    $('#user_search_ajax').select2({
        width: '100%',
        placeholder: "Type Name or Mobile Number",
        minimumInputLength: 3, // Start searching after 3 characters
        ajax: {
            url: 'user-search-live.php', // Ensure this file exists in your admin folder
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

function copyTableText(id) {
    let text = "";
    let selector = (id === 'all') ? '.data-row' : '#table-' + id + ' .data-row';
    
    document.querySelectorAll(selector).forEach(row => {
        if(row.innerText.trim() !== "No data found") {
            text += row.innerText.replace(/\t/g, ' ').replace(/\n/g, ' ').trim() + "\n";
        }
    });

    if(text === "") {
        alert("No data to copy");
        return;
    }

    const temp = document.createElement("textarea");
    document.body.appendChild(temp);
    temp.value = text;
    temp.select();
    document.execCommand("copy");
    document.body.removeChild(temp);
    alert("Bids copied to clipboard!");
}
</script>

<?php include('footer.php'); ?>