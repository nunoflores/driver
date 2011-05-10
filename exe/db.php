<?php

//
// Driver Plug-in database configuration file
// Author: Nuno Flores, 2001-01-15
// version: 1.0
//
// This plug-in uses the PHP Text Database API () and it needs to configure
// the following two global constants for the file "txt-db-api-php".
//
// By defining them here, one can upgrade to future versions of the
// API without having to tamper with anything. 
//
// JUST MAKE SURE THIS FILE IS INCLUDED BEFORE INCLUDING THE API
//
// All remaining functions are "helpers".
//

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../../../');

// Directory where the API is located (Server Path, no URL)
$API_HOME_DIR = DOKU_INC."lib/plugins/driver/exe/php-txt-db-api/";
	
//$API_HOME_DIR="c:\\programme\\apache\\htdocs\\php-api\\";	

// Directory where the Database Directories are located
// THIS IS NOT THE FULL PATH TO A DATABASE, ITS THE PATH
// TO A DIRECTORY CONTAINING 1 OR MORE DATABASE DIRECTORIES
// e.g. if you have a Database in Directory /home/website/test/TestDB
// you must set this property to /home/website/test/ 		

$DB_DIR = DOKU_INC."lib/plugins/driver/db/";			
//$DB_DIR="c:\\programme\\apache\\htdocs\\php-api-tests\\";			

define("API_HOME_DIR" ,$API_HOME_DIR);
define("DB_DIR" ,$DB_DIR);

// Not quite the SINGLETON, but close...more like a TENNENT
class DatabaseAccessor {
	private static $instance;
	
	private function __construct() {
	}
	
	public static function getInstance() {
		if (!self::$instance) 
		    { 
				if (!file_exists(DB_DIR . "driver_db")) {
					$db = new Database(ROOT_DATABASE);
					$db->executeQuery("CREATE DATABASE driver_db");
				}	
		        self::$instance = new Database("driver_db"); 
		    } 

		return self::$instance;
	}
}

// builds database infrastructure to make sure all is there.
function driverdb_startDb() {
	
	$db = DatabaseAccessor::getInstance();
	
	// create tables
	if (!file_exists(DB_DIR . "driver_db/lp.txt"))
		$db->executeQuery("CREATE TABLE lp (id inc, tags str, rank str)");

	if (!file_exists(DB_DIR . "driver_db/step.txt"))
		$db->executeQuery("CREATE TABLE step (lp int, ord int, pageid str, flag str)");

	if (!file_exists(DB_DIR . "driver_db/rating.txt"))
		$db->executeQuery("CREATE TABLE rating (lp int, user str, rating int)");

	return $db;
}

// return the rating a user has given a certain learning path
function driverdb_getRatingByUser($lp,$user) {	
	$db = driverdb_startDb();
	$result = $db->executeQuery("SELECT rating AS r FROM rating WHERE lp = ".$lp." AND user LIKE '".$user."'");
	if ($result->getRowCount() > 0) {
		return $result->getValueByName(0,'r');
	}
	return 0;
}

// returns the rating of a learning path
function driverdb_getRating($lp) {
	// FIXME: currently it will calculate everytime it is requested.
	// May be later it will be updated directly on the database at each rating operation.
	
	$db = driverdb_startDb();
	$result = $db->executeQuery("SELECT AVG(rating) AS r FROM rating WHERE lp = ".$lp);
	if ($result->getRowCount() > 0) {
		return $result->getValueByName(0,'r');
	}
	return 0;
}

// updates a user's rating on a learning path
function driverdb_applyRating($lp, $user, $rating) {
	$db = driverdb_startDb();
	// check if exists
	$exists = $db->executeQuery("SELECT * FROM rating WHERE lp = ".$lp." AND user LIKE '".$user."')");
	if ($exists->getRowCount() == 0) { // rating doesn't exist 	
		$db->executeQuery("INSERT INTO rating(lp,user,rating) VALUES (".$lp.",'".$user."',".$rating.")");
	} else {
		$db->executeQuery("UPDATE rating SET rating=".$rating." WHERE lp = ".$lp." AND user LIKE '".$user."')");
	}
}

// returns an array with all the tags in the database (no duplicates)
function driverdb_getAllTags() {
	$db = driverdb_startDb();
	$result = $db->executeQuery("SELECT tags FROM lp");
	$tags = array();
	while ($result->next()) {
		$moretags = $result->getCurrentValues();
		$moretags = explode(" ",$moretags[0]);
		$tags = array_merge($tags, $moretags);
		$tags = array_unique($tags);
	}
	return $tags;
}

// Returns the LPs search result by tags.

// The results will be presented as follows: 
//  - LPs with all the tags in $tagset are returned first.
//  - Then all the LPs that have a subset of $tagset, ordered by match number (the higher matches come first).
//  - All other LPs (which don't have at least on tag on $tagset) are discarded.

function driverdb_searchLPs($tagset) {
	$db = driverdb_startDb();	

	// calculating tag weight (the first tag is heavier that the second and so forth...)
	$ntags = count($tagset);
	$tagWeight = array();
	for ($i = 0 ; $i < $ntags ; $i++) {
		$tagWeight[$i] = pow(2,$ntags-$i); 
	}
	
	// To save time later on fetching tags again...	
	$tagsMap = array();
		
	// counting tags per LP
	$lps = $db->executeQuery("SELECT * FROM lp");
	$finalWeight = array();
	while ($lps->next()) {
		$weight = 0;
		$tagIndex = 0;
		$tags = $lps->getCurrentValueByName("tags");
		foreach ($tagset as $tag) {
			if (strpos($tags,$tag) > -1) 
				$weight += $tagWeight[$tagIndex]; //accumulate weight
			$tagIndex++;
		}
		if ($weight > 0) {
			$id = $lps->getCurrentValueByName("id");
			$finalWeight[$id] = $weight;
			$tagsMap[$id] = $tags;
		}
	}	
	//order lps by weight, in descending order
	arsort($finalWeight);
	
	// fetch steps and re-attach tags
	$return = array();
	foreach ($finalWeight as $key=>$value) {
		$path = driverdb_getLP($key,$tagsMap[$key]);
		$return[] = $path;
	}
	// return steps
	return $return;
}

// returns an lp with the id ($lpid) as an array
// $tags allows to save time if previously fetched.
function driverdb_getLP($lpid,$tags='') {

	$db = driverdb_startDb();

	//fetch tags if not set
	if (isset($tags)) {
		$lp = $db->executeQuery("SELECT tags FROM lp WHERE id = ".$lpid);
		while($lp->next()) { // there should be only one result
			$tags = $lp->getCurrentValueByName("tags");
		}
	}

	$path = array();
	$path['id'] = $lpid;
	$path['tags'] = $tags;
	
	$path['path'] = array();
	$steps = $db->executeQuery("SELECT * FROM step WHERE lp = ".$lpid." ORDER BY ord");
	while($steps->next()) {
		$step = array();
		// parse pageId and sectionId
		$id = explode("#",$steps->getCurrentValueByName("pageid"));
		$step['page'] = $id[0];
		$step['section'] = $id[1];
		//$step['page'] = $steps->getCurrentValueByName("pageid");
		$step['flag'] = $steps->getCurrentValueByName("flag");
		$path['path'][] = $step;			
	}
	
	return $path;
}
// returns similar LPs 
//
// A two similar LPs have the same pages in the same order, but might have different flagging.
//
// search heuristics:
//  (1) get all the LP ids that have steps that are equal to first page of $lp and order is 0.
//  (2) filter out all of those that the next page is not of order 1.
//  (3) continue until all $lp pages are visited. At the end, the remaining LPs are the similar ones.
function driverdb_getSimilarLPs($lp, &$result, &$equal){
	
	$db = driverdb_startDb();	
	
	// $lp should be an array of wiki page ids.
	$order = 0;
	$pool = array();
	foreach ($lp as $pageid) {
		$resultSet = $db->executeQuery("SELECT lp, ord as o FROM step WHERE o = ".$order." AND pageid LIKE '".$pageid."'");
		while($resultSet->next()) {
			$id = $resultSet->getCurrentValueByName("lp");
			if (!isset($pool[$id])) {
				$pool[$id] = 1;				
			} else {
				$pool[$id]++;								
			}
		}		
		$order++;
	}
	
	// get lps id only that have more hits
	$lps = array_keys($pool,$order);

	// see if there in already equal lp (order = lp size) 
	foreach ($lps as $id) {
		$resultSet = $db->executeQuery("SELECT count(*) as size FROM step WHERE lp = ".$id);
		while($resultSet->next()) {
			$size = $resultSet->getCurrentValueByName("size");
			if ($size == $order) {
				$equallp = $id;
			} 
		}
		if (isset($equallp)) break;
	}
	
	// fetch equal lp 
	// for now only return id of LP
	$equal = $equallp;
	//if (isset($equallp)) {
	//	$equal = array();
	//	$equal = driverdb_getLP($equallp);
	//}
		
	// build lps 
	$result = array();
	foreach ($lps as $lp) {
		$result[] = driverdb_getLP($lp);
	}
	return count($result);
}

// returns id of matching lp already in the database
function driverdb_getLPMatchId($lp) {

		$db = driverdb_startDb();	

		// lp should be array of pageids|flag.	
		$order = 0;
		foreach ($lp as $pageid) {
			$data = explode('|',$pageid);
			$more = array();
			error_log(print_r($data,true));
			$resultSet = $db->executeQuery("SELECT lp, ord as o FROM step WHERE o = ".$order." AND pageid LIKE '".$data[0]."'");
			while($resultSet->next()) {
				$id = $resultSet->getCurrentValueByName("lp");
				$more[] = $id;
			}		
			if (!isset($exact)) {
				$exact = array();
				$exact = $more;
			} else {
				$exact = array_intersect($exact, $more);
			}
			$order++;
		}
		if (count($exact) > 0) return $exact[0];
		return -1;
}
	
// saves a new learning path to the database
function driverdb_saveLP($learningPath, $tagsArray) {
	//Start database
	$db = driverdb_startDb();
	
	// does an equal lp already exists? if so, merge tags
	if (($id = driverdb_getLPMatchId($learningPath)) > 0) {
		// fetch tags
		$resultSet = $db->executeQuery("SELECT tags FROM lp WHERE id = ".$id);
		while($resultSet->next()) {
			$tags = explode(" ",$resultSet->getCurrentValueByName("tags"));
		}
		// merge tags
		$finaltags = array_unique(array_merge($tags, $tagsArray));
		$tags = implode(" ", $finaltags);
		// save and return
		$db->executeQuery("UPDATE lp SET tags='".$tags."' WHERE id = ".$id);
		return;
	}
	
	//insert learning path into database

	// processin tags 
	$tags = implode(" ",$tagsArray);

	// storing learning path "header"
	$db->executeQuery("INSERT INTO lp (tags, rank) VALUES ('".$tags."','0.0')");

	// get last inserted id (from lp) from database for foreign key purpose...
	$lp = $db->getLastInsertId();

	// storing steps
	$order = 0;
	foreach ($learningPath as $step) {
		$data = explode('|',$step);
		$db->executeQuery("INSERT INTO step(lp,ord,pageid,flag) VALUES (".$lp.",".$order.",'".$data[0]."','".$data[1]."')");
		$order++;
	}
}

// returns the next steps most LPs take from a particular page ($pageId)
function driverdb_getMostNextSteps($pageId) {
	$db = driverdb_startDb();	

	error_log("DB_DIR:".DB_DIR."\nAPI_HOME_DIR:".API_HOME_DIR);

	error_log("query: "."SELECT lp , ord FROM step WHERE pageid='".$pageId."'");
	// Get all LPs where pageId is.
	
	$allLPs = $db->executeQuery("SELECT * FROM step WHERE pageid = '".$pageId."'");
	
	// Fetch all next steps
	while($allLPs->next()) {
		$lp = $allLPs->getCurrentValueByName("lp");
		$order = $allLPs->getCurrentValueByName("ord");
		$result = $db->executeQuery("SELECT * FROM step WHERE lp = ".$lp." AND ord = ".($order+1));
		while ($result->next()) {
			$nextStep = $result->getCurrentValueByName("pageid");
			if (isset($recommend[$nextStep])) {
				$recommend[$nextStep]++;
			} else {
				$recommend[$nextStep] = 1;
			}
		}
	}	
	arsort($recommend);
	return $recommend;
}

// DEBUG function just for printing out the database contents.
function driverdb_printout() {
	$db = driverdb_startDb();	
	
	print "<h2>recommend</h2>";
	
	print_r(driverdb_getMostNextSteps('start'));
	
	print "<h2>test</h2>";
	$lp = 3;
	$order = 0; 
	$resultSet = $db->executeQuery("SELECT pageid, ord AS x FROM step WHERE lp=2 AND x=1");
	$resultSet->dump();

	print "<h2>lp</h2>";
	$resultSet = $db->executeQuery("SELECT * FROM lp");
	$resultSet->dump();
	
	print "<h2>steps</h2>";
	$resultSet = $db->executeQuery("SELECT * FROM step");
	$resultSet->dump();

	print "<h2>rating</h2>";
	$resultSet = $db->executeQuery("SELECT * FROM rating");
	$resultSet->dump();

}

?>
