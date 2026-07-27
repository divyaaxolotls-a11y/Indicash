<?php
include "con.php";
date_default_timezone_set('Asia/Kolkata');

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

$date = isset($input['date']) ? $input['date'] : date("d/m/Y");
$gametype = isset($input['gametype']) ? $input['gametype'] : "main";
$data = array();


// ================= MAIN GAME =================

if($gametype == "main"){

    $get = mysqli_query($con,"SELECT * FROM gametime_manual ORDER BY open");

    while($row = mysqli_fetch_assoc($get)){

        $market = $row['market'];

        $q = mysqli_query($con,"SELECT * FROM manual_market_results 
        WHERE market='$market' AND date='$date'");

        if(mysqli_num_rows($q)>0){

            $res = mysqli_fetch_assoc($q);

            $result = $res['open_panna']."-".$res['open'];

            if($res['close']!=""){
                $result .= "-".$res['close'];
            }else{
                $result .= "-*";
            }

            if($res['close_panna']!=""){
                $result .= "-".$res['close_panna'];
            }else{
                $result .= "-***";
            }

        }else{

            $result = "***-**-***";

        }

        $mrk = array();
        $mrk['market'] = $market;
        $mrk['open_time'] = date("g:i a",strtotime($row['open']));
        $mrk['close_time'] = date("g:i a",strtotime($row['close']));
        $mrk['result'] = $result;

        $data['result'][] = $mrk;
    }
}


// ================= STARLINE GAME =================

if($gametype == "starline"){

    $get = mysqli_query($con,"SELECT * FROM starline_markets WHERE active='1'");

    while($row = mysqli_fetch_assoc($get)){

        $market = $row['name'];

        $q = mysqli_query($con,"SELECT * FROM starline_results 
        WHERE market='$market' AND date='$date'");

        if(mysqli_num_rows($q)>0){

            $res = mysqli_fetch_assoc($q);

            $result = $res['panna']."-".$res['number'];

        }else{

            $result = "***-**";

        }

        $mrk = array();
        $mrk['market'] = $market;
        $mrk['result'] = $result;

        $data['result'][] = $mrk;
    }
}


// ================= JACKPOT GAME =================

if($gametype == "jackpot"){

    $get = mysqli_query($con,"SELECT * FROM jackpot_markets WHERE is_active='1'");

    while($row = mysqli_fetch_assoc($get)){

        $market = $row['name'];

        $q = mysqli_query($con,"SELECT * FROM jackpot_results 
        WHERE market='$market' AND date='$date'");

        if(mysqli_num_rows($q)>0){

            $res = mysqli_fetch_assoc($q);

            $result = $res['number'];

        }else{

            $result = "**";

        }

        $mrk = array();
        $mrk['market'] = $market;
        $mrk['close_time'] = date("g:i a",strtotime($row['close']));
        $mrk['result'] = $result;

        $data['result'][] = $mrk;
    }
}
$count = isset($data['result']) ? count($data['result']) : 0;

$response = array();

if($count > 0){

    $response['success'] = true;
    $response['message'] = "Records found successfully";
    $response['count'] = $count;
    $response['result'] = $data['result'];

}else{

    $response['success'] = false;
    $response['message'] = "No records found";
    $response['count'] = 0;
    $response['result'] = [];

}

echo json_encode($response, JSON_PRETTY_PRINT);
exit;

// echo json_encode($data);
?>