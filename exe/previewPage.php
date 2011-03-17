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

	$id = $_REQUEST['pageid'];
	if (!isset($id)) die;

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php tpl_metaheaders(); ?>
	<link type="text/css" href="<?php print DOKU_BASE.'lib/plugins/driver/style.css' ?>" rel="Stylesheet" />	
  </head>
<body>
	<div id='page' class='dokuwiki'>
	<?php print p_wiki_xhtml($id); ?>
	</div>
</body>
</html>	
