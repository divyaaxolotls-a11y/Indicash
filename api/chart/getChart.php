<?php
include "../../admin/connection/config.php";
extract($_REQUEST);


$results = query("select timing, date, panna, number from starline_results where market='$market'");
while($dd = fetch($results)) {

$data['result'][$dd['date']][$dd['timing']]['open'] = $dd['number'];
$data['result'][$dd['date']][$dd['timing']]['panna'] = $dd['panna'];

}


?>

<!DOCTYPE html>

<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo $market; ?> Chart</title>
</head>
<body>


 <style>html{overflow-x:hidden;scroll-behavior:smooth}body{background-color:black;text-align:center;padding:3px 10px;margin:0;scroll-behavior:smooth;font-style:italic;font-family:Helvetica,sans-serif;font-weight:700}*{margin:0;padding:0;box-sizing:border-box}a:hover,a{text-decoration:none}.faq h4,.faq p,.faq a,.disclamer h2,.disclamer p,.about-us p,.about-us b,.about-us a,.ftr-sm h2,.ftr-sm p,.pby-us{font-style:normal}.paid-gm p,.matka-card>div,.satta-result div{border-bottom:1px solid #4b3730}.logo,.mrque-div,.satta-text,.cm-patti,.matka-result,.conta,.slash-text,.satta-result,.paid-gm,.blue-div,.red-list>div,.purpel-header,.faq,.disclamer,.about-us,.ftr-sm,.pby-us{border:2px solid #eb008b;border-radius:10px 0 10px 10px;margin-bottom:2px;overflow:hidden}.paid-gm p,.matka-card>div,.satta-result div{border-bottom:1px solid #0d6059}.logo img{width:220px;height:auto;padding:6px 0 0}.mrque-div{display:flex;padding:5px;align-items:center}.mrque-div img{width:90px;height:auto;border-radius:5px}.mrque-div marquee{color:red;font-size:15px}.satta-text{padding:5px}.satta-text h1{font-size:16px;color:#1a237e;padding-bottom:3px}.satta-text p{color:#444;font-size:14px}.cm-patti{border-color:#1a237e}.cm-patti .row{display:-webkit-flex;display:-moz-flex;display:-ms-flex;display:-o-flex;display:flex}.cm-patti .row>div{width:50%}.cm-patti h4{font-size:24px;color:#ff0;text-shadow:1px 1px 3px #000000}.cm-patti p{font-size:22px;color:#cc217f;text-shadow:1px 1px 2px #ffe0c0}.cm-patti .aa55{border-right:1px solid #e91e63}.cm-patti .bb55{border-left:1px solid #e91e63}.cm-patti h3{margin:0;background-image:linear-gradient(45deg,#9c27b0,#e91e63,#9c27b0);color:#fff;padding-top:2px;border-bottom:1px solid #ffffff61;font-size:22px}.matka-result{}.matka-result h4{background-color:#1a237e!important;color:#fff;padding-top:2px;padding-bottom:4px;font-size:30px}.matka-card>div{padding-bottom:4px;padding-bottom:4px}.matka-card>div:last-child{border-width:0px}.matka-card h6{font-size:22px!important;color:#880e4f;text-shadow:1px 1px 2px #ffe2c6}.matka-card h5{font-size:21px!important;color:#4a148c;text-shadow:1px 1px 2px #fdf3ff;line-height:1}.refresh-btn,.matka-card a{border:2px solid #ff006c;background-color:#ff006c;color:#fff;padding:3px 7px;border-radius:8px 0;box-shadow:0px 0px 1px #000000d6;font-size:12px;margin:2px 0 -1px;display:inline-block;transition:all .3s}.refresh-btn:hover,.matka-card a:hover{border:2px solid #ba004f;background-color:#ba004f;box-shadow:0px 0px 13px 3px #00000033;cursor:pointer}.conta{padding-top:4px;padding-bottom:7px;background-color:#fbe7ff;display:-webkit-flex;display:-moz-flex;display:-ms-flex;display:-o-flex;display:flex;-ms-align-items:center;align-items:center;justify-content:center}.conta p{font-size:22px;color:#ed143d;display:flex;flex-direction:column;justify-content:center;margin-right:12px}.conta a{background-color:#ffc107;color:#000;padding:5px 8px 2px;border-radius:80px;display:inline-block;box-shadow:0 0 10px -3px #000;border:1px solid #ee008d;font-size:18px}.conta a:hover{box-shadow:0 0 10px 0px #000}.slash-text{color:#000;line-height:1.4;font-size:14px;padding:4px 10px;text-shadow:1px 1px #f4e1e1}.banner{border-radius:4px;background:#aa00c0;color:#fff;text-shadow:1px 1px 0 #444;letter-spacing:1px;font-size:24px;padding:4px 4px 4px;margin-bottom:2px}.satta-result{}.satta-result h4{font-size:22px;background-color:transparent;color:#1a237e;text-shadow:1px 1px #d9d9d9}.satta-result h5{margin:0;font-size:22px;background-color:transparent;color:#880e4f;text-shadow:1px 1px #0000001f;line-height:1}.satta-result h6{color:#7a028d;font-size:15px;padding:2px 0;text-shadow:1px 1px 2px #c4c4c4;margin-bottom:0}.satta-result div{padding:3px}.satta-result div:last-child{border-bottom-width:0}.yellowbg{background-color:#ff0;border-bottom:1px solid #FF9800!important}.paid-gm{border-color:#085e58}.paid-gm h4{background-color:#085e58;color:#fff;font-size:28px;padding:3px 0 4px}.paid-gm img{border:2px solid #085e58;border-radius:10px 0 10px 10px;width:250px;height:auto;margin-top:4px}.paid-gm p{padding:6px 20px;text-shadow:1px 1px 2px #eee}.paid-gm .aa,.paid-gm .bb{color:#000;font-size:18px}.paid-gm .bb,.paid-gm .cc,.paid-gm .dd{color:#085e58}.paid-gm .cc{font-size:19px}.paid-gm .dd{font-size:20px}.paid-gm .ee{color:#e91e63;padding-bottom:3px;font-size:21px}.paid-gm .ff{color:#e91e63;font-size:15px}.paid-gm .gg{color:#000;font-size:32px}.paid-gm span{display:block}.my-table{margin-bottom:2px}.my-table h4{border:solid 2px #e91e63;border-bottom-width:0;padding:3px 5px 2px;font-size:24px}.my-table table{border-collapse:collapse;width:100%}.my-table thead{background-color:#efefef;font-size:16px}.my-table tbody{font-size:16px}.my-table th,.my-table td{border:2px solid #e91e63}.my-table th,.my-table td{padding:2px 0;font-size:15px}.my-table th{color:#e91e63}.my-table td{}.my-table tr td:nth-child(2),.my-table tr td:nth-child(4){color:#00f}.my-table.cm-sl h2,.my-table.mr-sl h2{background-color:yellow;color:blue}.my-table.mumraj-sl h4{background-color:#800080;color:#fff}.blue-div{border:2px solid #1f3092}.blue-div h4{background-color:#1f3092;color:#fff;font-size:30px;padding:3px 5px}.blue-div a{color:#000;font-size:22px;display:block;border-bottom:2px solid #1f3092;padding:5px}.blue-div a:last-child{border-bottom-width:0px}.red-list{}.red-list>div{}.red-list h4{background-color:#e71d36;color:#fff;line-height:1.1;padding:4px 10px 3px;text-shadow:1px 1px 2px #000;font-size:24px}.red-list p{font-size:18px;text-align:center;line-height:1.3}.purpel-header{}.purpel-header h4{color:#fff;padding:5px 10px 3px;font-size:24px}.purpel-header a{display:block;font-size:22px;padding:5px 7px 4px}.ab1 a{border-bottom:2px solid #024c88;color:#003c6c}.purpel-header a:last-child{border-bottom-width:0}.ab1{border-color:#024c88}.ab1 h4{background-color:#024c88}.ab2 a{border-bottom:2px solid #460000;color:#460000}.ab2{border-color:#460000}.ab2 h4{background-color:#460000}.faq{}.faq h4{color:#d70544;font-size:22px;padding:5px 5px 6px;border-top:1.5px solid #e0557f;margin-top:5px}.faq h4:first-child{border-top-width:0;margin-top:0}.faq p{font-size:12px;padding:0 5px 15px;line-height:1.4;color:#1a1a1a}.faq a{color:#d70544;text-decoration:underline}@media only screen and (max-width:768px){.faq h4{font-size:15px}}.disclamer{}.disclamer h4{color:#fff;font-size:18px;margin-bottom:5px;background-color:#eb3269;padding-top:4px;text-shadow:1px 1px 3px #000}.disclamer p{font-size:13px;color:#340d7a;padding:2px 5px 5px;line-height:1.2}.about-us{padding:5px}.about-us p{font-size:13px;margin-bottom:0;color:#000;padding-bottom:15px;text-shadow:1px 1px #f4e1e1}.about-us b{color:#d70544;text-transform:uppercase}.about-us a{background-color:#e91e63;color:#fff;padding:4px 6px;display:inline-block;text-shadow:1px 1px 2px #2f2f2f;border-radius:4px}.ftr-sm{padding:5px}.ftr-sm h4{font-size:18px;margin-bottom:4px;color:#d3003f}.ftr-sm p{color:#a50031;font-size:10px;line-height:1.4;text-shadow:1px 1px #f4e1e1}.pby-us{text-shadow:1px 1px #f4e1e1;color:#000;padding-top:2px;padding-bottom:1px}.refresh-btn{position:fixed;bottom:10px;right:10px}.bdr-b-0{border-width:0!important}.p-0{padding:0!important}@media only screen and (max-width:768px){}@media only screen and (max-width:500px){body{padding:2px 5px}.logo img{width:200px}.faq h4{font-size:15px}}@media only screen and (max-width:375px){}@media only screen and (max-width:320px){}/* end media 

</style>


<div style="color: rgb(0, 0, 0); overflow: auto; --darkreader-inline-color:#e8e6e3;" align="center" id="content" data-darkreader-inline-color="">
<div><table id="example" border="1" bgcolor="white" style="text-align: center; width: 100%; --darkreader-inline-bgcolor:#181a1b;" data-darkreader-inline-bgcolor="">
<tbody> <tr style="padding: 0">
<td colspan="13" style="padding:0">
<div style="background-color: rgb(242, 247, 37); text-transform: uppercase; --darkreader-inline-bgcolor:#b1b407;" data-darkreader-inline-bgcolor="">
<font size="5" color="#181ff2" data-darkreader-inline-color=""><?php echo $market; ?> Chart</font>
</div> </td></tr>
<tr style="background-color: rgba(242, 247, 37, 0.4); --darkreader-inline-bgcolor:rgba(177, 180, 7, 0.4);">
<td> <p align="center" class="Rhead"> DATE</p></td>
<?php

$results = query("select * from starline_timings where market='$market' order by str_to_date(close, '%H:%i')");
while($dd = fetch($results)) {
$close = $dd['close'];
$data['alltiming'][] = $close;
?>

<td valign="middle" align="center" class="Rhead"><?php echo date("g:i a", strtotime($close)); ?> </td>

<?php }

$getdates = array_keys($data['result']);

for($i = 0 ; $i < count($getdates);$i++){ 
?>

</tr><tr><td><span>
<?php echo $getdates[$i]; ?>
</span></td>

<?php for($ix = 0 ; $ix < count($data['alltiming']);$ix++){ 
    
    $timing = $data['alltiming'][$ix];
    
    
?>

<td> <span><?php if(isset($data['result'][$getdates[$i]][$timing])){ echo $data['result'][$getdates[$i]][$timing]['panna'];  } else { echo "***"; }?></span> <br> <span><?php if(isset($data['result'][$getdates[$i]][$timing])){ echo $data['result'][$getdates[$i]][$timing]['open'];  } else { echo "*"; }?></span></td>

<?php } ?>

</tr>

<?php } ?>


</tbody></table>


</div></div>

</body></html>