<?php

include('config.php');

$game_type = $_POST['game_type'] ?? '';
$game_name = $_POST['game_name'] ?? '';
$status    = $_POST['status'] ?? '';
$date      = $_POST['date'] ?? '';

$where=[];

if($game_type!=''){
$where[]="game='".mysqli_real_escape_string($con,$game_type)."'";
}

if($game_name!=''){
$where[]="bazar='".mysqli_real_escape_string($con,$game_name)."'";
}

if($status!=''){
$where[]="session='".mysqli_real_escape_string($con,$status)."'";
}

if($date!=''){
$date = date('d/m/Y',strtotime($date));
$where[]="date='$date'";
}

$whereSQL="";

if(count($where)>0){
$whereSQL="WHERE ".implode(" AND ",$where);
}

/* FETCH RECORDS */

$sql = "
SELECT game, amount, status
FROM games
$whereSQL
";

$query = mysqli_query($con,$sql);

$data=[];

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

$game = $row['game'];
$bids = (float)$row['amount'];

$win = ($row['status']=='win' || $row['status']=='1') ? ($bids*9) : 0;

if(!isset($data[$game])){
$data[$game]=[
'bids'=>0,
'win'=>0
];
}

$data[$game]['bids'] += $bids;
$data[$game]['win']  += $win;

}

}

/* GAME TYPES */

$gameTypes=[
'single'=>'Single Ank',
'jodi'=>'Jodi',
'singlepatti'=>'Single Pana',
'doublepatti'=>'Double Pana',
'triplepatti'=>'Triple Pana',
'halfsangam'=>'Half Sangam',
'fullsangam'=>'Full Sangam'
];

/* OUTPUT */

foreach($gameTypes as $key=>$label){

$bids = isset($data[$key]) ? $data[$key]['bids'] : 0;
$win  = isset($data[$key]) ? $data[$key]['win'] : 0;

?>

<div class="game-row">
<div><?php echo $label; ?></div>
<div><?php echo $bids; ?></div>
<div><?php echo $win; ?></div>
</div>

<?php } ?>