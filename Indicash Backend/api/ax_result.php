<?php
include "con.php";

extract($_REQUEST);
$date = date('d/m/Y');


//$date = '11/12/2023';
// $pattern = '/^\d{3}-\d{2}-\d{3}$/'; // Adjust the pattern as needed

// $sx = mysqli_query($con, "SELECT 
//           manual_market_results.market AS market_name,
//           manual_market_results.date AS market_time,
//           manual_market_results.open_panna,
//           manual_market_results.open,
//           manual_market_results.close,
//           manual_market_results.close_panna,
//           manual_market_results.date,
//           gametime_manual.market,
//           gametime_manual.open as gametime_open,
//           gametime_manual.close as gametime_close,
//           gametime_manual.active
//       FROM
//           manual_market_results
//       INNER JOIN
//           gametime_manual
//       ON
//           manual_market_results.market = gametime_manual.market 
//           WHERE
//           manual_market_results.date = '$date';");


// $sx = mysqli_query($con, "SELECT
//     manual_market_results.market as market_name,
//     manual_market_results.date AS market_time,
//     manual_market_results.open_panna,
//     manual_market_results.open,
//     manual_market_results.close,
//     manual_market_results.close_panna,
//     manual_market_results.date,
//     gametime_manual.open AS gametime_open,
//         gametime_manual.market AS market,

//     gametime_manual.close AS gametime_close,
//     gametime_manual.active
// FROM
//     gametime_manual
// LEFT JOIN
//     manual_market_results ON gametime_manual.market = manual_market_results.market AND manual_market_results.date = '$date';");


// $sx=mysqli_query($con, "SELECT
//     manual_market_results.market as market_name,
//     manual_market_results.date AS market_time,
//     manual_market_results.open_panna,
//     manual_market_results.open,
//     manual_market_results.close,
//     manual_market_results.close_panna,
//     manual_market_results.date,
//     gametime_manual.open AS gametime_open,
//         gametime_manual.market AS market,

//     gametime_manual.close AS gametime_close,
//     gametime_manual.active
// FROM
//     gametime_manual
// LEFT JOIN
//     manual_market_results ON gametime_manual.market = manual_market_results.market AND manual_market_results.date = '$date';");

// $data = array();

// while ($x = mysqli_fetch_array($sx)) {
//     $result = $x['result'];

//     // Check if the result matches the desired pattern
//     // if (preg_match($pattern, $result)) {
//         $data[] = array(
//             'sn' => $x['sn'],
//             'market' => $x['market'],
//             'open_panna' =>  $x['open_panna'],
//             'open' =>  $x['open'],
//             'close' => $x['close'],
//             'close_panna' => $x['close_panna'],
//                         'gametime_open' => $x['gametime_open'],
//             'gametime_close' => $x['gametime_close'],

            
//             'date' => $x['date'],

//         );
//     // }
// }

// echo json_encode($data);
 ?>
 <?php
$sx = mysqli_query($con, "
    SELECT
        manual_market_results.market as market_name,
        manual_market_results.sn,
        manual_market_results.date AS market_time,
        manual_market_results.open_panna,
        manual_market_results.open,
        manual_market_results.close,
        manual_market_results.close_panna,
        manual_market_results.date,
        gametime_manual.open AS gametime_open,
        gametime_manual.market AS market,
        gametime_manual.close AS gametime_close,
        gametime_manual.active
    FROM
        gametime_manual
    LEFT JOIN
        manual_market_results ON gametime_manual.market = manual_market_results.market
    ORDER BY manual_market_results.sn DESC;
");

$data = array();
$uniqueMarkets = array();

while ($x = mysqli_fetch_array($sx)) {
    $market = $x['market'];

    // Check if we haven't added a record for this market yet
    if (!isset($uniqueMarkets[$market])) {
        // Add this record to the result
        $data[] = array(
            'sn' => $x['sn'],
            'market' => $x['market'],
            'open_panna' =>  $x['open_panna'],
            'open' =>  $x['open'],
            'close' => $x['close'],
            'close_panna' => $x['close_panna'],
            'gametime_open' => $x['gametime_open'],
            'gametime_close' => $x['gametime_close'],
            'date' => $x['date'],
        );

        // Mark this market as processed
        $uniqueMarkets[$market] = true;
    }
}

echo json_encode($data);
?>

