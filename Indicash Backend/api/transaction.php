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
$transactionsQuery = "SELECT * FROM `transactions` WHERE user='$mobile' AND remark NOT LIKE '%Bet Cancel Refund%' ORDER BY sn DESC";
$transactionsResult = mysqli_query($con, $transactionsQuery);

// Query for games
$gamesQuery = "SELECT * FROM `games` WHERE user='$mobile' ORDER BY created_at DESC";
$gamesResult = mysqli_query($con, $gamesQuery);

// Initialize arrays
$transactionsData = [];
$gamesData = [];

// Process transactions
// while ($x = mysqli_fetch_assoc($transactionsResult)) {

//     if ($x['type'] == "0") {
//         $x['amount'] = '-' . $x['amount'];
//     }

//     // $x['date'] = date('d-m-Y H:i',$x['created_at']);
    
//     if(!empty($x['created_at']) && is_numeric($x['created_at'])){
//         $x['date'] = date('d-m-Y H:i',$x['created_at']);
//     }

//     $remark = $x['remark'];

//     $game = null;
//     $market = null;
//     $number = null;
//     $session = null;  // add this
//     $type = null;
//     // $bet_amount = null; // Add this line
//     $bet_amount = abs((float)$x['amount']); 


//     /* OLD PANEL BET FORMAT */
//     if(strpos($remark,'Bet Placed on') !== false){

//         preg_match('/Bet Placed on (.*?) market name (.*?) on number (.*)/',$remark,$m);

//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);
//         if(isset($m[3])) $number = trim($m[3]);

//         $type = "market_bet";
//     }

//     /* OLD WIN FORMAT */
//     elseif(strpos($remark,'Winning') !== false){

//         preg_match('/(.*?) (.*?) Winning/',$remark,$m);

//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);

//         $type = "win";
//     }
    
//     elseif(strpos($remark, 'Bet Placed') !== false && strpos($remark, '|') !== false){
//         preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:([^|]+)/', $remark, $m);
//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);
//         if(isset($m[3])) $session = trim($m[3]);
//         if(isset($m[4])) $number = trim($m[4]);
//         $type = "market_bet";
//     }

//     /* NEW WIN FORMAT */
//     // elseif(strpos($remark,'Result Win') !== false){

//     //     // preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);
//     //     preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:([^|]+)/',$remark,$m);

//     //     if(isset($m[1])) $game = trim($m[1]);
//     //     if(isset($m[2])) $market = trim($m[2]);
//     //     if(isset($m[3])) $session = trim($m[3]);
//     //     if(isset($m[4])) $number = trim($m[4]);

//     //     $type = "win";
//     // }
//     /* NEW WIN FORMAT */
//     elseif(strpos($remark,'Result Win') !== false){
//         // Updated regex to include the Bet segment
//         preg_match('/Game:(.*?) \| Market:(.*?) \| Session:(.*?) \| Number:(.*?) \| Bet:(.*?) \| Result Win/',$remark,$m);

//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);
//         if(isset($m[3])) $session = trim($m[3]);
//         if(isset($m[4])) $number = trim($m[4]);
//         if(isset($m[5])) $bet_amount = trim($m[5]); // Capture the bet amount

//         $type = "win";
//     }

//     /* NEW BET FORMAT */
//     elseif(strpos($remark,'Type:Bet') !== false){

//     preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);

//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);
//         if(isset($m[3])) $number = trim($m[3]);

//         $type = "market_bet";
//     }

//     /* STARLINE */
//     elseif(strpos($remark,'Type:Starline') !== false || strpos($remark,'Starline Bet Placed') !== false){

//     preg_match('/Game:(.*?) \| Market:(.*?) \| Number:([^|]+)/',$remark,$m);

//         if(isset($m[1])) $game = trim($m[1]);
//         if(isset($m[2])) $market = trim($m[2]);
//         if(isset($m[3])) $number = trim($m[3]);

//         $type = "starline_bet";
//     }

//     /* attach parsed keys if detected */
//     if($type != null){
//         $x['game'] = $game;
//         $x['market'] = $market;
//         $x['number'] = $number;
//         $x['session'] = $session ?? null;
//         $x['type'] = $type;
//         $x['bet_amount'] = $bet_amount ?? null; // Add this line
//     }


//     $transactionsData[] = $x;
// }
// Process transactions
while ($x = mysqli_fetch_assoc($transactionsResult)) {

    $is_debit = ($x['type'] == "0");
    if ($is_debit) { $x['amount'] = '-' . $x['amount']; }
    
    if(!empty($x['created_at']) && is_numeric($x['created_at'])){
        $x['date'] = date('d-m-Y H:i',$x['created_at']);
    }

    $remark = $x['remark'];
    $game_id = $x['game_id'] ?? null;

    // Initialize variables
    $game = null;
    $market = null;
    $number = null;
    $session = null;
    $type = null;
    $bet_amount = null; 

    /* 
       STEP 1: UNIVERSAL GAME ID LOOKUP 
       If a game_id exists, fetch data from the database.
    */
    if(!empty($game_id)) {
        $game_id_clean = mysqli_real_escape_string($con, $game_id);
        
        // Try fetching from main games table
        $game_lookup = mysqli_query($con, "SELECT game, bazar, number, amount, game_type FROM games WHERE sn = '$game_id_clean' LIMIT 1");
        
        // If not found in main games, check starline_games (optional, based on your DB)
        if(mysqli_num_rows($game_lookup) == 0) {
            $game_lookup = mysqli_query($con, "SELECT game, bazar, number, amount FROM starline_games WHERE sn = '$game_id_clean' LIMIT 1");
        }

        if($g_data = mysqli_fetch_assoc($game_lookup)) {
            $game       = $g_data['game'];
            $market     = $g_data['bazar'];
            $number     = $g_data['number'];
            $bet_amount = $g_data['amount'];
            $session    = $g_data['game_type'] ?? null;
        }
    }

    /* 
       STEP 2: TYPE DETECTION & FALLBACK PARSING 
       If game_id lookup didn't fill everything, or to detect the 'type' (win/bet)
    */
    if(strpos($remark, 'Win') !== false) {
        $type = "win";
    } elseif(strpos($remark, 'Starline') !== false) {
        $type = "starline_bet";
    } elseif(strpos($remark, 'Bet') !== false) {
        $type = "market_bet";
    }

    // Fallback: If no game_id was present, use Regex to parse the remark (for old records)
    if($game === null) {
        // New Pipe Format
        if(preg_match('/Game:(.*?) \| Market:(.*?) \| (?:Session:(.*?) \| )?Number:([^|]+)/', $remark, $m)){
            $game = trim($m[1]);
            $market = trim($m[2]);
            $session = !empty($m[3]) ? trim($m[3]) : null;
            $number = trim($m[4]);
        } 
        // Old String Format
        elseif(preg_match('/Bet Placed on (.*?) market name (.*?) on number (.*)/', $remark, $m)){
            $game = trim($m[1]); $market = trim($m[2]); $number = trim($m[3]);
        }
    }

    // For Debit (Bet) transactions, if bet_amount is still null, use the transaction amount
    if($is_debit && $bet_amount === null) {
        $bet_amount = abs((float)$x['amount']);
    }

    /* Attach detected/fetched keys to the response */
    if($type !== null || $game !== null){
        $x['game']    = $game;
        $x['market']  = $market;
        $x['number']  = $number;
        $x['session'] = $session;
        $x['type']    = $type ?? "other";
        $x['bet_amount'] = $bet_amount; 
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
