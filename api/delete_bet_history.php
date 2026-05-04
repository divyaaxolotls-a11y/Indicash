<?php
include "con.php";

$result = mysqli_query($con, "SELECT data FROM settings WHERE data_key='history_delete_days'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $daysToKeep = (int)$row['data'];
} else {
    die("Error fetching settings: " . mysqli_error($con));
}

// cutoff in d/m/Y format for varchar columns
$cutoffDate = date('d/m/Y', strtotime("-$daysToKeep days"));
echo "Keeping $daysToKeep days, cutoff=$cutoffDate<br>";

// delete from games
$sqlGames = "DELETE FROM games 
             WHERE STR_TO_DATE(date, '%d/%m/%Y') < STR_TO_DATE('$cutoffDate', '%d/%m/%Y')";
if (mysqli_query($con, $sqlGames)) {
    echo mysqli_affected_rows($con) . " games deleted.<br>";
} else {
    echo "Error deleting games: " . mysqli_error($con) . "<br>";
}

// delete from transactions
// cutoff in Y-m-d format for datetime columns
$cutoffDateTime = date('Y-m-d 00:00:00', strtotime("-$daysToKeep days"));

// delete from transactions
$sqlTrans = "DELETE FROM transactions 
             WHERE STR_TO_DATE(dated_on, '%Y-%m-%d %H:%i:%s') < STR_TO_DATE('$cutoffDateTime', '%Y-%m-%d %H:%i:%s')";
if (mysqli_query($con, $sqlTrans)) {
    echo mysqli_affected_rows($con) . " transactions deleted.<br>";
} else {
    echo "Error deleting transactions: " . mysqli_error($con) . "<br>";
}


mysqli_close($con);
?>
