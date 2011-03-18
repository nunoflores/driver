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

require_once(DOKU_INC.'lib/plugins/driver/syntax/core.php');

$path = DOKU_BASE.'lib/plugins/driver/exe/jquery/';

//parse tags

session_start();
$trail = $_SESSION[DOKU_COOKIE]['trail'];
$tags = array();
foreach ($trail as $page) {
	$pagetags = p_get_metadata($page['page'],'subject');
	$tags = array_merge($tags, $pagetags);
	//print_r($tags);
}
//print_r($tags);
//WISH: Produce draggable tag cloud...look at cloud plug-in...
$tags = array_unique($tags);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Prune/Graft your Learning Path</title>
    <?php //tpl_metaheaders() ?>
	<link type="text/css" href="<?php print $path ?>css/smoothness/jquery-ui-1.8.7.custom.css" rel="Stylesheet" />	
    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/style.css' ?>" rel="Stylesheet" />	
    <link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/exe/exe_style.css' ?>" rel="Stylesheet" />	
	<link href="<?php print $path ?>plugins/star-rating/jquery.rating.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="<?php print $path ?>js/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="<?php print $path ?>js/jquery-ui-1.8.7.custom.min.js"></script>
	<script src='<?php print $path ?>plugins/star-rating/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
	<script src='<?php print $path ?>plugins/star-rating/jquery.rating.js' type="text/javascript" language="javascript"></script>
	<style>
		#effect {
			width: 100%;
			height: 300px;
		}
		.preview {
			width: 100%;
			height: 300px;	
			border=0px;		
		}
		#showSimilarPaths {
			width:100%;
			height:300px;
			overflow:auto;
			background-color: #f6f6f6;
		}
		</style>
		<script>
		function submitLP() {
			var lpath = $( "#finalPath" ).sortable('toArray');
			if (lpath.length == 0) {
				alert('No Learning Path to save.');
				return;
			}
			var tags = $( "#dropTags" ).sortable('toArray');
			var moretags = document.getElementById("othertags").value;
			if (moretags.length > 0) {
				moretags = moretags.split(" ");
				tags = tags.concat(moretags); 
			}
			if (tags.length == 0) {
				alert('Cannot save Learning Path with assigned tags.');
				return;
			}
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
		$(function() {
			$( "#learningPath" ).sortable({
				connectWith: "#finalPath"
			});
			
			$( "#finalPath" ).sortable({
				connectWith: "#learningPath",
				update: function(event, ui) { findSimilar() }
			});
			
			// binding callback to change events on final path to look for similar paths on database.
			
			function findSimilar() {
				// hide if not yet hidden.
				document.getElementById("showSimilarButton").value = 'Show';
				$ ('showSimilarButton').hide();
				$ ('showSimilarPaths').hide();
				document.getElementById("match").innerHTML = '';
				// searching
				document.getElementById("similarPaths").innerHTML = "Searching for similar paths...";
				var lpath = $( "#finalPath" ).sortable('toArray');
				$.post('findSimilarLPs.php', {lpath: lpath}, function(data) {
					var result = data.split('<count>');
					var values = result[0].split(',');
					var msg = '';
					if (values[0] == 0) msg = 'No similar paths found.';
					if ((values[1] == 1) && (values[0] == values[1])) {
						msg = 'Found a <b>match!</b>';
						document.getElementById("match").innerHTML = '(Will merge tags with matched path)';
					}
					if ((values[0] > 1) && (values[1] == 1)) {
						msg = 'Found a <b>match</b> and <b>'+(values[0]-1)+'</b> similar path(s).';
						document.getElementById("match").innerHTML = '(Will merge tags with matched path)';
					}
					if ((values[0] > 0) && (values[1] == 0)) msg = 'Found <b>'+(values[0])+'</b> similar path(s).';
					document.getElementById("similarPaths").innerHTML = msg;											
					if (values[0] > 0) {
						document.getElementById("showSimilarButton").style.display = 'inline';
						//document.getElementById("showSimilarPaths").innerHTML = result[1];
						//$( "#showSimilarPaths" ).show();
						$( "#showSimilarPaths" ).html(result[1]);
						//$( "#showSimilarPaths" ).hide();
						
					}				
				});
			}

			$( "div.dropTags" ).sortable({
				connectWith: "div.dragTags"
			});

			$( "div.dragTags" ).sortable({
				connectWith: "div.dropTags"
			});

			$( "#learningPath, #finalPath, #dropTags, #dragTags" ).disableSelection();
	
			function showSimilarPaths() {
				$( "#showSimilarPaths" ).toggle( "blind", {}, 200);
				var label = document.getElementById("showSimilarButton").value;
				if (label == 'Show') {
					document.getElementById("showSimilarButton").value = 'Hide';
				} else {
					document.getElementById("showSimilarButton").value = 'Show';
				}
			}

			$( "#showSimilarButton" ).click(function() {
				showSimilarPaths();
			});
		
			function runEffect() {
				$( "#effect" ).toggle( "slide", {direction: 'up'}, 200);
			};
			
			$( "#previewButton" ).click(function() {
				runEffect();
				var label = document.getElementById("previewButton").value;
				if (label == 'Show Previewer') {
					document.getElementById("previewButton").value = 'Hide Previewer';
				} else {
					document.getElementById("previewButton").value = 'Show Previewer';
				}
				return false;
			});
			$( "#effect" ).hide();
			$ ( "#showSimilarPaths" ).hide();
		});
		function onPreviewPage(pageid) {
			$.post('previewPage.php', {pageid: pageid}, function(data) {
				document.getElementById("preview").contentDocument.body.innerHTML="";
				document.getElementById("preview").contentDocument.write(data);					
			});
			return false;
		}
		</script>
    <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />
  </head>
  <body>
	<div class="demo">
    <table width=100% style="margin-bottom:0px" cellpadding=2px>
    <tr><td><h1> Learning Path </h1></td>
        <td valign=bottom><div align="right">
            <input id="previewButton" type="button" class="button" value="Show Previewer"/>
            <input type="button" class="button" value="Save Unprunned"/>
            <!--input type="button" class="button" value="Don't Save and Close" onclick="window.close()"/-->
        </div></td>
    </tr>
	<tr><td id="td-effect" colspan=2>
	<div id="effect" class="ui-widget-content ui-corner-all">
		<iframe id="preview" name="preview" class="preview">
		</iframe>
	</div>
	</td></tr>
	</table>
	<table><tr><td id="td-lp" width=100%>
	<div id="learningPath" class='droptrue' >
    <?php
		session_start();
		$trail = $_SESSION[DOKU_COOKIE]['trail'];
		//print_r($trail);
		$data = processTrailArrayForPrinting($trail);
		foreach ($data as $page) {
			print printTrailPage($page,'','onPreviewPage');
		}	
		?>
	</div>
	</td></tr>
	</table>
	<hr>

	<h2>The Way to Enlightenment</h2>
	<div align="right"><i>"I only show you the door...you're the one who has to walk through it..." </i>Morpheus, <i>in "The Matrix"</i></div>
	<div id="finalPath" class='droptrue' style="width:100%;height:90px">
	</div>	
	<div align="right">
		<table><tr><td style="padding-top:5px">
		<div id="similarPaths" style="float:left">No similar paths found.</div>
		</td><td>
		<input id="showSimilarButton" style="display:none" type="button" class="button" value="Show">
		</td></tr></table>
	</div>
	<div id="showSimilarPaths" class="showSimilarPaths">
	</div>
	<h2>Tagging</h2>
    <table width=100% style="margin-bottom:0px" cellspacing=0>
	    <tr><td colspan=2>Tags for this learning path:</td><td>Tags on the way...</td></tr>
		<tr>
			<td width=50% colspan=2>
				<div id="dropTags" class='dropTags'></div>
			</td>
			<td width=50% colspan=2 style="padding-left:5px">
				<div id="dragTags" class="dragTags">
					<?php
						foreach ($tags as $tag) {
							print '<div id="'.$tag.'" class="tag">'.$tag.'</div>';
						}
					?>
				</div>
			</td>
		</tr>
		<tr>
			<td rowspan=2 width="10%" valign=top style="padding-top:6px">Other tags: </td>
			<td><input id="othertags" type="text" name="tags" value="" style="width:100%"></td>
			<td></td>
			<td align=right valign=bottom><span id="match" style="font-style:italic;clear:none;font-size:9px;padding-right:5px"></span><input type="button" class="button" value="Save" onclick="submitLP()"></input></td>
		</tr>
		<tr>
			<td valign=top style="font-size:9px"><i>(space separated)</i></td>
		</tr>
	</table>
	<br clear="both" />

	</div><!-- End demo -->
  </body>
</html>


<?php 

// example of a page's metadata... 
$trash = 
'Array ( [date] => 
	Array ( [created] => 1289405092 
			[modified] => 1294163508 ) 
		[creator] => Nuno Flores 
		[last_change] => Array ( 
			[date] => 1294163508 
			[ip] => ::1 
			[type] => E 
			[id] => start 
			[user] => admin 
			[sum] => [Framework specifics] 
			[extra] => ) 
		[contributor] => Array ( 
			[admin] => Nuno Flores ) 
			[title] => Welcome to the Framework Learning Scaffold Wiki! 
			[description] => Array ( 
				[tableofcontents] => Array ( 
					[0] => Array ( 
						[hid] => welcome_to_the_framework_learning_scaffold_wiki 
						[title] => Welcome to the Framework Learning Scaffold Wiki! 
						[type] => ul [level] => 1 ) 
					[1] => Array ( 
						[hid] => getting_started 
						[title] => Getting Started 
						[type] => ul 
						[level] => 2 ) 
					[2] => Array ( 
						[hid] => what_is_flake_fwname 
						[title] => What is FLAKE//// ? 
						[type] => ul 
						[level] => 3 ) 
					[3] => Array ( 
						[hid] => what_do_you_want_to_learn_today 
						[title] => What do you want to learn today ? 
						[type] => ul 
						[level] => 2 ) 
					[4] => Array ( 
						[hid] => framework_generics 
						[title] => Framework generics 
						[type] => ul 
						[level] => 3 ) 
					[5] => Array ( 
						[hid] => framework_specifics 
						[title] => Framework specifics 
						[type] => ul 
						[level] => 3 ) 
					) 
				[abstract] => Hi there! This is a scaffold wiki for guiding your learning on a specific framework in a generic way. For demonstration purposes, lets assume the framework is called FLAKE (Framework Learning Active Knowledge Example). So feel free to let your learning styles wander off througout this wiki and give us feedback on how you think things could be improved. ) 
				[relation] => Array ( 
					[references] => Array ( [tag:welcome] => [tag:home] => [tag:intro] => [sf] => [if] => [ef] => [uad] => [uta] => [udi] => [usc] => [start:another_page] => 1 ) 
					[firstimage] => flake_logo.png ) 
				[subject] => Array ( [0] => welcome [1] => home [2] => intro ) 
				[internal] => Array ( 
					[cache] => 1 
					[toc] => 1 ) )';
					
?>
