<?php
include "con.php";

extract($_REQUEST);



	require_once "../scrap/data/support/web_browser.php";
	require_once "../scrap/data/support/tag_filter.php";

	$htmloptions = TagFilter::GetHTMLOptions();

$url = "https://dpboss.net/";
	$web = new WebBrowser();
	$result = $web->Process($url);

	if (!$result["success"])
	{
		echo "Error retrieving URL.  " . $result["error"] . "\n";
		exit();
	}

	if ($result["response"]["code"] != 200)
	{
		echo "Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] . "\n";
		exit();
	}

	$baseurl = $result["url"];

	$html = TagFilter::Explode($result["body"], $htmloptions);

	$root = $html->Get();
// class="satta-result"
	$rows = $root->Find("div.satta-result h4");
	$h5rows = $root->Find("div.satta-result h5");

// $sx = mysqli_query($con,"SELECT * FROM `result` where sn='1'");
// $x = mysqli_fetch_array($sx);
// $data = $x;

foreach ($rows as $row)
{
    $temp_h4[] = $row->GetInnerHTML();
}
	
foreach ($h5rows as $row)
{
    $temp_h5[] = $row->GetInnerHTML();
}


$get = mysqli_query($con,"select * from gametime_new");
while($xc = mysqli_fetch_array($get))
{
	$time = array_search($xc['market'], $temp_h4);
	$mrk['market'] = $xc['market'];
	$mrk['result'] = $temp_h5[$time];
    $data['result'][] = $mrk;
}

  
$dd = mysqli_query($con,"select sn,wallet,active,session,code from users where mobile='$mobile'");
$d = mysqli_fetch_array($dd);

$nt = mysqli_query($con,"select homeline from content where sn='1'");
$n = mysqli_fetch_array($nt);

if($d['code'] == "0")
{
    $code = $d['sn'].rand(100000,9999999);
    mysqli_query($con,"update users set code='$code' where mobile='$mobile'");
}
else
{
    $code = $d['code'];
}


$data['code'] = $code;
$data['wallet'] = $d['wallet'];
$data['active'] = $d['active'];
$data['session'] = $d['session'];
$data['homeline'] = $n['homeline'];


echo json_encode($data);