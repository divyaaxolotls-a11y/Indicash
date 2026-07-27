<?php 
include('header.php'); 

if (in_array(6, $HiddenProducts)){ 

// 1. Handle Date & Filter Defaults
$f_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 
$today_db  = date('d/m/Y', strtotime($f_date)); 
$f_game = $_GET['game_name'] ?? '';
$f_type = $_GET['game_type'] ?? '';
$f_status = $_GET['status'] ?? '';
?>

<style>
    body{ background:#e5e5e5; }
    .filter-area{ padding:12px; background:#ddd; border-radius:10px; margin-bottom:10px; }
    .round-input{ width:100%; border-radius:30px; border:none; height:42px; padding-left:15px; margin-bottom:10px; box-shadow:0 1px 4px rgba(0,0,0,0.15); font-size:14px; }
    .submit-btn{ background:#1976d2; color:white; border:none; border-radius:25px; padding:8px 35px; font-size:16px; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.2); cursor:pointer; }
    .summary-header{ background:#FFA500; padding:10px; border-radius:12px; font-weight:bold; display:flex; justify-content:space-between; margin:12px 0; }
    .game-table-header{ background:black; color:white; border-radius:8px; display:flex; padding:10px; font-weight:bold; }
    .game-table-header div{ flex:1; text-align:center; }
    .game-row{ display:flex; background:#f5f5f5; margin-top:8px; border-radius:10px; padding:12px; box-shadow:0 2px 4px rgba(0,0,0,0.2); font-weight:600; }
    .game-row div{ flex:1; text-align:center; }
    body{ background:#f4f7f6; }
    /* Table styling to match SC */
    .game-table-header { 
        background: #000; color: #fff; border-radius: 8px; display: flex; 
        padding: 12px; font-weight: bold; margin-bottom: 5px; 
    }
    .game-table-header div { flex: 1; text-align: center; }

    .game-row { 
        display: flex; background: #fff; margin-bottom: 8px; 
        border-radius: 12px; padding: 15px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.08); 
        font-weight: 700; color: #333;
        border: 1px solid #ddd;
    }
    .game-row div { flex: 1; text-align: center; font-size: 16px; }
    .game-row div:first-child { font-weight: 800; color: #000; }
</style>

<section class="content">
    <div class="container-fluid">
        <!-- FILTER AREA -->
        <div class="filter-area">
            <div class="row">
                <div class="col-6">
                    <select id="game_type" class="round-input">
                        <option value="">All Types</option>
                        <option value="single">Single Ank</option>
                        <option value="jodi">Jodi</option>
                        <option value="singlepatti">Single Pana</option>
                        <option value="doublepatti">Double Pana</option>
                        <option value="triplepatti">Triple Pana</option>
                        <option value="halfsangam">Half Sangam</option>
                        <option value="fullsangam">Full Sangam</option>
                    </select>
                </div>
                <div class="col-6">
                    <select id="game_name" class="round-input">
                        <option value="">All Games</option>
                        <?php
                        $gq = mysqli_query($con,"SELECT DISTINCT market FROM gametime_manual ORDER BY market ASC");
                        while($g = mysqli_fetch_assoc($gq)){
                            echo "<option value='".$g['market']."'>".$g['market']."</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-6">
                    <select id="status" class="round-input">
                        <option value="">Both Sessions</option>
                        <option value="OPEN">Open</option>
                        <option value="CLOSE">Close</option>
                    </select>
                </div>
                <div class="col-6">
                    <input type="date" id="date" value="<?php echo $f_date; ?>" class="round-input">
                </div>
                <div class="col-12 text-center">
                    <button id="submitFilter" class="submit-btn">Submit</button>
                </div>
            </div>
        </div>

        <!-- This wrapper is what the AJAX will update -->
        <div id="report-data-wrapper">
            <div class="summary-header">
                <div id="summaryText">Game: all , Market: all</div>
                <div id="summaryDate">Date : <?php echo date("d/M/Y", strtotime($f_date)); ?></div>
            </div>

            <!--<div id="report-data">-->
                <!-- Initial Load Logic -->
            <!--    <?php-->
            <!--    $where = " WHERE date='$today_db'";-->
            <!--    $f_game_db = strtoupper(str_replace(" ", "_", trim($f_game)));-->

            <!--    $where = " WHERE date='$today_db'";-->
            <!--    if($f_game != '') {-->
            <!--        $where .= " AND (bazar LIKE '$f_game%' OR bazar LIKE '$f_game_db%')";-->
            <!--    }-->
            <!--    if($f_status != '') {-->
            <!--        $where .= " AND game_type='$f_status'";-->
            <!--    }-->
                
            <!--    $sql = "SELECT game, amount, win_amount FROM games $where";-->
            <!--    $select = mysqli_query($con, $sql);-->
            <!--    $data = [];-->
            <!--    while($row = mysqli_fetch_assoc($select)){-->
            <!--        $g = $row['game'];-->
            
            <!--         if($row['status'] == 1 && $row['is_loss'] == 0) {-->
            <!--            if(isset($data[$g])) {-->
                          
            <!--            }-->
            <!--        }-->
            <!--    }-->

            <!--    $starlineData = [];-->
            <!--    $ssql = mysqli_query($con, "SELECT game, amount, win_amount FROM starline_games $where");-->
            <!--    while($row = mysqli_fetch_assoc($ssql)){-->
            <!--        $g = $row['game'];-->
            <!--        if(!isset($starlineData[$g])) $starlineData[$g] = ['bids'=>0, 'win'=>0];-->
            <!--        $starlineData[$g]['bids'] += (float)$row['amount'];-->
            <!--        $starlineData[$g]['win']  += (float)$row['win_amount'];-->
            <!--    }-->

            <!--    $gameTypes = ['single'=>'Single Ank','jodi'=>'Jodi','singlepatti'=>'Single Pana','doublepatti'=>'Double Pana','triplepatti'=>'Triple Pana','halfsangam'=>'Half Sangam','fullsangam'=>'Full Sangam'];-->

            <!--    echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';-->
            <!--    foreach($gameTypes as $key=>$label){-->
            <!--        echo '<div class="game-row"><div>'.$label.'</div><div>'.($data[$key]['bids'] ?? 0).'</div><div>'.($data[$key]['win'] ?? 0).'</div></div>';-->
            <!--    }-->

            <!--    echo '<div class="summary-header" style="background:#007bff; color:white; margin-top:25px;">Starline Report</div>';-->
            <!--    echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';-->
            <!--    foreach($gameTypes as $key=>$label){-->
            <!--        echo '<div class="game-row"><div>'.$label.'</div><div>'.($starlineData[$key]['bids'] ?? 0).'</div><div>'.($starlineData[$key]['win'] ?? 0).'</div></div>';-->
            <!--    }-->
            <!--    ?>-->
            <!--</div>-->
            <div id="report-data">
            <?php
                $gameTypes = [
                    'single' => 'Single Ank', 'jodi' => 'Jodi', 'singlepatti' => 'Single Pana',
                    'doublepatti' => 'Double Pana', 'triplepatti' => 'Triple Pana',
                    'halfsangam' => 'Half Sangam', 'fullsangam' => 'Full Sangam'
                ];
            
                // 1. Initialize
                $data = []; foreach($gameTypes as $k => $v) { $data[$k] = ['bids' => 0, 'win' => 0]; }
                $starData = []; foreach($gameTypes as $k => $v) { $starData[$k] = ['bids' => 0, 'win' => 0]; }
            
                // 2. Query - No bazar filter if $f_game is empty
                $where = " WHERE date='$today_db'";
                if($f_game != '') { 
                    $f_game_db = strtoupper(str_replace(" ", "_", trim($f_game)));
                    $where .= " AND (bazar LIKE '$f_game%' OR bazar LIKE '$f_game_db%')"; 
                }
            
                // 3. Fetch Normal Games
                $res = mysqli_query($con, "SELECT game, amount, win_amount, status, is_loss FROM games $where");
                while($row = mysqli_fetch_assoc($res)){
                    $g = $row['game'];
                    if(isset($data[$g]) && $row['status'] == 1 && $row['is_loss'] == 0) {
                        $data[$g]['bids'] += (float)$row['amount'];
                        $data[$g]['win']  += (float)$row['win_amount'];
                    }
                }
            
                // 4. Fetch Starline
                $sRes = mysqli_query($con, "SELECT game, amount, win_amount, status, is_loss FROM starline_games WHERE date='$today_db'");
                while($row = mysqli_fetch_assoc($sRes)){
                    $g = $row['game'];
                    if(isset($starData[$g]) && $row['status'] == 1 && $row['is_loss'] == 0) {
                        $starData[$g]['bids'] += (float)$row['amount'];
                        $starData[$g]['win']  += (float)$row['win_amount'];
                    }
                }
            
                // 5. Display Normal
                echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';
                foreach($gameTypes as $key => $label){
                    echo '<div class="game-row"><div>'.$label.'</div><div>'.(int)$data[$key]['bids'].'</div><div>'.(int)$data[$key]['win'].'</div></div>';
                }
            
                // 6. Display Starline
                echo '<div class="summary-header" style="background:#007bff; color:white; margin-top:25px;">Starline Report</div>';
                echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';
                foreach($gameTypes as $key => $label){
                    echo '<div class="game-row"><div>'.$label.'</div><div>'.(int)$starData[$key]['bids'].'</div><div>'.(int)$starData[$key]['win'].'</div></div>';
                }
            ?>
            </div>
        </div>
    </div>
</section>

<script>
$('#submitFilter').click(function(){
    var game_type = $('#game_type').val();
    var game_name = $('#game_name').val();
    var status = $('#status').val();
    var date = $('#date').val();

    // Visual feedback
    $('#submitFilter').text('Loading...');

    $.ajax({
        url: 'profit-report-ajax.php',
        type: 'POST',
        data: {
            game_type: game_type,
            game_name: game_name,
            status: status,
            date: date
        },
        success: function(data){
            $('#report-data-wrapper').html(data);
            $('#submitFilter').text('Submit');
        }
    });
});
</script>

<?php 
} else { echo "<script>window.location.href='unauthorized.php';</script>"; }
include('footer.php');
?>