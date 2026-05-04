<?php
extract($_REQUEST);


	require_once "../../scrap/data/support/web_browser.php";
	require_once "../../scrap/data/support/tag_filter.php";

	$htmloptions = TagFilter::GetHTMLOptions();
	
	if($chart == "main_panel"){
	    $url = "https://dpboss.net/main-bazar-panel-chart.php";
	} else if($chart == "time_panel"){
	    $url = "https://dpboss.net/time-bazar-penal.php";
	} else if($chart == "milan_day_panel"){
	    $url = "https://dpboss.net/milan-day-penal.php";
	} else if($chart == "milan_night_panel"){
	    $url = "https://dpboss.net/milan-night-penal.php";
	} else if($chart == "kalyan_panel"){
	    $url = "https://dpboss.net/kalyan-penal-chart.php";
	} else if($chart == "time_jodi"){
	    $url = "https://dpboss.net/time-bazar-Chart.php";
	} else if($chart == "main_jodi"){
	    $url = "https://dpboss.net/main-bazar-jodi-chart.php";
	} else if($chart == "milan_day_jodi"){
	    $url = "https://dpboss.net/milan-day-chart.php";
	} else if($chart == "milan_night_jodi"){
	    $url = "https://dpboss.net/milan-night-chart.php";
	} else if($chart == "kalyan_jodi"){
	    $url = "https://dpboss.net/kalyan-chart.php";
	} 
	
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