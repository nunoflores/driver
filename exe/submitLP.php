<?php

	require_once ('db.php');
	require_once ('php-txt-db-api/txt-db-api.php');

	$learningPath = $_REQUEST['lpath'];
	if (!isset($learningPath)) die;

	$tagsArray = $_REQUEST['tags'];	
		
	driverdb_saveLP($learningPath, $tagsArray) ;
	
/*
	$f = fopen('lps.txt','a');
	$content = print_r($learningPath,true);
	//$content .= print_r($tags,true);
	fwrite($f,$content,strlen($content)); 
	fclose($f);
*/
?>