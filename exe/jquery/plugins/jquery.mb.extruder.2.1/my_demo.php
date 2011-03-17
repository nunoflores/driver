<html>
<head>
	 <link href="css/mbExtruder.css" media="all" rel="stylesheet" type="text/css">
	  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
	  <script type="text/javascript" src="inc/jquery.hoverIntent.min.js"></script>
	  <script type="text/javascript" src="inc/jquery.metadata.js"></script>
	  <script type="text/javascript" src="inc/jquery.mb.flipText.js"></script>
	  <script type="text/javascript" src="inc/mbExtruder.js"></script>
	 <script type="text/javascript">

	    $(function(){
	      $("#extruderRight").buildMbExtruder({
	        position:"right",
	        width:800,
	        extruderOpacity:1,
	        textOrientation:"tb",
	        onExtOpen:function(){},
	        onExtContentLoad:function(){},
	        onExtClose:function(){}
	      });
	      $("#extruderRight2").buildMbExtruder({
	        position:"right",
	        width:800,
	        extruderOpacity:1,
	        textOrientation:"tb",
	        onExtOpen:function(){},
	        onExtContentLoad:function(){},
	        onExtClose:function(){}
	      });

	    });

	  </script>
	  <style>
		.extruder .flap {
			font-family: Verdana;
		}
		.extruder .flapLabel {
			font-size: small;
		}
	  </style>
</head>
<body>
	<a href="#" onclick="$('#extruderRight').openMbExtruder(true);$('#extruderRight').openPanels()">open</a><br/>
	<a href="#" onclick="$('#extruderRight').closeMbExtruder(true)">close</a><br/>
	<?php
		for ($i = 0; $i < 100 ; $i++)
			print "asadafksdlkmlecqoecmxdjns ifuw efuhal erfhualruh alruhv alruehv aleru valeurhv laureh viuaehr valuher vlaeurh<br/>";
	?>
	<div id="extruderRight" class="{title:'Search'}">
	 <iframe width="100%" height="100%" frameborder=0 src="../pathSearch.php"></iframe>
	</div>
	<div id="extruderRight2" class="{title:'Prune/Graft'}">
	 <iframe width="100%" height="100%" frameborder=0 src="../pathPruner.php"></iframe>
	</div>
	
</body>
</html>