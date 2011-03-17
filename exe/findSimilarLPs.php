<?php

	require_once ('db.php');
	require_once ('php-txt-db-api/txt-db-api.php');
	require_once ('printUtils.php');

	$learningPath = $_REQUEST['lpath'];
	if (!isset($learningPath)) die;
	
	// expecting array of "pageid|flag"
	// strip flags
	$lps = array();
	foreach ($learningPath as $page) {
		$el = explode('|',$page);
		$lps[] = $el[0];
	}
	
	$r = driverdb_getSimilarLPs($lps, $result, $equal);
		
	$same = (isset($equal))?1:0;

	print $r.','.$same."<count>";
	
	// for now, just print the number of lps found...
	$tagset = array();
	buildSearchResultsPrintOut($result,$tagset, false, false, $equal);
	
	//print print_r($result,true);
		
/*
	$f = fopen('lps.txt','a');
	$content = print_r($learningPath,true);
	//$content .= print_r($tags,true);
	fwrite($f,$content,strlen($content)); 
	fclose($f);
*/
?>
