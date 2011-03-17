<?php
	require_once ('db.php');
	require_once ('php-txt-db-api/txt-db-api.php');

	$user = $_REQUEST['lpuser'];
	if (!isset($user)) die;

	$rating = $_REQUEST['lprating'];
	if (!isset($rating)) die;

	$learningPath = $_REQUEST['lp'];
	if (!isset($learningPath)) die;

	driverdb_applyRating($learningPath, $user, $rating);

/*
	$f = fopen('lps.txt','a');
	$content = print_r($_REQUEST,true);
//	$content = print_r($learningPath,true);
//	$content .= print_r($user,true);
//	$content .= print_r($rating,true);
	//$content .= print_r($tags,true);
	fwrite($f,$content,strlen($content)); 
	fclose($f);
*/
?>