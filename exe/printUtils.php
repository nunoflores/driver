<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../../../');

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/lang/en/lang.php');
require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');
require_once(DOKU_INC.'inc/media.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/template.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'inc/parserutils.php');

require_once(DOKU_INC.'lib/plugins/driver/syntax/core.php');
require_once(DOKU_INC.'lib/plugins/driver/exe/db.php');
require_once (DOKU_INC.'lib/plugins/driver/exe/php-txt-db-api/txt-db-api.php');

// returns xhtml printout of "show rating" table element.
// It takes a float between 0 and 5 and round to halves, showing it in the rating stars.
function printShowRatingTable($lp, $rating) {
	$result = '<table cellspacing=0 style="font-size:9px;">';
	$result .= '<tr><td>Overall rating:</td>';
	$result .= '<td>';
	for ($i = 1 ; $i < 11 ; $i++) {
		$checked = (round($rating * 2) == $i) ? 'checked="checked"' : '';
		$result .='<input name="lpID_'.$lp.'_showRating" type="radio" class="star {split:2}" disabled="disabled" '.$checked.'/>';
	}
	$result .= '</td><td>('.$rating.')';
	$result .= '</td></tr></table>';
	return $result;
}

// returns xhtml printout of "applying rating" table element
function printApplyRatingTable($lp, $user) {
	$result = '<div style="float:right;font-size:9px;">';
	$result .= '<table><tr><td>Your rating:</td>';
	$result .= '<td style="padding-top:2px"><form>';
	for ($i = 1 ; $i < 6 ; $i++) {
		$checked = (driverdb_getRatingByUser($lp, $user) == $i) ? 'checked="checked"' : '';
		$result .='<input name="'.$user.'_ratelpID_'.$lp.'" type="radio" class="star" value="'.$i.'" '.$checked.'/>';
	}
	$result .= '</form></td>';
	$result .= '<td><input style="font-size:9px" type="button" value="Rate" onClick="applyRating($(this.form).serialize() ||  0)"/></td>';
	$result .= '</table></div>';
	return $result;
}

// prints out the results of the an LPs search
function buildSearchResultsPrintOut($results, $tagsSearched, $count=true, $applyRating=true, $match=0, $target='', $callback='', $previewing=false) {

	// no results
	if (count($results) == 0) {
		if ($count) print '<b>Found 0 learning paths.<br/>';
		return;
	}

	// results count
	if ($count) print 'Found <b>'.count($results).'</b> learning paths.<br/>';

	// getting logged in user to check rating
	$user = $_SESSION[DOKU_COOKIE]['auth']['user'];

    // produce output
	$resultsCounter = 0;
	foreach ($results as $contents) {
	
	    // style for exact match found
		$isMatch='';
		if ($match == $contents['id']) $isMatch='style="border: 2px solid #333333"';
	
	    // processing resulting path into an array of metadata.
		$pages = processTrailArrayForPrinting($contents['path']);
	
	    // composing result line using an html table
		print '<table cellpadding=0 cellspacing=0 width=100% >';
		
		
		print '<tr>';

		// printing tags		
		print '<td><div style="font-size:9px;padding-top:5px">tags: ';

		$tags = explode(" ", trim($contents['tags']));
		foreach ($tags as $tag) {
			// highlight tags if match searched
			if (array_search($tag, $tagsSearched) > -1) {
				print '<b>'.$tag.'</b> ';
			} else {
				print $tag.' ';

			}
		}
		
		// printing overall rating
		print '</div></td><td align=right style="padding-top:5px">';
		print printShowRatingTable($contents['id'],driverdb_getRating($contents['id']));	
		print '</td>';
		
		print '</tr>';
		
		
		// path printing
		print '<tr><td colspan=2><div class="searchResultBar" '.$isMatch.'>';
		foreach ($pages as $page) 
			if ($previewing) {
				print printTrailPage($page, $target, $callback, 'preview'.$resultsCounter);	
			} else {
				print printTrailPage($page, $target, $callback);					
			}
		print '</div></td></tr>';	
		
		
		print '<tr>';
		
		// toggle previewer 
		if ($previewing) {
			print '<td>';
			print '<input style="font-size:9px" id="previewButton'.$resultsCounter.'"';
			print ' type="button" class="button" value="Show Previewer"';
			print ' onClick="togglePreviewer(effect'.$resultsCounter.', previewButton'.$resultsCounter.')">';
			print '</td>';
		}
			
		// if user can rate result is enabled ($applyRating)
		if ($applyRating) {
			print '<td>';
			print '<form action="applyRating.php">';
			print printApplyRatingTable($contents['id'], $user);
			print '</form></td>';
		}
	
		print '</tr>';
		
		// printing previewer (hidden)
		if ($previewing) {
			print '<tr><td id="td-effect" colspan=2>
					<div id="effect'.$resultsCounter.'" class="effect">
						<iframe id="preview'.$resultsCounter.'" name="preview" class="preview">
						</iframe>
					</div>
				</td></tr>';
		}
		
		print '</table>';
		
		$resultsCounter++;
	}
}



?>