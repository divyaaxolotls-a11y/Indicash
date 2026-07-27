<?php
extract($_REQUEST);
include "con.php";



	require_once "../scrap/data/support/web_browser.php";
	require_once "../scrap/data/support/tag_filter.php";

	$htmloptions = TagFilter::GetHTMLOptions();
	
	$get_provider = mysqli_fetch_array(mysqli_query($con,"select data from settings where data_key='provider'"));
	if($get_provider['data'] == "dpboss"){
	    
	    
	    $url = $_REQUEST['url'];
	 
	} else {
	
	    $url = "https://spboss.net/".$_REQUEST['url'];
	
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

	  $table = $row;
	}
	

	$rows = $root->Find("body");
	
	
foreach ($rows as $row)
	{


	  $body = $row;
	}
	

echo str_replace($body,$table,$root);