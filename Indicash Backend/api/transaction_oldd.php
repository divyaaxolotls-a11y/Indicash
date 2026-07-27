<?php
// include "../connection/config.php";
// include "con.php";

// extract($_REQUEST);

// $sx = mysqli_query($con,"SELECT * FROM `transactions` where user='$mobile' order by created_at desc");
// $sx = mysqli_query($con,"SELECT * FROM `games` where user='$mobile' order by created_at desc");

// while($x = mysqli_fetch_array($sx))
// {
//     if($x['type'] == "0")
//     {
//         $x['amount'] = '-'.$x['amount'];
//     }
//     $x['date'] = date('d/m/y',$x['created_at']);
//     $data['data'][] = $x;
// }

// echo json_encode($data);


include "con.php";

extract($_REQUEST);

// Query for transactions
$transactionsQuery = "SELECT * FROM `transactions` WHERE user='$mobile' ORDER BY sn DESC";
$transactionsResult = mysqli_query($con, $transactionsQuery);

// Query for games
$gamesQuery = "SELECT * FROM `games` WHERE user='$mobile' ORDER BY created_at DESC";
$gamesResult = mysqli_query($con, $gamesQuery);

// Initialize arrays to store results
$transactionsData = [];
$gamesData = [];

// Process transactions
while ($x = mysqli_fetch_array($transactionsResult)) {
    if ($x['type'] == "0") {
        $x['amount'] = '-' . $x['amount'];
    }
    $transactionsData[] = $x;
}

// Process games
while ($x = mysqli_fetch_array($gamesResult)) {
    $gamesData[] = $x;
}

// Combine both results into a single array
$data = [
    'transactions' => $transactionsData];

// Output the combined JSON data
echo json_encode($data);
