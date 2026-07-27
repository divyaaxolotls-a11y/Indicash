<?php
extract($_REQUEST);


	require_once "../../scrap/data/support/web_browser.php";
	require_once "../../scrap/data/support/tag_filter.php";

	$htmloptions = TagFilter::GetHTMLOptions();

	$url = "https://dpboss.net/main-bazar-jodi-chart.php";
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

	$rows = $root->Find("table");
	
	
foreach ($rows as $row)
	{
// 	    $da = str_replace('<img src="images/www.gif" alt="satta King">','',$row);
// 	  //    $da = str_replace('<img src="images/www.gif" alt="satta King">','-XXX-',$row);
// 	    $da = str_replace('</p>','',$da);
// 	    $da = str_replace('<p class="text_2">','',$da);

	  $table = $row;
	}
	

	$rows = $root->Find("body");
	
	
foreach ($rows as $row)
	{
// 	    $da = str_replace('<img src="images/www.gif" alt="satta King">','',$row);
// 	  //    $da = str_replace('<img src="images/www.gif" alt="satta King">','-XXX-',$row);
// 	    $da = str_replace('</p>','',$da);
// 	    $da = str_replace('<p class="text_2">','',$da);

	  $body = $row;
	}
	

echo str_replace($body,$table,$root);