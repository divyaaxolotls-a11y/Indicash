<?php
include('config.php');

$game_name = $_POST['game_name'] ?? ''; 
$date_raw  = $_POST['date'] ?? date('Y-m-d');
$date_db   = date('d/m/Y', strtotime($date_raw));

$gameTypes = [
    'single'=>'Single Ank','jodi'=>'Jodi','singlepatti'=>'Single Pana',
    'doublepatti'=>'Double Pana','triplepatti'=>'Triple Pana',
    'halfsangam'=>'Half Sangam','fullsangam'=>'Full Sangam'
];

$data = []; foreach($gameTypes as $k => $v) { $data[$k] = ['bids' => 0, 'win' => 0]; }
$starData = []; foreach($gameTypes as $k => $v) { $starData[$k] = ['bids' => 0, 'win' => 0]; }

// 1. Build Condition
$where = " WHERE date='$date_db'";
if($game_name != "") {
    $market_search = strtoupper(str_replace(" ", "_", trim($game_name)));
    $where .= " AND (bazar LIKE '$game_name%' OR bazar LIKE '$market_search%')";
}

// 2. Fetch Data
$res = mysqli_query($con, "SELECT game, amount, win_amount, status, is_loss FROM games $where");
while($row = mysqli_fetch_assoc($res)){
    $g = $row['game'];
    if(isset($data[$g]) && $row['status'] == 1 && $row['is_loss'] == 0) {
        $data[$g]['bids'] += (float)$row['amount'];
        $data[$g]['win']  += (float)$row['win_amount'];
    }
}

// 3. Fetch Starline
$sRes = mysqli_query($con, "SELECT game, amount, win_amount, status, is_loss FROM starline_games WHERE date='$date_db'");
while($row = mysqli_fetch_assoc($sRes)){
    $g = $row['game'];
    if(isset($starData[$g]) && $row['status'] == 1 && $row['is_loss'] == 0) {
        $starData[$g]['bids'] += (float)$row['amount'];
        $starData[$g]['win']  += (float)$row['win_amount'];
    }
}

// --- HTML OUTPUT ---
echo '<div class="summary-header"><div>Market: '.($game_name ?: 'All Games').'</div><div>Date : '.date("d/M/Y", strtotime($date_raw)).'</div></div>';
echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';
foreach($gameTypes as $key => $label){
    echo '<div class="game-row"><div>'.$label.'</div><div>'.(int)$data[$key]['bids'].'</div><div>'.(int)$data[$key]['win'].'</div></div>';
}

echo '<div class="summary-header" style="background:#007bff; color:white; margin-top:25px;">Starline Report</div>';
echo '<div class="game-table-header"><div>Game Type</div><div>Bids</div><div>Win</div></div>';
foreach($gameTypes as $key => $label){
    echo '<div class="game-row"><div>'.$label.'</div><div>'.(int)$starData[$key]['bids'].'</div><div>'.(int)$starData[$key]['win'].'</div></div>';
}
?>