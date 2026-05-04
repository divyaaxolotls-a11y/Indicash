<?php
//error_reporting(0);
include "../../admin/connection/config.php";
extract($_REQUEST);
?>
<?php 
?>
<?php

$red_arr=array("00","05","11","16","22","27","33","38","44","49","50","55","61","66","72","77","83","88","94","99","146","119","669","237","228","778","238","337","788" ,"149","446","699","500","555","169","114","466","278","223","377","378","233","288","469","144","199","550","000","**","***");
?>
<?php
$aldateq = query("select * from manual_market_results where market='$market' ORDER BY STR_TO_DATE(date, '%d/%m/%Y')");
while($aldatef = fetch($aldateq)){
    $ecplo = explode("/",$aldatef['date']);
    

   $date_arr[] = date("Y-m-d",mktime(0, 0, 0, $ecplo[1], $ecplo[0], $ecplo[2])); 
}
usort($date_arr, function($a, $b) {
    $dateTimestamp1 = strtotime($a);
    $dateTimestamp2 = strtotime($b);
    return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
 });
$ofdate=$date_arr[0];
$oldate = $date_arr[count($date_arr) - 1];
?>
<!--old chart-->
<?php
//min date
/*$fqryo="select MIN(date) as ofdate from `old_result_market` where `game_id`='$srow[id]'";
$fqueryo=mysqli_query($link,$fqryo);
$fquo=mysqli_fetch_assoc($fqueryo);
 $ofdate=$fquo['ofdate'];
//max date
$mquryo="SELECT MAX(date) as oldate FROM `old_result_market` WHERE game_id='$srow[id]'";
$mqueryo=mysqli_query($link,$mquryo);
$mqfo = mysqli_fetch_assoc($mqueryo);
$oldate = $mqfo['oldate'];*/
if($ofdate!=''){
$odate1=date_create($ofdate);
 $odate2=date_create($oldate);
$odiff=date_diff($odate1,$odate2);
$onumber_of_result=$odiff->format("%a") + 1;
$ototal_pages=ceil($onumber_of_result/7);

if($ototal_pages<1){
    if($ofdate!='' || $oldate!=''){
   $ototal_pages =1; 
    }
}
}else{
   $ototal_pages=0; 
}
$sat_s = 1;
$sun_s = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel Chart</title>
<meta name="Description" content="<?php echo "$desc";?>">
<meta name="Keywords" content="<?php echo $metakey ?>">
<link rel="icon" href="<?php echo "$index_favurl";?>" type="image/x-icon">
<!--<link rel="stylesheet" href="css/panel.css">-->
<link rel="stylesheet" href="css/panel.css">
<!--<link rel="stylesheet" href="css/panel2.css">-->
<script language="javascript" src="js/ajax.js"></script>
<style>
    .ov-scrool{
        .pc-demo th{
    font-size: 14px;
    padding: 10px 0;
}

.ov-scrool{
    overflow:scroll;
}
    }
</style>
</head>
<body style="background-color:"  >
<input type="hidden" id="page" name="page" value="<?php echo $page; ?>">
<div class="panel panel-info">
<div class="panel-heading text-center" style="background: #3f51b5;"><h1 style="font-size: 22px;color:#fff; text-shadow: 0px 0px;"><?php echo $market; ?> Pannel Chart</h1></div>
<div class="panel-body ov-scrool">
<table style="width: 100%; text-align:center;" class="panel-chart chart-table" cellpadding="2">
    <thead>
        <tr>
            <th>Date</th>
            <th colspan="3">Mon</th>
            <th colspan="3">Tue</th>
            <th colspan="3">Wed</th>
            <th colspan="3">Thu</th>
            <th colspan="3">Fri</th>
            <?php //if($page!="RAJDHANI NIGHT" && $page!="KALYAN NIGHT" && $page!="RAJDHANI NIGHT"){ ?>
            <?php if($sat_s==1){ ?>
            <th colspan="3">Sat</th>
            <?php }?>
            <?php //if($page!="KALYAN" && $page!="MILAN NIGHT"){ ?>
            <?php if($sun_s==1){ ?>
            <th colspan="3">Sun</th>
            <?php }  
            //} //} ?>
        </tr>
    </thead>                	
<tbody>
 <!--old chart-->   
<?php
for($a=1;$a<=$ototal_pages;$a++){
$oldatee= date('Y-m-d', strtotime($ofdate. ' + 6 days'));
?>
<tr>
     <td><?php  $ofdate; echo date('d/m/Y', strtotime($ofdate)); ?><br> To <br> <?php 
     if($sun_s==0 && $sat_s==0){
         $oldatees= date('Y-m-d', strtotime($oldatee. ' - 2 days')); 
        echo date('d/m/Y', strtotime($oldatees));
     }elseif($sun_s==1 &&  $sat_s==0){
         $oldatees= date('Y-m-d', strtotime($oldatee. ' - 1 days'));  
        echo date('d/m/Y', strtotime($oldatees));
     }elseif($sun_s==0 &&  $sat_s==1){
          $oldatees= date('Y-m-d', strtotime($oldatee. ' - 1 days')); 
         echo date('d/m/Y', strtotime($oldatees));
     }else{
         $oldatee;
         echo date('d/m/Y', strtotime($oldatee));
     }
      ?></td>
     <?php
     //for monday
     $oarr=array();
     for($b=1;$b<=7;$b++){
       $oarr[]= $ofdate;
     $oevrd= date('Y-m-d', strtotime($ofdate. ' + 0 days')); 
	
     $oevrd2= date('d/m/Y', strtotime($ofdate. ' + 0 days')); 
     $odatel_old21 = strtotime($oevrd);
     $odayn = date('D', $odatel_old21);
     $osql="select * from manual_market_results where market='$market' and date='$oevrd2'";
     $osqlq = query($osql);
     $osqlf = fetch($osqlq);
     $osqlf['lval'] = $osqlf['close_panna'];
     $osqlf['open_panna'];
     $osqlf['fval'] = $osqlf['open_panna'];
     $osqlf['mval'] = $osqlf['open'].$osqlf['close'];
     if($odayn=='Mon'){
         if($osqlf['fval']!=''){
          $omfval = str_split($osqlf['fval']); 
          $omfval1=$omfval[0];
          $omfval2=$omfval[1];
          $omfval3=$omfval[2];
         }else{
           $omfval1='*';  
           $omfval2='*';
           $omfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $omlval = str_split($osqlf['lval']);
           $omlval1=$omlval[0];  
           $omlval2=$omlval[1];
           $omlval3=$omlval[2];  
         }else{
           $omlval1='*';  
           $omlval2='*';
           $omlval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $omvalm = $osqlf['mval'];
    	 }else{
    	  $omvalm = '**';   
    	 }
     }elseif($odayn=='Tue'){
         if($osqlf['fval']!=''){
          $otfval = str_split($osqlf['fval']); 
          $otfval1=$otfval[0];
          $otfval2=$otfval[1];
          $otfval3=$otfval[2];
         }else{
           $otfval1='*';  
           $otfval2='*';
           $otfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $otlval = str_split($osqlf['lval']);
           $otlval1=$otlval[0];  
           $otlval2=$otlval[1];
           $otlval3=$otlval[2];  
         }else{
           $otlval1='*';  
           $otlval2='*';
           $otlval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $otvalm = $osqlf['mval'];
    	 }else{
    	  $otvalm = '**';   
    	 }
     }elseif($odayn=='Wed'){
        if($osqlf['fval']!=''){
          $owfval = str_split($osqlf['fval']); 
          $owfval1=$owfval[0];
          $owfval2=$owfval[1];
          $owfval3=$owfval[2];
         }else{
           $owfval1='*';  
           $owfval2='*';
           $owfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $owlval = str_split($osqlf['lval']);
           $owlval1=$owlval[0];  
           $owlval2=$owlval[1];
           $owlval3=$owlval[2];  
         }else{
           $owlval1='*';  
           $owlval2='*';
           $owlval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $owvalm = $osqlf['mval'];
    	 }else{
    	  $owvalm = '**';   
    	 }
     }elseif($odayn=='Thu'){
         if($osqlf['fval']!=''){
          $othfval = str_split($osqlf['fval']); 
          $othfval1=$othfval[0];
          $othfval2=$othfval[1];
          $othfval3=$othfval[2];
         }else{
           $othfval1='*';  
           $othfval2='*';
           $othfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $othlval = str_split($osqlf['lval']);
           $othlval1=$othlval[0];  
           $othlval2=$othlval[1];
           $othlval3=$othlval[2];  
         }else{
           $othlval1='*';  
           $othlval2='*';
           $othlval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $othvalm = $osqlf['mval'];
    	 }else{
    	  $othvalm = '**';   
    	 }  
        
     }elseif($odayn=='Fri'){
          if($osqlf['fval']!=''){
          $ofrfval = str_split($osqlf['fval']); 
          $ofrfval1=$ofrfval[0];
          $ofrfval2=$ofrfval[1];
          $ofrfval3=$ofrfval[2];
         }else{
           $ofrfval1='*';  
           $ofrfval2='*';
           $ofrfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $ofrlval = str_split($osqlf['lval']);
           $ofrlval1=$ofrlval[0];  
           $ofrlval2=$ofrlval[1];
           $ofrlval3=$ofrlval[2];  
         }else{
           $ofrlval1='*';  
           $ofrlval2='*';
           $ofrlval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $ofrvalm = $osqlf['mval'];
    	 }else{
    	  $ofrvalm = '**';   
    	 }
     }elseif($odayn=='Sat'){
         if($osqlf['fval']!=''){
          $osfval = str_split($osqlf['fval']); 
          $osfval1=$osfval[0];
          $osfval2=$osfval[1];
          $osfval3=$osfval[2];
         }else{
           $osfval1='*';  
           $osfval2='*';
           $osfval3='*';
         }
         
         if($osqlf['lval']!=''){
           $oslval = str_split($osqlf['lval']);
           $oslval1=$oslval[0];  
           $oslval2=$oslval[1];
           $oslval3=$oslval[2];  
         }else{
           $oslval1='*';  
           $oslval2='*';
           $oslval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $osvalm = $osqlf['mval'];
    	 }else{
    	  $osvalm = '**';   
    	 } 
     }elseif($odayn=='Sun'){
          if($osqlf['fval']!=''){
          $osufval = str_split($osqlf['fval']); 
          $osufval1=$osufval[0];
          $osufval2=$osufval[1];
          $osufval3=$osufval[2];
         }else{
           $osufval1='*';  
           $osufval2='*';
           $osufval3='*';
         }
         
         if($osqlf['lval']!=''){
           $osulval = str_split($osqlf['lval']);
           $osulval1=$osulval[0];  
           $osulval2=$osulval[1];
           $osulval3=$osulval[2];  
         }else{
           $osulval1='*';  
           $osulval2='*';
           $osulval3='*';
         }
    	 if($osqlf['mval']!=''){
    	  $osuvalm = $osqlf['mval'];
    	 }else{
    	  $osuvalm = '**';   
    	 }
     }
     $oevrd= date('Y-m-d', strtotime($ofdate. ' + 1 days')); 
     $ofdate =$oevrd;
     }
	?>
	<!--mon-->
	<td class="<?php if(in_array($omfval1.$omfval2.$omfval3,$red_arr)){ echo 'r';} ?>"> <?=$omfval1?> <br> <?=$omfval2?> <br> <?=$omfval3?> <br></td>
	 <td class="<?php if(in_array($omvalm,$red_arr)){ echo 'r';} ?>"><?php echo $omvalm; ?></td>
	 <td class="<?php if(in_array($omlval1.$omlval2.$omlval3,$red_arr)){ echo 'r';} ?>"> <?=$omlval1?> <br> <?=$omlval2?> <br> <?=$omlval3?> <br></td>
	 
	 <!--tue-->
	<td class="<?php if(in_array($otfval1.$otfval2.$otfval3,$red_arr)){ echo 'r';} ?>"> <?=$otfval1?> <br> <?=$otfval2?> <br> <?=$otfval3?> <br></td>
	 <td class="<?php echo (in_array($otvalm,$red_arr))?'r':''; ?>"><?php echo $otvalm; ?></td>
	 <td class="<?php if(in_array($otlval1.$otlval2.$otlval3,$red_arr)){ echo 'r';} ?>"> <?=$otlval1?> <br> <?=$otlval2?> <br> <?=$otlval3?> <br></td>
	 
	 <!--wed-->
	<td class="<?php if(in_array($owfval1.$owfval2.$owfval3,$red_arr)){ echo 'r';} ?>"> <?=$owfval1  ?> <br> <?=$owfval2  ?> <br> <?=$owfval3  ?> <br></td>
	 <td class="<?php echo (in_array($owvalm,$red_arr))?'r':''; ?>"><?php echo $owvalm; ?></td>
	 <td class="<?php if(in_array($owlval1.$owlval2.$owlval3,$red_arr)){ echo 'r';} ?>"> <?=$owlval1  ?> <br> <?=$owlval2  ?> <br> <?=$owlval3  ?> <br></td>
	 
	 <!--thu-->
	<td class="<?php if(in_array($othfval1.$othfval2.$othfval3,$red_arr)){ echo 'r';} ?>"> <?=$othfval1 ?> <br> <?=$othfval2?> <br> <?=$othfval3?> <br></td>
	 <td class="<?php echo (in_array($othvalm,$red_arr))?'r':''; ?>"><?php echo $othvalm; ?></td>
	 <td class="<?php if(in_array($othlval1.$othlval2.$othlval3,$red_arr)){ echo 'r';} ?>"> <?=$othlval1  ?> <br> <?=$othlval2?> <br> <?=$othlval3?> <br></td>
	 
	 <!--fri-->
	<td class="<?php if(in_array($ofrfval1.$ofrfval2.$ofrfval3,$red_arr)){ echo 'r';} ?>"> <?=$ofrfval1 ?> <br> <?=$ofrfval2?> <br> <?=$ofrfval3?> <br></td>
	 <td class="<?php echo (in_array($ofrvalm,$red_arr))?'r':''; ?>"><?php echo $ofrvalm; ?></td>
	 <td class="<?php if(in_array($ofrlval1.$ofrlval2.$ofrlval3,$red_arr)){ echo 'r';} ?>"> <?=$ofrlval1 ?> <br> <?=$ofrlval2?> <br> <?=$ofrlval3?> <br></td>
	 <?php if($sat_s==1){ ?>
	 <!--sat-->
	<td class="<?php if(in_array($osfval1.$osfval2.$osfval3,$red_arr)){ echo 'r';} ?>"> <?=$osfval1  ?> <br> <?=$osfval2?> <br> <?=$osfval3?> <br></td>
	 <td class="<?php echo (in_array($osvalm,$red_arr))?'r':''; ?>"><?php echo $osvalm; ?></td>
	 <td class="<?php if(in_array($oslval1.$oslval2.$oslval3,$red_arr)){ echo 'r';} ?>"> <?=$oslval1  ?> <br> <?=$oslval2?> <br> <?=$oslval3?> <br></td>
	 <?php }?>
	 
	 <?php if($sun_s==1){ ?>
	 <!--sun-->
	<td class="<?php if(in_array($osufval1.$osufval2.$osufval3,$red_arr)){ echo 'r';} ?>"> <?=$osufval1 ?> <br> <?=$osufval2?> <br> <?=$osufval3?> <br></td>
	 <td class="<?php echo (in_array($osuvalm,$red_arr))?'r':''; ?>"><?php echo $osuvalm; ?></td>
	 <td class="<?php if(in_array($osulval1.$osulval2.$osulval3,$red_arr)){ echo 'r';} ?>"> <?=$osulval1 ?> <br> <?=$osulval2?> <br> <?=$osulval3?> <br></td>
	 <?php } ?>
</tr>

<?php
// $fdate=$ldatee;
$ofdate= date('Y-m-d', strtotime($oldatee. ' + 1 days'));
}
?> 
 <!--end old chart-->  
</table>
<input type="hidden" id="dat" name="dat" value="<?php echo $ofdate; ?>">


</div>
</div>
</div>
</div>


 <script src="js/jquery.min.js"></script>

</body>
</html>   