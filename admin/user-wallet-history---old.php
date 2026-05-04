<?php 
include('header.php'); 

$mobile = $_GET['user_mobile'] ?? ''; 
$selected_date = $_GET['date'] ?? '';
$filter_type = $_GET['filter_type'] ?? 'all';

$where = " WHERE user = '$mobile' ";

// DATE FILTER
if(!empty($selected_date)){
    $where .= " AND DATE(FROM_UNIXTIME(created_at)) = '$selected_date' ";
}

// FILTER TYPE
if($filter_type == 'win')      $where .= " AND remark LIKE '%Win%' ";
if($filter_type == 'add')      $where .= " AND type = '1' ";
if($filter_type == 'withdraw') $where .= " AND (type = '0' OR remark LIKE '%Withdraw%') ";

$sql = "SELECT * FROM `transactions` $where ORDER BY `sn` DESC";
$result = mysqli_query($con, $sql);
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
body{
    background:#f2f2f2;
    font-family:sans-serif;
}

.filter-label{
    font-size:13px;
    font-weight:600;
    margin-bottom:3px;
}

.custom-input{
    border-radius:20px;
    height:40px;
    border:1px solid #ccc;
    width:100%;
    padding:0 10px;
}

/* PILLS */
.pills-container{
    display:flex;
    gap:6px;
    margin:15px 0;
    flex-wrap:wrap;
}

.pill-link{
    padding:6px 14px;
    border-radius:20px;
    color:#fff !important;
    font-size:13px;
    text-decoration:none;
}

.active-pill{
    border:2px solid #fff;
}

/* HEADER */
.orange-header{
    background:#ffa500;
    border-radius:12px;
    display:flex;
    padding:12px;
    font-weight:600;
    margin-bottom:12px;
}

.col-item{
    flex:1;
    text-align:center;
}

/* CARD */
.trans-card{
    background:#fff;
    border-radius:14px;
    margin-bottom:12px;
    border:1px solid #ddd;
    overflow:hidden;
}

.card-top{
    display:flex;
    justify-content:space-between;
    padding:12px 15px;
    font-size:18px;
    font-weight:600;
}

.card-bottom{
    padding:12px 15px;
    border-top:1px solid #eee;
}

.text-green{ color:#28a745; }
.text-red{ color:#dc3545; }

.time{
    font-size:14px;
    color:#555;
    margin-bottom:5px;
}

.remark{
    font-size:15px;
    font-weight:500;
    line-height:1.4;
}
</style>

<div class="container-fluid p-3">

<!-- FILTER -->
<form method="GET">
    <div class="row">
        <div class="col-6">
            <label class="filter-label">Date</label>
            <input type="date" name="date" value="<?php echo $selected_date; ?>" class="custom-input">
        </div>

        <div class="col-6">
            <label class="filter-label">Select User</label>
            <select name="user_mobile" class="custom-input" id="user_search_ajax">
                <option value="">Select User</option>
                <?php 
                /*$u_res = mysqli_query($con, "SELECT name, mobile FROM users ORDER BY name ASC");
                while($u = mysqli_fetch_assoc($u_res)){
                    $sel = ($mobile == $u['mobile']) ? 'selected' : '';
                    echo "<option value='".$u['mobile']."' $sel>".$u['name']."</option>";
                }*/
                if($mobile != ''){
                    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$mobile'");
                    $u = mysqli_fetch_assoc($res);
                
                    echo '<option value="'.$mobile.'" selected>';
                    echo $u['name'] . " ($mobile)";
                    echo '</option>';
                
                } else {
                
                    echo '<option value="">Search User</option>';
                
                }
                ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary mt-3" style="border-radius:20px;">Filter</button>
</form>

<!-- PILLS -->
<div class="pills-container">
<?php 
$base_url = "?user_mobile=$mobile&date=$selected_date"; 
function active($a,$b){ return $a==$b?'active-pill':''; }
?>
<a href="<?php echo $base_url; ?>&filter_type=all" class="pill-link <?php echo active('all',$filter_type); ?>" style="background:#333;">All</a>
<a href="<?php echo $base_url; ?>&filter_type=win" class="pill-link <?php echo active('win',$filter_type); ?>" style="background:#17a2b8;">Win</a>
<a href="<?php echo $base_url; ?>&filter_type=add" class="pill-link <?php echo active('add',$filter_type); ?>" style="background:#28a745;">Add</a>
<a href="<?php echo $base_url; ?>&filter_type=withdraw" class="pill-link <?php echo active('withdraw',$filter_type); ?>" style="background:#dc3545;">withdraw</a>
<!--<a href="#" class="pill-link" style="background:#333;">Bid History</a>-->
<a href="javascript:void(0)" id="bid_history_btn" class="pill-link" style="background:#333;">Bid History</a>
</div>

<!-- HEADER -->
<div class="orange-header">
    <div class="col-item" style="text-align:left;">Description</div>
    <div class="col-item">Point</div>
    <div class="col-item" style="text-align:right;">Balance</div>
</div>

<!-- DATA -->
<?php 
if($mobile == ""){
    echo "<div class='text-center mt-4'>Select user</div>";
}
elseif(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
print_r($row);

        $is_add = ($row['type'] == '1');
        $color = $is_add ? 'text-green' : 'text-red';
        $sign = $is_add ? '+' : '-';

        // =========================
        // 🔥 MARKET LOGIC
        // =========================
        $market = "";

        // CASE 1: GAME ID
        if(!empty($row['game_id']) && $row['game_id'] != 0){
            $gid = $row['game_id'];
            $q1 = mysqli_query($con, "SELECT bazar FROM games WHERE sn = '$gid' LIMIT 1");
            if(mysqli_num_rows($q1)){
                $g = mysqli_fetch_assoc($q1);
                $market = $g['bazar'];
            }
        }

        // CASE 2: FALLBACK
        if($market == ""){
            $user = $row['user'];
            $date = date('Y-m-d', (int)$row['created_at']);

            $q2 = mysqli_query($con, "
                SELECT bazar FROM games 
                WHERE user = '$user'
                AND DATE(timestamp) = '$date'
                ORDER BY sn DESC
                LIMIT 1
            ");

            if(mysqli_num_rows($q2)){
                $g = mysqli_fetch_assoc($q2);
                $market = $g['bazar'];
            }
        }

        $market_display = strtoupper(str_replace('_',' ', $market));
?>

<div class="trans-card">

    <div class="card-top">

        <div>
            <?php echo number_format($row['wallet_before'],2); ?>
        </div>

        <div class="<?php echo $color; ?>">
            <?php echo $sign . number_format($row['amount'],2); ?>
        </div>

        <div>
            <?php echo number_format($row['wallet_after'],2); ?>
        </div>

    </div>

    <div class="card-bottom">

        <div class="time">
            <?php echo date('d-m-Y h:i A', (int)$row['created_at']); ?>
        </div>

        <div class="remark">
            <b><?php echo $row['remark']; ?></b><br>

            <?php if($market_display != ""){ ?>
                <?php echo $market_display; ?> :<br>
            <?php } ?>
        </div>

    </div>

</div>

<?php 
    }
} else {
    echo "<div class='text-center mt-5'>No records found</div>";
}
?>

</div>
<!-- Modal 1: Select Category -->
<div class="modal fade" id="matkaModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Game</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <button class="btn btn-warning w-100 mb-2" onclick="openFilter()">Matka</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Advanced Filters -->
<div class="modal fade" id="filterModal">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Bid History</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="historyFilterForm">
                    <h6>By Game Type</h6>
                    <label><input type="checkbox" name="session" value="Open"> Open</label>
                    <label><input type="checkbox" name="session" value="Close"> Close</label>
                    <hr>

                    <h6>By Winning Status</h6>
                    <label><input type="checkbox" name="filter" value="win"> Win</label>
                    <label><input type="checkbox" name="filter" value="loss"> Loss</label>
                    <label><input type="checkbox" name="filter" value="pending"> Pending</label>
                    <hr>

                    <h6>By Game</h6>
                    <?php
                        $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");
                        $seen = [];
                        while($g = mysqli_fetch_assoc($g_q)){
                            $clean = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']));
                            if(!in_array($clean, $seen) && $clean != ''){
                                echo '<label class="game-box d-block mb-2" style="background:#f4b400; padding:10px; border-radius:10px;">
                                        <input type="checkbox" name="game_name[]" value="'.$clean.'"> '.$clean.'
                                      </label>';
                                $seen[] = $clean;
                            }
                        }
                    ?>
                    <hr>
                    <button type="button" class="btn btn-success w-100" onclick="goToWinner()">Filter Go</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    // Open the first modal when Bid History is clicked
    $('#bid_history_btn').click(function(){
        $('#matkaModal').modal('show');
    });
});

// Function to switch from Category modal to Filter modal
function openFilter(){
    $('#matkaModal').modal('hide');
    $('#filterModal').modal('show');
}

// Function to collect selected filters and redirect to winners.php
function goToWinner(){
    var game_name = $('input[name="game_name[]"]:checked').val();
    var session = $('input[name="session"]:checked').val();
    var filter = $('input[name="filter"]:checked').val();

    var url = "winners.php?";

    if(game_name){
        url += "game_name=" + encodeURIComponent(game_name) + "&";
    }
    if(session){
        url += "session=" + encodeURIComponent(session) + "&";
    }
    if(filter){
        url += "filter=" + encodeURIComponent(filter) + "&";
    }

    // Redirect the user to the winners report page
    window.location.href = url;
}
</script>
<script>
$(document).ready(function () {
    if ($.fn.select2) {
        $('#user_search_ajax').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "Search Mobile or Name...",
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: 'user-search-live.php',
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    } else {
        console.error("Select2 library not loaded.");
    }
});
</script>
<?php include('footer.php'); ?>