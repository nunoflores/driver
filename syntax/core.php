<?php
	function get_first_heading($page) {
		$heading = p_get_first_heading($page);
		if (!isset($heading)) $heading = $page;
		return $heading;
	}

	function trimPageTitle($title, $maxchars=19) {
		if (strlen($title) > $maxchars)	return substr($title,0,$maxchars).'...';
		return $title;
	}
	
	function isLoggedIn() {
		return isset($_SESSION[DOKU_COOKIE]['auth']['user']);
		
	}
	
	function driver_metaheaders() {
		// show only if user is logged in.
		
		if (!isLoggedIn()) return;

		// this will go into the <head> section of every wiki page.
		// REFACTOR: right now it is inserted through the template, but there should be a more seamless way...
		
		$jquery_path = DOKU_BASE.'lib/plugins/driver/exe/jquery/';
		$mbExtruder_path = $jquery_path.'plugins/jquery.mb.extruder.2.1/';
		
		// css for mb.extruder ui
		print '<link href="'.$mbExtruder_path.'css/mbExtruder.css" media="all" rel="stylesheet" type="text/css">';
		
	 	//jquery
		print '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>';

		//jquery No conflict directive
		print '<script type="text/javascript"><!--
		 jQuery.noConflict(); 
		--></script>';
		
		//print '<script type="text/javascript" src="'.$jquery_path.'js/jquery-1.4.4.min.js"></script>';
		//print '<script type="text/javascript" src="'.$jquery_path.'js/jquery-ui-1.8.7.custom.min.js"></script>';

		// mb.extruder ui
		print '<script type="text/javascript" src="'.$mbExtruder_path.'inc/jquery.hoverIntent.min.js"></script>';
		print '<script type="text/javascript" src="'.$mbExtruder_path.'inc/jquery.metadata.js"></script>';
		print '<script type="text/javascript" src="'.$mbExtruder_path.'inc/jquery.mb.flipText.js"></script>';
		print '<script type="text/javascript" src="'.$mbExtruder_path.'inc/mbExtruder.js"></script>';

		print '
		<script type="text/javascript"><!--//--><![CDATA[//><!--
		jQuery(function($){
		      $("#extruderRight").buildMbExtruder({
		        position:"right",
		        width:800,
		        extruderOpacity:1,
		        textOrientation:"bt",
		        onExtOpen:function(){},
		        onExtContentLoad:function(){},
		        onExtClose:function(){}
		      });
		      $("#extruderRight2").buildMbExtruder({
		        position:"right",
		        width:800,
		        extruderOpacity:1,
		        textOrientation:"bt",
		        onExtOpen:function(){},
		        onExtContentLoad:function(){},
		        onExtClose:function(){}
		      });		
		      $("#extruderRight3").buildMbExtruder({
		        position:"top",
		        width:400,
				height:"auto",
		        extruderOpacity:1,
		        textOrientation:"bt",
		        onExtOpen:function(){},
		        onExtContentLoad:function(){},
		        onExtClose:function(){}
		      });		
		   });
				
		function searchClick() {
				var searchPanel = jQuery("#extruderRight2");
				searchPanel.openMbExtruder(true);
				searchPanel.openPanels();
				return false;
		}
		
		//--><!]]></script>
		';
	}

	function driver_sidePanels() {
		// mb.extruder ui
		$path = DOKU_BASE.'lib/plugins/driver/exe/';
		
		if (!isLoggedIn()) return;
		
		print '<div id="extruderRight" class="{title:\'Prune/Graft\'}">';
		print '<iframe width="100%" height="100%" frameborder=0 src="'.$path.'pathPruner.php"></iframe>';
		print '</div>';
		print '<div id="extruderRight2" class="{title:\'Search\'}">';
		print '<iframe width="100%" height="100%" frameborder=0 src="'.$path.'pathSearch.php"></iframe>';
		print '</div>';
		if (isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {
			print '<div id="extruderRight3" class="{title:\'Hint\'}">';
			print '<iframe width="100%" height="300" frameborder=0 src="'.$path.'recommend.php"></iframe>';
			print '</div>';
		}
	}
	
	function driver_showLPathMenu() {
			// show only if user is logged in.
			if (!isLoggedIn()) {
				// clear LP
				unset($_SESSION[DOKU_COOKIE]['isTrailing']);
				$_SESSION[DOKU_COOKIE]['trail'] = array();
				$_SESSION[DOKU_COOKIE]['ignore'] = array();
				return;
			}
			
			if (!isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {
				print '<div class="sidebar-box">';
				print '<a id="startlpath" class="startlpath" href="#">Start Your Learning Path</a>';
				print '<hr style="margin-top:5px"><div align=right style="margin-top:5px"><a id="searchlpath" class="searchaction" href="#" onclick="return searchClick();">Search...</a></div>';
				print '</div>';
				return;
			}
			
			print '<div class="sidebar-box">';
			print '<a id="stoplpath" class="stoplpath" href="#">Stop Your Learning Path</a>';
			print '<hr style="margin-top:5px"><div class="driver_trailing_menu_title" >Page Actions</div>';
			print '<a id="mark_landmark" class="trail_flag_highlight" href="#">Mark as Landmark</a>';
			$ignore = $_SESSION[DOKU_COOKIE]['ignore'];
			if (array_key_exists(getID(), $ignore)) {
				print '<br/><span style="margin-left:20px">Page Ignored (<a id="reactivate_page" class="reactivateaction" href="#">Reactivate</a>)</span>';				
			} else {
				print '<br/><a id="ignore_page" class="ignoreaction" href="">Ignore Page</a>';				
			}
			print '<hr style="margin-top:5px"><div align=right style="margin-top:5px"><a id="searchlpath" class="searchaction" href="#" onclick="return searchClick();">Search...</a></div>';
			print '</div>';			
	}
		
	require_once(DOKU_INC.'inc/parserutils.php');
	function driver_showLearningPath($render=true) {
		// show only if user is logged in.
		if (!isLoggedIn()) return;
				 
		if (!isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {
			return;
		}
				
			//get trail from SESSION
			$trail = $_SESSION[DOKU_COOKIE]['trail'];
			if (!isset($trail)) // trail must be starting
				print "Trail not in session space."; 

			// parse id page names
			
			//print_r($trail);
			
			$data = processTrailArrayForPrinting($trail);
							
			// handle overflow
			// Count letters until reaches threshold. If beyond threshold, show last ones below visible threshold.
			
			$threshold = 100; // FIXME: Make configurable...
			$letters = 0;
			$printout = array();
			while (($letters < $threshold) && (count($data) > 0)) {
				$page = array_pop($data);
				$letters += strlen($page['name']);
				$printout[] = $page;
			}
			
			$printout = array_reverse($printout);
			
			$result = '<div class=trail>';
			if ($letters > $threshold) { // overflow: showing only last ones.
				$result .= '<div class=trail_page></div>';
			}
			foreach ($printout as $page) {
				$result .= printTrailPage($page);
			}
			$result .= '</div>';
			if ($render) print $result;
			return $result;
			
	}
	
	function processTrailArrayForPrinting($trail) {
		$data = array();
		foreach ($trail as $jump) {
			$pageId = $jump['page'];
			$name = noNSorNS($pageId);	
			$title = get_first_heading($pageId);
			if (isset($title)) $name = $title;
			$r['id'] = $pageId;			
			$r['section']= $jump['section'];
			$r['name'] = $name;
			$r['flag'] = $jump['flag'];
			$data[] = $r;
		}
		return $data;
	}
	
	/* $page is expect to have the following structure
	*
	*  page['id]
	*  page['section']
	*  page['name']
	*  page['flag']
	*  
	*/ 
	function printTrailPage($page, $target='', $callback='', $previewer='', $div_class = 'trail_page', $count=0) {

		$result = '';
		if (strcmp($page['flag'],'ignore') == 0) {
			return $result;
		}

		$url = wl($page['id']);
		$label = $page['name'];
		$linkClass = '';
		$tooltip = $label;
		$sectionId = '';
		
		// check is its section
		$isSection = isset($page['section']);
		
		// parse section data to see how its encoded and decode it...
		// it can be (1) the section title, or (2) in the format: <sectionId>'='<section title>
		$sectionParts = explode("=",$page['section']);
		if (sizeof($sectionParts) > 1) {
			$page['section'] = $sectionParts[1];
		}
		 
		if ($isSection) {
			$sectionId = sectionId($page['section'],$check);
			$url .= '#'.$sectionId;
			$label = $page['section'].' <sub>('.substr($label,0,5).'...)</sub>';
			$linkClass = 'class="trail_section_link"';
			$tooltip = $page['section'].' (in ['.$page['name'].'])';
		}
		
		// process callback and previewer
		$onclick = '';
		if (strcmp($callback,'') != 0) {
			if (strcmp($previewer,'') != 0) {	
				// pageSearch case	
				$onclick .= 'onclick="return '.$callback.'(\''.$previewer.'\',\''.$page['id'].'\', \''.$sectionId.'\')"';
			} else {
				// pathPruner case
				$onclick .= 'return onclick="return '.$callback.'(\''.$page['id'].'\', \''.$sectionId.'\')"';				
			}
			//$url = '#';
		}
		

		// id must contains flags for later POST purposes via JavaScript
		$page_id = $page['id'];
		if ($isSection) $page_id .= '#'.$sectionId.'='.$page['section'];
		$page_id .= '|'.$page['flag'];
		
		// main div, no flag
		$add = '<div id="'.$page_id.'" class="'.$div_class.'">';
		
		//recommendation count
		if ($count > 0) {
			$add .= '<div class="recommend_count">('.$count.')</div>';
		}
		
		//flag
		if (strcmp($page['flag'],'start') == 0) 
			$add .= '<a class="trail_flag_start" target="'.$target.'" href="'.$url.'" '.$onclick.'></a>';
		if (strcmp($page['flag'],'visited') == 0) 
			$add .= '<a class="trail_flag_visited" target="'.$target.'" href="'.$url.'" '.$onclick.'></a>';
		if (strcmp($page['flag'],'landmark') == 0) 
			$add .= '<a class="trail_flag_highlight" target="'.$target.'" href="'.$url.'" '.$onclick.'></a>';		
		$result .= $add;
		
		// link
		$result .= '<a '.$linkClass.' title="'.$tooltip.'" target="'.$target.'" href="'.$url.'" '.$onclick.'>'.$label.'</a>';
		
		// end main div
		$result .= '</div>';
		return $result;
	}	
?>
