<?php
// print_r($_REQUEST);
$date = date('d/m/Y',strtotime($_REQUEST['date']));
$gameID = explode("_",$_REQUEST['gameID']);

$market = $gameID[1];
$timing = $gameID[2];
$digit  = $_REQUEST['digit'];

include('config.php');

$get_rates = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM `rate`"));

$sql = "
SELECT g.*
FROM jackpot_games g
JOIN jackpot_markets m 
ON g.timing_sn = m.sn
WHERE g.date='$date'
AND g.bazar='$market'
AND m.close='$timing'
AND g.number='$digit'
";

$select = mysqli_query($con,$sql);
$i = 1;

while($row = mysqli_fetch_array($select)){
    // print_r($row);
    $userID = $row['user'];

    $user = mysqli_query($con,"SELECT * FROM `users` WHERE `mobile`='$userID'");
    $fetch = mysqli_fetch_array($user);

?>

<tr>

<td><?php echo $i; ?></td>

<td>
<?php
if($fetch['name']!=''){
    echo $fetch['name'];
}else{
    echo 'N/A';
}
?>
</td>

<td>
<?php
if($row['amount']!=''){
    echo $row['amount'];
}else{
    echo 'N/A';
}
?>
</td>

<td>
<?php
if($row['amount']!=''){
    echo $get_rates[$row['game']] * $row['amount'];
}else{
    echo 'N/A';
}
?>
</td>

<td>
<?php
if($row['bazar']!=''){
    echo $row['bazar'].' '.$row['close_time'];
}else{
    echo 'N/A';
}
?>
</td>

<td style="text-transform: capitalize;">
<?php
if($row['game']!=''){
    echo $row['game'];
}else{
    echo 'N/A';
}
?>
</td>

<td>
<?php
if($row['number']!=''){
    echo $row['number'];
}else{
    echo 'N/A';
}
?>
</td>

<td>
<?php echo date('h:i A d-m-Y',$row['created_at']); ?>
</td>

<td>
<a href="user-profile.php?userID=<?php echo $row['user']; ?>">
<i class="fas fa-eye" style="font-size:25px;"></i>
</a>
</td>

</tr>

<?php
$i++;
}
?>