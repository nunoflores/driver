<?php
/**
 * This is the template for the snippets popup
 * @author Michael Klier <chi@chimeric.de>
 */


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
require_once(DOKU_INC.'inc/pluginutils.php');

require_once(DOKU_INC.'lib/plugins/driver/syntax/core.php');
require_once(DOKU_INC.'lib/plugins/driver/exe/db.php');
require_once (DOKU_INC.'lib/plugins/driver/exe/php-txt-db-api/txt-db-api.php');
require_once('printUtils.php');

$path = DOKU_BASE.'lib/plugins/driver/exe/jquery/';


// gets driver tags from database

$tags = driverdb_getAllTags();
$availableTags = '';
foreach ($tags as $tag) {
	$availableTags .= '"'.$tag.'",';
}
$availableTags = substr($availableTags, 0, -1); // remove last comma
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Searching for a Learning Path</title>
    <link type="text/css" href="<?php print $path ?>css/smoothness/jquery-ui-1.8.7.custom.css" rel="Stylesheet" />	
    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/style.css' ?>" rel="Stylesheet" />	
    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/exe/exe_style.css' ?>" rel="Stylesheet" />	
	<link href="<?php print $path ?>plugins/star-rating/jquery.rating.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="<?php print $path ?>js/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="<?php print $path ?>js/jquery-ui-1.8.7.custom.min.js"></script>
	<script src='<?php print $path ?>plugins/star-rating/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
	<script src='<?php print $path ?>plugins/star-rating/jquery.rating.js' type="text/javascript" language="javascript"></script>
	
	<style>
		.effect {
			width: 100%;
			height: 300px;
			display:none;
		}
		.preview {
			width: 100%;
			height: 300px;	
			border=0px;		
 		}
	</style>
	<script>
		function submitLP() {
			var lpath = $( "#finalPath" ).sortable('toArray');
			var tags = $( "#dropTags" ).sortable('toArray');
			var moretags = document.getElementById("othertags").value;
			moretags = moretags.split(" ");
			tags = tags.concat(moretags);
			$.post('submitLP.php', {lpath: lpath, tags: tags}, function(data) {});
			window.close();
		}
		function applyRating(data) {
			if (data == 0) return;
			var r = data.split("=");
			var rating = r[1];
			var l = r[0].split("_");
			var user = l[0];
			var lp = l[2];
			$.post('applyRating.php', {lpuser: user, lp: lp, lprating: rating} , function(data) {
				location.reload(true);
			});			
		}
			
		function onPreviewPage(previewer, pageid) {
			$.post('previewPage.php', {pageid: pageid}, function(data) {
				document.getElementById(previewer).contentDocument.body.innerHTML="";
				document.getElementById(previewer).contentDocument.write(data);					
			});
			return false;
		}
		
		function slidingPreviewer(id) {
				$(id).toggle( "slide", {direction: 'up'}, 200);
		}
		
		function togglePreviewer(div, button) {
				slidingPreviewer(div);
				var label = button.value;
				if (label == 'Show Previewer') {
					button.value = 'Hide Previewer';
				} else {
					button.value = 'Show Previewer';
				}
		}
		
		$(function() {
			var availableTags = [<?php print $availableTags;?>];
			function split( val ) {
				return val.split( /\s\s*/ );
			}
			function extractLast( term ) {
				return split( term ).pop();
			}

			$( "#tags" )
				// don't navigate away from the field on tab when selecting an item
				.bind( "keydown", function( event ) {
					if ( event.keyCode === $.ui.keyCode.TAB &&
							$( this ).data( "autocomplete" ).menu.active ) {
						event.preventDefault();
					}
				})
				.autocomplete({
					minLength: 0,
					source: function( request, response ) {
						// delegate back to autocomplete, but extract the last term
						response( $.ui.autocomplete.filter(
							availableTags, extractLast( request.term ) ) );
					},
					focus: function() {
						// prevent value inserted on focus
						return false;
					},
					select: function( event, ui ) {
						var terms = split( this.value );
						// remove the current input
						terms.pop();
						// add the selected item
						terms.push( ui.item.value );
						// add placeholder to get the space at the end
						terms.push( "" );
						this.value = terms.join( " " );
						return false;
					}
				});				
		});
		</script>
    <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />
  </head>
  <body>
	<div>
     <h1> Searching for a Learning Path... </h1></td><td valign=bottom></td></tr></table>
	<hr>
	<div>
		<form>
		<label for="tags">Tags: </label>
		<input id="tags" name="tags" size="50" />
		<input id="searchButton" type="submit" value="Search"/>
		</form>
	</div>
	<hr>
	<table><tr><td width=100% style="vertical-align:top">
	<div id="searchResults">
		<?php 
			//print p_render('xhtml',p_get_instructions('[[newpage]]'),$info);
			
			if (!isset($_REQUEST['tags'])) {
				print "no results yet...";
			} else {
				$tags = $_REQUEST['tags'];
				print '<h3>Search results for "'.trim($tags).'"</h3>';
				$tagset = explode(' ',trim($tags));
				//buildSearchResultsPrintOut(driverdb_searchLPs($tagset),$tagset,true,true,0,'','onPreviewPage');
				buildSearchResultsPrintOutWithPreviewer(driverdb_searchLPs($tagset),$tagset,true,true,0,'','onPreviewPage');
			}
		?>
	</div>
	</td></tr></table>
	<br clear="both" />

	</div>
  </body>
</html>