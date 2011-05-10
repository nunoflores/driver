<?php
/**
 * Plugin driver - Nuno Flores
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Nuno Flores (nuno.flores@gmail.com)
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
require_once(DOKU_INC.'inc/pageutils.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_driver extends DokuWiki_Action_Plugin {
 
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Nuno Flores',
            'email'  => 'nuno.flores@gmail.com',
            'date'   => '2011-01-01',
            'name'   => 'driver Action Plug-in',
            'desc'   => 'Tracks your navigation within the wiki (breadcrumbs on steroids ;))',
            'url'    => '',
        );
    }
 
    /*
    * Register its handlers with the dokuwiki's event controller
    */
    function register(&$controller) {
        $controller->register_hook('ACTION_HEADERS_SEND', 'BEFORE',  $this, '_addJump');
		$controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_handle_ajax_call');
		$controller->register_hook('PARSER_CACHE_USE','BEFORE', $this, '_cache_prepare');
    }

	/*
	* disabling cache alltogether...for now...
	*
	* Code taken from "include" plugin
	*/
	function _cache_prepare(&$event, $param) {
		
		global $conf;		
        $cache =& $event->data;

        if(!isset($cache->page)) return;
        //if(!isset($cache->mode) || !in_array($cache->mode, $this->supportedModes)) return;

 		$depends = p_get_metadata($cache->page);
        if (!is_array($depends)) return; // nothing to do

		$cache->depends['purge'] = true;

/*
		error_log("page: ".$cache->page);

        $depends = p_get_metadata($cache->page, 'plugin_driver');

        if (!is_array($depends)) return; // nothing to do for us

        if (!is_array($depends['pages']) ||
            !is_array($depends['instructions']) ||
            $depends['pages'] != $this->helper->_get_included_pages_from_meta_instructions($depends['instructions']) ||
            // the include_content url parameter may change the behavior for included pages
            $depends['include_content'] != isset($_REQUEST['include_content'])) {

            $cache->depends['purge'] = true; // included pages changed or old metadata - request purge.
  
        } else {
            // add plugin.info.txt to depends for nicer upgrades
            $cache->depends['files'][] = dirname(__FILE__) . '/plugin.info.txt';
            foreach ($depends['pages'] as $page) {
                if (!$page['exists']) continue;
                $file = wikiFN($page['id']);
                if (!in_array($file, $cache->depends['files'])) {
                    $cache->depends['files'][] = $file;
                }
            }
        }
		*/
	/*	
		error_log("page: ".$cache->page);
		
		$id = $cache->page;
		$data = array('cache' => 'expire');
		$render = false;
		$persistent = false;

		p_set_metadata($id, $data, $render, $persistent);
		*/
	}
    
    /*
    *  Logging trail...
    */
	function _processJump($isSection=false,$sectionTitle='',$id='') {
	    global $ID;

		$pageId = $ID;
		
		if ($isSection) 
			$pageId = $id;

		session_start();
		
		if (isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {
			
			$trail = $_SESSION[DOKU_COOKIE]['trail'];

			$jump['page'] = $pageId;
			
			// if its section like, then store section
			if ($isSection) {
				$jump['section'] = $sectionTitle;
			}

			// mark start
			if (count($trail) == 0)
				$jump['flag'] = 'start';
				
			// mark re-visited or inherit previous flag
			foreach ($trail as $t) {
				if (strcmp($t['page'],$pageId) == 0)
				    $jump['flag'] = (isset($t['flag'])) ? $t['flag'] : 'visited';
			}
			
			// ignore non-existing pages
			if (!page_exists($pageId)) {
				error_log("Page doesn't exist: ".$pageId);
				return;
			}


			// refresh page ignore.
			if (count($trail) > 0) { 
				$lastjump = $trail[count($trail)-1];
				if ((!$isSection) && (strcmp($pageId,$lastjump['page']) == 0)) {
					error_log("consecutive page re-submition: ".$lastjump['page']);
					return;
				}
			}
			
			// ignore section consecutive like
			if (count($trail) > 0) { 
				$lastjump = $trail[count($trail)-1];
				if (($isSection) && (strcmp($sectionTitle,$lastjump['section']) == 0)) {
					error_log("consecutive section re-submition: ".$lastjump['section']);					
					return;
				}
			}

			// ignoring ignore marked pages 
			$ignore = $_SESSION[DOKU_COOKIE]['ignore'];
			if (array_key_exists($pageId, $ignore)) {
				error_log("ignoring page: ".$pageId);				
				return;
			}
				
			error_log("pushed jump: ".print_r($jump, true));	
			
			array_push($_SESSION[DOKU_COOKIE]['trail'],$jump);
		}
		//session_stop();
    }

	function _addJump(&$event, $param) {
		// redirect
		$this->_processJump();
	}

    function _handle_ajax_call(&$event, $param) {

        if (($event->data == 'driver_start') || ($event->data == 'driver_stop')) {
            $event->preventDefault();  
            $event->stopPropagation();
			session_start();
			if ($event->data == 'driver_start') { 
				error_log("DRIVER_START");
				$_SESSION[DOKU_COOKIE]['isTrailing'] = 1;
				$_SESSION[DOKU_COOKIE]['trail'] = array();
				$_SESSION[DOKU_COOKIE]['ignore'] = array();
			};
			if ($event->data == 'driver_stop') {
				error_log("DRIVER_STOP");
				unset($_SESSION[DOKU_COOKIE]['isTrailing']); 
			}
        }

		if (strcmp($event->data,'driver_landmark') == 0) {
			$event->preventDefault();  
	        $event->stopPropagation();
			session_start();
			$trail = &$_SESSION[DOKU_COOKIE]['trail'];
			$lastpage = array_pop($trail);
			$lastpage['flag'] = 'landmark';
			$trail[] = $lastpage;
		} 
		if (strcmp($event->data,'driver_ignore') == 0) {
			$event->preventDefault();  
	        $event->stopPropagation();
			session_start();
			$trail = &$_SESSION[DOKU_COOKIE]['trail'];
			// pop from trail
			$lastpage = array_pop($trail);
			//mark as ignoring
			$ignore = &$_SESSION[DOKU_COOKIE]['ignore'];
			$key = $lastpage['page'];
			$ignore[$key] = 1;
		} 
		if (strcmp($event->data,'driver_reactivate') == 0) {
			$event->preventDefault();  
	        $event->stopPropagation();
			session_start();
			//remove from ignore array
			$ignore = &$_SESSION[DOKU_COOKIE]['ignore'];
			$here = $_REQUEST['here'];
			unset($ignore[$here]);
			// add to trail
			_addJump();
		} 
		
		if (strcmp($event->data,'driver_sectionLike') == 0) {
			$event->preventDefault();  
	        $event->stopPropagation();
			session_start();

			error_log("driver_sectionLike");
			
			$sectionTitle = $_REQUEST['sectionTitle'];
			$pageId = $_REQUEST['here'];
			
			// add to trail
			$this->_processJump(true,$sectionTitle,$pageId);
		}
		
    }

} // End of class

?>