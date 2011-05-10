<?php

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../../../');

require_once ('db.php');
require_once ('php-txt-db-api/txt-db-api.php');

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'lib/plugins/driver/syntax/core.php');

function driver_getRecommendation($lastJump) {

	$currentPage = $lastJump['page'];
	if (isset($lastJump['section'])) {
		$currentPage = $lastJump['page'].'#'.sectionId($lastJump['section']).'='.$lastJump['section'];
	}

	$output = '';
	$output .= '<div id=driver_recommendation class=driver_rbox>';
	//$output .= '<table class=driver_rbox_table>';
	//$output .= '<tr><td class=driver_rbox_table_title_column>Try these...</td></tr>';
	//$output .= '<tr><td class=driver_rbox_table_column >';	
	if (!page_exists($currentPage)) {
		$output .= '<div align="center" style="font-style:italic"> nowhere yet...</div>';
		$output .='</div>';
		return $output;
	}
	
	error_log("currentPage: ".print_r($currentPage,true));
	
	$recommend = driverdb_getMostNextSteps($currentPage);

	error_log("recommend: ".print_r($recommend,true));

	foreach ($recommend as $page => $count) {
		
		unset($trailPage);
						
		// is it section?
		$pageParts = explode("#",$page);
		if (sizeof($pageParts) > 1) {
			//its section, then parse title
			$sectionParts = explode("=",$pageParts[1]);
			$trailPage['section'] = $sectionParts[1];			
		}
				
		$trailPage['id'] = $pageParts[0];
		$trailPage['name'] = trimPageTitle(get_first_heading($pageParts[0]),40);
				
		$output .= printTrailPage($trailPage,'_parent','','','trail_page_recommend',$count);
	}
		
	//$output .= '</td></tr>';
	//$output .='</table>';	
	$output .='</div>';
	return $output;						
	
}

function prettyPrintLastJump($jump) {
	
	error_log($jump, true);

	$label = trimPageTitle(get_first_heading($jump['page']),40);
	if (isset($jump['section'])) 
	 	$label = $jump['section'].' <sub>('.substr($label,0,5).'...)</sub>';
 	return $label;
}

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
	  <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/style.css' ?>" rel="Stylesheet" />	
	    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/exe/exe_style.css' ?>" rel="Stylesheet" />	
	    <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />
	  </head>
	  <body>
	<?php
	   	session_start();
		$trail = $_SESSION[DOKU_COOKIE]['trail'];
		$last = $trail[sizeof($trail)-1];
		print "<div align='right' style='font-style:italic;padding-bottom:5px'>From <b>".prettyPrintLastJump($last)."</b>, learners usually go...</div>";
		print '<div id=driver_recommendation class=driver_rbox>';
		print driver_getRecommendation($last);
		print '</div>';
		?>
	</body>
	</html>