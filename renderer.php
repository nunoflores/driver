<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';
require_once DOKU_INC.'inc/pageutils.php';


class renderer_plugin_driver extends Doku_Renderer_xhtml {
	
	var $hasContent = false; // used to check if a section has content, or if its just a heading.
	var $sectionCount = 0; // section counter to re-use "edit button" event callbacks
	var $sectionTitle = ""; // section title to use on "like" onclick callback.

    function getInfo(){
        return confToHash(dirname(__FILE__).'/README');
    }

    function getFormat(){
        return 'xhtml';
    }

    function canRender($format) {
        return ($format=='xhtml');
    }

 	function header($text, $level, $pos) {
		parent::header($text, $level, $pos);
		
		global $sectionTitle;
		
		if (!$text) {
			$sectionTitle = "";
			return;
		}
		$sectionTitle = $text;
		
	}

	function section_open($level) {
	   parent::section_open($level);
	
	   global $sectionCount;
	   global $hasContent;
	
	   $hasContent = false;
	   $sectionCount++;

	}
	
	function cdata($text) {
		parent::cdata($text);
		
		global $hasContent;
		$hasContent = true;
	}
	
	function section_close() {
		
		global $sectionCount;
		global $hasContent;
		global $sectionTitle;
		
		global $ID;
				
		// I want to do this
		
		// Only if in "trail" (learning) mode
		
		session_start();

		//error_log("section_close on ".$ID." isTrailing: ".print_r(isset($_SESSION[DOKU_COOKIE]['isTrailing']),true));

		if (isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {
			if (($hasContent) && ($sectionCount != 1)) //first section means the entire page: ignore! Let _addJump handle that!
			{
				$this->doc .= DOKU_LF.'<div class=\'secedit editbutton_'.$sectionCount.'\'><form class=\'button btn_secedit\'>';
				$this->doc .= '<a href="#" class="likeLink" onclick="driver_addSectionJump(\''.$sectionTitle.'\');return false;">';
				$this->doc .= 'Like</a></form></div>';
			}
		}
		
		// parent code
		parent::section_close();
		//$this->doc .= DOKU_LF.'</div>'.DOKU_LF;
	}
	
}

