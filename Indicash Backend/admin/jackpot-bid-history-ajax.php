<?php

include('config.php');

$date = date('d/m/Y',strtotime($_POST['date']));
$market = $_POST['market'];

$sql = "SELECT * FROM jackpot_games WHERE date='$date'";

if($market != ''){
$sql .= " AND bazar='$market'";
}

$sql .= " ORDER BY sn DESC";

$select = mysqli_query($con,$sql);

$i=1;

while($row=mysqli_fetch_assoc($select)){

$userMobile = $row['user'];

$userQ = mysqli_query($con,"SELECT * FROM users WHERE mobile='$userMobile'");
$user = mysqli_fetch_assoc($userQ);

?>

<tr>

<td><?php echo $i; ?></td>

<td><?php echo $user['name'] ?? 'N/A'; ?></td>

<td><?php echo $user['mobile'] ?? 'N/A'; ?></td>

<td><?php echo $row['bazar']; ?></td>

<td><?php echo $row['number']; ?></td>

<td><?php echo $row['amount']; ?></td>

<td>

<?php

if($row['status']==1){
echo "<span class='badge badge-success'>Win</span>";
}
elseif($row['is_loss']==1){
echo "<span class='badge badge-danger'>Loss</span>";
}
else{
echo "<span class='badge badge-warning'>Pending</span>";
}

?>

</td>

<td><?php echo $row['date']; ?></td>

</tr>

<?php

$i++;

}

?>