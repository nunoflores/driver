/**
 * Javascript for DokuWiki Plugin driver
 * @author Nuno Flores <nuno.flores@gmail.com>
 */


driver = {
    // Attach all events to elements
    attach: function() {
        // attach events
        var start = $('startlpath');
		var stop = $('stoplpath');
		var landmark = $('mark_landmark');
		var search = $('searchlpath');
		var ignorepage = $('ignore_page');
		var reactivatepage = $('reactivate_page');
        if (start) {
                addEvent(start, 'click', function(event) {
                    driver.pathAction('driver_start'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
        if (stop) {
                addEvent(stop, 'click', function(event) {
					var url = DOKU_BASE+'lib/plugins/driver/exe/pathPruner.php';
					var windowFeatures = 'width='+window.outerWidth+',height='+window.outerHeight+',left=20,top=20,directories=no,location=no,toolbar=no,menubar=no';
					window.open(url,'LPathpopup',windowFeatures);
                    driver.pathAction('driver_stop'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
        if (landmark) {
                addEvent(landmark, 'click', function(event) {
                    driver.pathAction('driver_landmark'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
		if (search) {
                addEvent(search, 'click', function(event) {
					var url = DOKU_BASE+'lib/plugins/driver/exe/pathSearch.php';
					var windowFeatures = 'width='+(window.innerWidth-100)+',height='+(window.innerHeight-100)+',left=20,top=20,directories=no,location=no,toolbar=no,menubar=no';
					window.open(url,'driverearchpopup',windowFeatures);
                    driver.pathAction('driver_search'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
		if (ignorepage) {
                addEvent(ignorepage, 'click', function(event) {
                    driver.pathAction('driver_ignore'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
		if (reactivatepage) {
                addEvent(reactivatepage, 'click', function(event) {
                    driver.pathAction('driver_reactivate'); 
                    //event.preventDefault();
                    //event.stopPropagation();
                    return true; });
        }
    },
    
    // go!
    pathAction: function(action) {
	
	       var ajax = new sack(DOKU_BASE+'lib/exe/ajax.php');
	        ajax.AjaxFailedAlert = '';
	        ajax.encodeURIString = false;

	        ajax.setVar('call', action);
			ajax.setVar('here', getURLparameter('id')); // needed for reactivate action

	        ajax.runAJAX();
	        return false;

    },
};

addInitEvent(function(){
    driver.attach();
});

function getURLparameter( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}

// vim:ts=4:sw=4:et:enc=utf-8:
