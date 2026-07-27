<?php
include "con.php";

extract($_REQUEST);

$stamp = isset($stamp) ? $stamp : time();

$time = date("H:i",$stamp);
$date = date("d/m/Y");

/* Rates */
$sx = mysqli_query($con,"SELECT * FROM rates_delhi WHERE sn='1'");
$x = mysqli_fetch_array($sx);
$data = $x;


/* Market Filter */
if(isset($name) && $name!=""){
    
    $get_markets = mysqli_query($con,"
    SELECT * FROM jackpot_markets
    WHERE name='$name' AND is_active='1'
    ORDER BY STR_TO_DATE(close,'%H:%i')
    ");
    
}else{

    $get_markets = mysqli_query($con,"
    SELECT * FROM jackpot_markets
    WHERE is_active='1'
    ORDER BY STR_TO_DATE(close,'%H:%i')
    ");
}


while($xc = mysqli_fetch_array($get_markets)){

    $dd['name'] = $xc['name'];
    $dd['close'] = $xc['close'];

    /* open check */
    if(strtotime($time) < strtotime($xc['close'])){
        $dd['is_open'] = "1";
    }else{
        $dd['is_open'] = "0";
    }

    $dd['time'] = date("g:i a", strtotime($xc['close']));


    /* result check */
    $getResult = mysqli_query($con,"
    SELECT number,panna 
    FROM jackpot_results 
    WHERE timing_sn='".$xc['sn']."' 
    AND date='$date'
    ");

    if(mysqli_num_rows($getResult)>0){

        $dd['is_open']="0";

        $result=mysqli_fetch_array($getResult);

        $dd['result']=$result['panna'].'-'.$result['number'];

    }else{

        $dd['result']="-";

    }


    $dd['is_close']="0";

    $data['data'][]=$dd;

}

echo json_encode($data);
?>