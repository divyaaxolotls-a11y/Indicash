<?php 
include('header.php');

// 1. Capture Filter Values from the URL (GET)
$f_date    = $_GET['date'] ?? date('Y-m-d');
$f_game    = $_GET['game_name'] ?? '';
$f_session = $_GET['status_filter'] ?? '';

// Handle User Filter (Check URL 'mobile' first for popups, then 'user_search' for manual filter)
$f_user = $_GET['mobile'] ?? ($_GET['user_search'] ?? '');

$user_name_for_display = '';
if ($f_user) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$f_user'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

if (in_array(6, $HiddenProducts)){  ?>

<style>
    body { background-color: #f1f1f1; }
    .filter-wrapper { padding: 4px 0; margin-bottom: 16px; }
    .custom-input-round { border-radius: 25px; border: none; height: 42px; background-color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: 100%; padding-left: 15px; color: #555; margin-bottom: 8px; font-size: 14px; }
    .select2-container .select2-selection--single { height: 42px !important; border-radius: 25px !important; border: none !important; padding-top: 7px; }
    .btn-filter-blue { background-color: #007bff; color: white; border-radius: 25px; width: 80%; max-width: 300px; height: 42px; font-weight: bold; border: none; margin-top: 8px; }
    .table-header-orange { background-color: #FFA500 !important; color: black; font-weight: bold; text-align: center; border: none;}
    .row-green { background-color: #008000 !important; color: white; }
    .row-red   { background-color: #ff4d4d !important; color: white; }
    .table td, .table th { vertical-align: middle; text-align: center; border: 1px solid white; padding: 10px 8px; font-size: 14px; }
/* Selected row style (Black) - Use !important to override everything */
.row-selected {
    background-color: #000000 !important;
    color: #ffffff !important;
}

/* Cursor pointer */
.table tbody tr {
    cursor: pointer;
    user-select: none;
}

/* Static Set Button */
.btn-set-static {
    background-color: #343a40;
    color: white;
    border-radius: 25px;
    padding: 10px 45px;
    font-weight: bold;
    border: none;
    margin-top: 20px;
    margin-bottom: 20px;
    display: inline-block;
}
</style>

<section class="content">
    <div class="container-fluid">
        
        <!-- START FILTER FORM -->
        <form method="GET" action="transaction.php">
            <div class="filter-wrapper">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted font-weight-bold" style="padding-left:14px;">Game List</small>
                        <select class="form-control custom-input-round" name="game_name">
                            <option value="">All Games</option>
                            <?php
                                // Use gametime_manual as the master list for the dropdown
                                $master_games = mysqli_query($con, "SELECT DISTINCT market FROM gametime_manual WHERE active=1 ORDER BY market ASC");
                                while($rg = mysqli_fetch_array($master_games)){ 
                                    $selected = ($f_game == $rg['market']) ? 'selected' : '';
                                    echo "<option value='".$rg['market']."' $selected>".$rg['market']."</option>"; 
                                } ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <small class="text-muted font-weight-bold" style="padding-left:14px;">Date</small>
                        <input type="date" name="date" value="<?php echo $f_date; ?>" class="form-control custom-input-round" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <select class="form-control custom-input-round" name="status_filter">
                            <option value="">Open-Close</option>
                            <option value="OPEN" <?php if($f_session == 'OPEN') echo 'selected'; ?>>Open</option>
                            <option value="CLOSE" <?php if($f_session == 'CLOSE') echo 'selected'; ?>>Close</option>
                        </select>
                    </div>
                    <div class="col-6">
                       <?php if (isset($_GET['mobile'])): ?>
                            <input type="text" class="form-control custom-input-round" value="<?php echo $user_name_for_display; ?>" readonly>
                            <input type="hidden" name="mobile" value="<?php echo $f_user; ?>">
                        <?php else: ?>
                            <select class="form-control custom-input-round select2bs4" name="user_search" id="user_search">
                                <?php if($f_user): ?>
                                    <option value="<?php echo $f_user; ?>" selected><?php echo $user_name_for_display; ?> (<?php echo $f_user; ?>)</option>
                                <?php else: ?>
                                    <option value="">Search for a User</option>
                                <?php endif; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-center">
                       <button type="submit" class="btn btn-filter-blue">Filter</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- END FILTER FORM -->

        <div id="report-container">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-header-orange">
                        <tr>
                            <th style="text-align:left; padding-left:15px;">Game</th>
                            <th>Bids</th>
                            <th>Win</th>
                            <th>PL</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
// 1. Loop through gametime_manual to keep rows constant
if(!empty($f_game)){
    $safe_game = mysqli_real_escape_string($con, $f_game);
    $bazar_list_query = "SELECT DISTINCT market FROM gametime_manual WHERE market='$safe_game'";
} else {
    $bazar_list_query = "SELECT DISTINCT market FROM gametime_manual ORDER BY market ASC";
}
$bazar_res = mysqli_query($con, $bazar_list_query);

$grand_bids = 0; $grand_win = 0; $grand_pl = 0;

while($b_row = mysqli_fetch_assoc($bazar_res)){
    $current_market = $b_row['market'];
    
    // Fuzzy match: replaces spaces with underscores to match 'SUPREME_NIGHT_CLOSE' etc.
    $bazar_search = str_replace(' ', '_', $current_market);

    $search_date = date('d/m/Y', strtotime($f_date));
    
    // Build the query to sum history from the 'games' table
    $where = " WHERE (bazar = '$current_market' OR bazar LIKE '$bazar_search%') AND date='$search_date'";

    if(!empty($f_user))    { $where .= " AND user='".mysqli_real_escape_string($con, $f_user)."'"; }
    if(!empty($f_session)) { $where .= " AND game_type='".mysqli_real_escape_string($con, $f_session)."'"; }

    $sum_q = mysqli_query($con, "SELECT 
        SUM(amount) as total_bids, 
        SUM(CASE WHEN (status='1' AND is_loss='0') THEN (amount * 9) ELSE 0 END) as total_win 
        FROM games $where");
    
    $data = mysqli_fetch_assoc($sum_q);
    $bids = (float)($data['total_bids'] ?? 0);
    $win  = (float)($data['total_win'] ?? 0);
    $pl   = $bids - $win;

    // Row color logic
    $rowClass = ($bids > 0) ? 'row-green' : 'row-red';
    
    $grand_bids += $bids; $grand_win += $win; $grand_pl += $pl;
?>
        <tr class="<?php echo $rowClass; ?> clickable-row">
        <td style="text-align:left; padding-left:15px; font-weight:bold;">
            <?php echo $current_market; ?>
        </td>
        <td><?php echo (int)$bids; ?></td>
        <td><?php echo (int)$win; ?></td>
        <td><?php echo (int)$pl; ?></td>
    </tr>
<?php } ?>
                        
                        <tr style="background-color: #ff4d4d; color: white; font-weight: bold;">
                            <td style="text-align:left; padding-left:15px;">Total</td>
                            <td><?php echo $grand_bids; ?></td>
                            <td><?php echo $grand_win; ?></td>
                            <td><?php echo $grand_pl; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-right pr-2">
                <button type="button" id="set_btn" class="btn-set-static">Set</button>
            </div>
        </div>
    </div>
</section>

<?php } include('footer.php'); ?>

<script>
$(document).ready(function(){
    // Select2 search still needs to be JS, but it will now update the form correctly
    if ($.fn.select2 && "<?php echo isset($_GET['mobile']) ? 'pop' : ''; ?>" === '') {
        $('#user_search').select2({
            theme: 'bootstrap4',
            minimumInputLength: 4,
            placeholder: "Search for a user",
            ajax: {
                url: 'user-search-live.php',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });
    }
});
$(document).ready(function(){
    
    // 1. Single Click: Add the black layer
    $(document).on('click', '.clickable-row', function() {
        $(this).addClass('row-selected');
    });

    // 2. Double Click: Remove the black layer (reverts to original color)
    $(document).on('dblclick', '.clickable-row', function() {
        $(this).removeClass('row-selected');
    });

    // 3. Set Button: Hide the selected rows
    $(document).on('click', '#set_btn', function() {
        $('.row-selected').fadeOut(300, function() {
            $(this).css('display', 'none');
        });
    });
    
});
</script>