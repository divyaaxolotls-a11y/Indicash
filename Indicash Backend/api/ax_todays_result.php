
<?php
include "con.php";

extract($_REQUEST);
$date = date('d/m/Y');
$market = isset($_GET['market']) ? mysqli_real_escape_string($con, $_GET['market']) : 'TIME BAZAR';

// Check for records for today
$sx = mysqli_query($con, "SELECT * FROM manual_market_results WHERE date = '$date' AND market ='$market';");

$data = array();

if (mysqli_num_rows($sx) > 0) {
    while ($x = mysqli_fetch_array($sx)) {
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
    }
} else {
    // If no records found for today, fetch records from yesterday
    $yesterday = date('d/m/Y', strtotime('-1 day'));
    $sx_yesterday = mysqli_query($con, "SELECT * FROM manual_market_results WHERE date = '$yesterday' AND market ='$market';");

    if (mysqli_num_rows($sx_yesterday) > 0) {
        while ($x = mysqli_fetch_array($sx_yesterday)) {
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
        }
    } else {
        // Handle the case when no records are found for today and yesterday
        $response = array(
            'error' => true,
            'message' => 'No records found for the specified date and market.',
        );

        echo json_encode($response);
        exit; // Stop execution if no records are found
    }
}



echo json_encode($data);
?>
