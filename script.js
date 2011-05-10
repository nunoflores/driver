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
		var ignorepage = $('ignore_page');
		var reactivatepage = $('reactivate_page');
        if (start) {
                addEvent(start, 'click', function(event) {
                    //event.preventDefault();
                    //event.stopPropagation();
                    driver.pathAction('driver_start'); 
                    return true; });
        }
        if (stop) {
                addEvent(stop, 'click', function(event) {
	                //event.preventDefault();
                    //event.stopPropagation();
					driver.pathAction('driver_stop');
                    return true; });
        }
        if (landmark) {
                addEvent(landmark, 'click', function(event) {
                    driver.pathAction('driver_landmark'); 
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

			var onCompletion = function() {
				// forces refresh on completion
				window.location.reload(true);			
			};
						
			ajax.onCompletion = onCompletion;
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

function driver_addSectionJump(title) {
	//alert(title);
	
	var ajax = new sack(DOKU_BASE+'lib/exe/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;

        ajax.setVar('call', 'driver_sectionLike');
		ajax.setVar('here', getURLparameter('id'));
		ajax.setVar('sectionTitle', title);
		
		ajax.onCompletion = function() {
			// forces refresh on completion
			window.location.reload(true);			
		};

        ajax.runAJAX();
        return false;
}

// vim:ts=4:sw=4:et:enc=utf-8:
