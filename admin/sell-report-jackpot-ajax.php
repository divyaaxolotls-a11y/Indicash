<?php
include('config.php');

$date2 = $_POST['resultDate'];
$date = date('d/m/Y',strtotime($date2));

$marketData = explode('_',$_POST['gameID']);

$market = $marketData[1];
$timing = $marketData[2];

/* get timing_sn */

$getTiming = mysqli_query($con,"SELECT sn FROM jackpot_markets WHERE name='$market' AND close='$timing' LIMIT 1");
$timingRow = mysqli_fetch_array($getTiming);

$timing_sn = $timingRow['sn'];

function invenDescSort($item1,$item2)
{
    if ($item1['amount'] == $item2['amount']) return 0;
    return ($item1['amount'] < $item2['amount']) ? 1 : -1;
}

?>

<div class="row">
<h4 class="game_title"><?php echo $market." ".$timing; ?> Jackpot Sell</h4>
</div>

<div class="row">
<div class="container-fluid colls">

<div class="row">

<div class="col-sm">
<p>Digit</p>
<p>Amount</p>
</div>

<?php

/* LOOP FROM 00 TO 99 */

for($i=0;$i<=99;$i++){

$digit = str_pad($i,2,"0",STR_PAD_LEFT);

$query = mysqli_query($con,"
SELECT SUM(amount) as total
FROM jackpot_games
WHERE bazar='$market'
AND number='$digit'
AND date='$date'
AND timing_sn='$timing_sn'
");
// print_r($query);

$get_q = mysqli_fetch_array($query);
// print_r($get_q);die;
$total = $get_q['total'];

if($total == null){
$total = 0;
}

$digits[] = [
'digit'=>$digit,
'amount'=>$total
];

}

usort($digits,'invenDescSort');

for($x=0;$x<count($digits);$x++){
?>

<div class="col-sm">

<p><?php echo $digits[$x]['digit']; ?></p>

<p>
<span class="<?php echo ($digits[$x]['amount']==0)?'redbox':'bluebox'; ?>">
<?php echo $digits[$x]['amount']; ?>
</span>
</p>

</div>

<?php 

/* NEW ROW AFTER EVERY 10 DIGITS */

if(($x+1)%10==0 && $x+1<count($digits)){ ?>

</div>
<div class="row">

<div class="col-sm">
<p>Digit</p>
<p>Amount</p>
</div>

<?php } } ?>

</div>
</div>
</div>