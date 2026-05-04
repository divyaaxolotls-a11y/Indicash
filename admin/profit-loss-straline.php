<?php 
include('header.php');

// 1. Capture Filter Values
$f_date    = $_GET['date'] ?? date('Y-m-d');
$f_market  = $_GET['market_name'] ?? ''; 
$f_user    = $_GET['mobile'] ?? ($_GET['user_search'] ?? '');

$user_name_for_display = '';
if ($f_user) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$f_user'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

if (in_array(18, $HiddenProducts)){  ?>

<style>
    body { background-color: #f1f1f1; }
    .filter-wrapper { padding: 4px 0; margin-bottom: 16px; }
    .custom-input-round { border-radius: 25px; border: none; height: 42px; background-color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: 100%; padding-left: 15px; color: #555; margin-bottom: 8px; font-size: 14px; }
    .select2-container .select2-selection--single { height: 42px !important; border-radius: 25px !important; border: none !important; padding-top: 7px; }
    .btn-filter-blue { background-color: #007bff; color: white; border-radius: 25px; width: 80%; max-width: 300px; height: 42px; font-weight: bold; border: none; margin-top: 8px; }
    .table-header-orange { background-color: #FFA500 !important; color: black; font-weight: bold; text-align: center; border: none;}
    
    /* Rows with 0 bids will be Red, rows with bids will be Green */
    .row-green { background-color: #008000 !important; color: white; }
    .row-red   { background-color: #ff4d4d !important; color: white; }
    .row-green { background-color: #008000 !important; color: white; }
.row-red   { background-color: #ff4d4d !important; color: white; }
.row-selected { background-color: #000000 !important; color: #ffffff !important; }
    .table td, .table th { vertical-align: middle; text-align: center; border: 1px solid white; padding: 10px 8px; font-size: 14px; }
    .row-selected { background-color: #000000 !important; color: #ffffff !important; }
    .table tbody tr { cursor: pointer; user-select: none; }
    .btn-set-static { background-color: #343a40; color: white; border-radius: 25px; padding: 10px 45px; font-weight: bold; border: none; margin-top: 20px; margin-bottom: 20px; display: inline-block; }
</style>

<section class="content">
    <div class="container-fluid">
        <h4 class="text-center py-2">Starline Profit Loss</h4>
        
        <form method="GET" action="profit-loss-straline.php">
            <div class="filter-wrapper">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted font-weight-bold" style="padding-left:14px;">Market Filter</small>
                        <select class="form-control custom-input-round" name="market_name">
                            <option value="">All Starline Markets</option>
                            <?php
                                $m_query = mysqli_query($con, "SELECT  name FROM starline_markets ORDER BY name ASC");
                                while($rm = mysqli_fetch_array($m_query)){ 
                                    $selected = ($f_market == $rm['name']) ? 'selected' : '';
                                    echo "<option value='".$rm['name']."' $selected>".$rm['name']."</option>"; 
                                } ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <small class="text-muted font-weight-bold" style="padding-left:14px;">Date</small>
                        <input type="date" name="date" value="<?php echo $f_date; ?>" class="form-control custom-input-round" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
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
                       <button type="submit" class="btn btn-filter-blue">Filter Starline</button>
                    </div>
                </div>
            </div>
        </form>

        <div id="report-container">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-header-orange">
                        <tr>
                            <th style="text-align:left; padding-left:15px;">Market / Timing</th>
                            <th>Bids</th>
                            <th>Win</th>
                            <th>PL</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
$search_date = date('d/m/Y', strtotime($f_date));

$grand_bids = 0; 
$grand_win  = 0; 
$grand_pl   = 0;

// ✅ GET MARKETS FIRST
$market_sql = "SELECT name FROM starline_markets WHERE active=1";

if(!empty($f_market)) {
    $market_sql .= " AND name = '".mysqli_real_escape_string($con, $f_market)."'";
}
$market_sql .= " ORDER BY name ASC";

$m_res = mysqli_query($con, $market_sql);

// ✅ LOOP MARKETS
while($m_row = mysqli_fetch_assoc($m_res)){

    $m_name = $m_row['name'];

    // ✅ GET TIMINGS OF THIS MARKET
    $t_res = mysqli_query($con,"
        SELECT sn, name 
        FROM starline_timings 
        WHERE name = '".mysqli_real_escape_string($con,$m_name)."'
        ORDER BY STR_TO_DATE(name, '%h:%i %p') ASC
    ");

    while($t_row = mysqli_fetch_assoc($t_res)){

        $t_id   = $t_row['sn'];
        $t_name = $t_row['name'];

        $where = " WHERE timing_sn = '$t_id' AND date = '$search_date'";

        if(!empty($f_user)) {
            $where .= " AND user = '".mysqli_real_escape_string($con, $f_user)."'";
        }

        $sum_q = mysqli_query($con, "SELECT 
            SUM(amount) as total_bids, 
            SUM(win_amount) as total_win 
            FROM starline_games $where");

        $data = mysqli_fetch_assoc($sum_q);

        $bids = (float)($data['total_bids'] ?? 0);
        $win  = (float)($data['total_win'] ?? 0);
        $pl   = $bids - $win;

        $rowClass = ($bids > 0) ? 'row-green' : 'row-red';

        $grand_bids += $bids; 
        $grand_win  += $win; 
        $grand_pl   += $pl;
?>
<tr class="<?php echo $rowClass; ?> clickable-row">
    <td style="text-align:left; padding-left:15px; font-weight:bold;">
        <?php echo $m_name . ' - ' . $t_name; ?>
    </td>
    <td><?php echo (int)$bids; ?></td>
    <td><?php echo (int)$win; ?></td>
    <td><?php echo (int)$pl; ?></td>
</tr>
<?php 
    } // timing loop
} // market loop
?>
                        
                        <tr style="background-color: #343a40; color: white; font-weight: bold;">
                            <td style="text-align:left; padding-left:15px;">TOTAL STARLINE</td>
                            <td><?php echo (int)$grand_bids; ?></td>
                            <td><?php echo (int)$grand_win; ?></td>
                            <td><?php echo (int)$grand_pl; ?></td>
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
    // Select2
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

    // Row management
    $(document).on('click', '.clickable-row', function() {
        $(this).addClass('row-selected');
    });
    $(document).on('dblclick', '.clickable-row', function() {
        $(this).removeClass('row-selected');
    });
    $(document).on('click', '#set_btn', function() {
        $('.row-selected').fadeOut(300);
    });
});
</script>