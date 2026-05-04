<?php
include('config.php');
session_start();

$market = $_POST['market'];
$date = date('d/m/Y', strtotime($_POST['date'])); // DB format: 22/01/2026
$session = $_POST['session'];

$bazar_name = str_replace(" ", "_", strtoupper($market . " " . $session));

$q = "SELECT * FROM `games` WHERE `bazar` LIKE '%$bazar_name%' AND `date` = '$date'";
$res = mysqli_query($con, $q);

if(mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_array($res)) {
        echo "<tr>
                <td>".$row['user']."</td>
                <td>".$row['game']."</td>
                <td>".$row['number']."</td>
                <td>".$row['amount']."</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No bets found.</td></tr>";
}
?>