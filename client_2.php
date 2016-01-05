<!---------------------------------------------------------------------------
Example client script for JQUERY:AJAX -> PHP:MYSQL example
---------------------------------------------------------------------------->

<html>
	<head>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
		<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
		<style>
		#accordion {
			width:  600px;
		}

		#mapCanvas {
			border:  1px dashed #777;
			width:  500px;
			height:  350px;
			margin:  0 auto;
		}
		</style>
  
		<script>		
			var map = null;
			
			/*function initializeMap() {
				var myLatlng = new google.maps.LatLng(33.7398316, -84.4075628);
				var myOptions = {
					zoom: 13,
					center: myLatlng,
//					width: $("body").width(),
//					height: $("body").height(),
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				//map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
				//return new google.maps.Map($('#mapCanvas')[0], myOptions);
				return new google.maps.Map(document.getElementById("mapCanvas"), myOptions);
				//var transitLayer = new google.maps.TransitLayer();
				//transitLayer.setMap(map);
				//google.maps.event.addDomListener(window, 'load', initialize);
			}
			*/
			
			function initializeMap() {
				var myOptions = {
					zoom: 8,
					center: latlng,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				return new google.maps.Map($('#mapCanvas')[0], myOptions);
			}			
		
			$(document).ready(function() {
				//$( "#select" ).selectmenu();
				$("#accordion").accordion();
				/*$('#datePicker').datepicker({
					dateFormat: 'yy-mm-dd',
					onSelect: function (selectedDate) {
						$( "#select" ).selectmenu();
						$.ajax({
							url: 'api.php',
							data: "date=" + selectedDate,
							dataType: 'json',
							success: function(data) {
								if(data.length!=0) {
									var newOption = "";
									$("#select").html("");
									for (var i=0; i<data.length; i++) {
										newOption = "<option value='" + data[i].device_name + "'>" + data[i].device_name + "</option>";
										$("#select").append(newOption).selectmenu('refresh');
									}
									htmlStr = "Select a device above.";
									$('#output').html(htmlStr);
								}
								else {
									htmlStr = "No devices for " + selectedDate;
									$('#output').html(htmlStr);
								}
							},
							error: function(XMLHttpRequest, textStatus, errorThrown) {
								alert(errorThrown);
							}
						});
					}
				});
				*/
				$("#accordion").bind('accordionchange', function(event, ui) {
					if (ui.newContent.attr('id') == 'tabTwo' && !map)
					{
						map = initializeMap();
					}
				});
			});		
		</script>
	</head>
	<body>
		<div id="accordion">
			<h3><a href="#">Settings</a></h3>
			<div id="tabOne">
				<p>Test</p>
			<!--
				<input type = "text" id="datePicker" value="Select a date:">
				<select name="select" id="select"></select>
			-->
			</div>
			
			<h3><a href="#">Playback</a></h3>
			<div id="tabTwo">
				<h4>Map to Somewhere</h4>
				<div id="mapCanvas"></div>
			</div>
		</div>
	</body>
</html>