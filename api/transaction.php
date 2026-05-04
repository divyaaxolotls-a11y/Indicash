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

// Initialize arrays
$transactionsData = [];
$gamesData = [];

// Process transactions
while ($x = mysqli_fetch_assoc($transactionsResult)) {

    if ($x['type'] == "0") {
        $x['amount'] = '-' . $x['amount'];
    }

    // $x['date'] = date('d-m-Y H:i',$x['created_at']);
    
    if(!empty($x['created_at']) && is_numeric($x['created_at'])){
        $x['date'] = date('d-m-Y H:i',$x['created_at']);
    }

    $remark = $x['remark'];

    $game = null;
    $market = null;
    $number = null;
    $session = null;  // add this
    $type = null;
    $bet_amount = null; // Add this line


    /* OLD PANEL BET FORMAT */
    if(strpos($remark,'Bet Placed on') !== false){

        preg_match('/Bet Placed on (.*?) market name (.*?) on number (.*)/',$remark,$m);

        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);
        if(isset($m[3])) $number = trim($m[3]);

        $type = "market_bet";
    }

    /* OLD WIN FORMAT */
    elseif(strpos($remark,'Winning') !== false){

        preg_match('/(.*?) (.*?) Winning/',$remark,$m);

        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);

        $type = "win";
    }
    
    elseif(strpos($remark, 'Bet Placed') !== false && strpos($remark, '|') !== false){
        preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:([^|]+)/', $remark, $m);
        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);
        if(isset($m[3])) $session = trim($m[3]);
        if(isset($m[4])) $number = trim($m[4]);
        $type = "market_bet";
    }

    /* NEW WIN FORMAT */
    // elseif(strpos($remark,'Result Win') !== false){

    //     // preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);
    //     preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:([^|]+)/',$remark,$m);

    //     if(isset($m[1])) $game = trim($m[1]);
    //     if(isset($m[2])) $market = trim($m[2]);
    //     if(isset($m[3])) $session = trim($m[3]);
    //     if(isset($m[4])) $number = trim($m[4]);

    //     $type = "win";
    // }
    /* NEW WIN FORMAT */
    elseif(strpos($remark,'Result Win') !== false){
        // Updated regex to include the Bet segment
        preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:(.*?) \| Bet:(.*?) \| Result Win/',$remark,$m);

        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);
        if(isset($m[3])) $session = trim($m[3]);
        if(isset($m[4])) $number = trim($m[4]);
        if(isset($m[5])) $bet_amount = trim($m[5]); // Capture the bet amount

        $type = "win";
    }

    /* NEW BET FORMAT */
    elseif(strpos($remark,'Type:Bet') !== false){

    preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);

        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);
        if(isset($m[3])) $number = trim($m[3]);

        $type = "market_bet";
    }

    /* STARLINE */
    elseif(strpos($remark,'Type:Starline') !== false || strpos($remark,'Starline Bet Placed') !== false){

    preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);

        if(isset($m[1])) $game = trim($m[1]);
        if(isset($m[2])) $market = trim($m[2]);
        if(isset($m[3])) $number = trim($m[3]);

        $type = "starline_bet";
    }

    /* attach parsed keys if detected */
    if($type != null){
        $x['game'] = $game;
        $x['market'] = $market;
        $x['number'] = $number;
        $x['session'] = $session ?? null;
        $x['type'] = $type;
        $x['bet_amount'] = $bet_amount ?? null; // Add this line
    }


    $transactionsData[] = $x;
}

// Process games
while ($x = mysqli_fetch_assoc($gamesResult)) {
    $gamesData[] = $x;
}

// Final response
$data = [
    'transactions' => $transactionsData,
    'games' => $gamesData
];

echo json_encode($data);
