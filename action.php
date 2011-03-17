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
    }
    
    /*
    *  Logging trail...
    */
    function _addJump(){
	    global $ID;

		session_start();
		
		if (isset($_SESSION[DOKU_COOKIE]['isTrailing'])) {

			$trail = $_SESSION[DOKU_COOKIE]['trail'];

			$jump['page'] = $ID;

			// mark start
			if (count($trail) == 0)
				$jump['flag'] = 'start';
				
			// mark re-visited or inherit previous flag
			foreach ($trail as $t) {
				if (strcmp($t['page'],$ID) == 0)
				    $jump['flag'] = (isset($t['flag'])) ? $t['flag'] : 'visited';
			}
			
			// ignore non-existing pages
			if (!page_exists($ID)) return;
			
			// refresh page ignore
			if (count($trail) > 0) { 
				$lastjump = $trail[count($trail)-1];
				if (strcmp($ID,$lastjump['page']) == 0) return;
			}
			
			// ignoring ignore marked pages 
			$ignore = $_SESSION[DOKU_COOKIE]['ignore'];
			if (array_key_exists($ID, $ignore)) return;
				
			array_push($_SESSION[DOKU_COOKIE]['trail'],$jump);
		}
		//session_stop();
    }

    function _handle_ajax_call(&$event, $param) {

		error_log("here!");

        if (($event->data == 'driver_start') || ($event->data == 'driver_stop')) {
            $event->preventDefault();  
            $event->stopPropagation();
			session_start();
			if ($event->data == 'driver_start') { 
				$_SESSION[DOKU_COOKIE]['isTrailing'] = 1;
				$_SESSION[DOKU_COOKIE]['trail'] = array();
				$_SESSION[DOKU_COOKIE]['ignore'] = array();
			};
			if ($event->data == 'driver_stop') 
				unset($_SESSION[DOKU_COOKIE]['isTrailing']); 
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
		
    }

} // End of class

?>
