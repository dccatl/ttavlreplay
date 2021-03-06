<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <!--
            thingtech Vehicle Playback Demo

            Prototype of moving vehicle AVL map using POC ReST'ful Web Service

            Revision History:
            ===============================================================
            2014-09-26: DCC - Genesis
        -->
        <title>thingtech Vehicle Location Playback</title>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
        <script type="text/javascript" src="label.js"></script>
        <script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/styledmarker/src/StyledMarker.js"></script>
        <style type='text/css'>
            html, body {
                width: 100%;
                height: 100%;
            }

            #map-canvas {
                height:100%;
                width:100%;
                top:0px;
                left:0px;
                right:0px;
                bottom:0px;
                overflow: hidden;
            }
            .labels {
                 color: red;
                 background-color: white;
                 font-family: "Lucida Grande", "Arial", sans-serif;
                 font-size: 10px;
                 font-weight: bold;
                 text-align: center;
                 width: 40px;
                 border: 2px solid black;
                 white-space: nowrap;
            }
			
			label { display: block; }
			select { width: 400px; }
			
			#menu { position: absolute; top: 10px; left: 100px; z-index: 99; }			
        </style>		
	    <script type='text/javascript'>
                var vMarkers = [];
                var vPolys = [];
                var map;
                var path;
                var marker;
 				var label;
                var firstTime;
                var infowindow;
                var contentString;
                var minutes = "";
                var earlyLate = "";
                var vehicle = "";
				var dateStr = "";
                var color;
   				// calculate map height and width based on screen size:
				var hScale = 0.95 //height scale
				var wScale = 0.72 //width scale
				var mapWidth = screen.width;
				var mapHeight = screen.height;

				window.onresize = function(event) {
                    var el = $("#map-canvas");
                    el.css("position", "absolute");
                    el.css("top", "0px");
                    el.css("left", "0px");
                    el.css("right", "0px");
                    el.css("bottom", "0px");
                    el.css("height", $("body").height());
                    el.css("width", $("body").width());
                    el.css("overflow", "hidden");
                    google.maps.event.trigger(map, 'resize');
                    map.setZoom(map.getZoom());
                }

                function initialize() {
                    var myLatlng = new google.maps.LatLng(33.7398316, -84.4075628);
                    var myOptions = {
                        zoom: 13,
                        center: myLatlng,
                        width: $("body").width(),
                        height: $("body").height(),
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                    firstTime = 0;
                    map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
                    var transitLayer = new google.maps.TransitLayer();
                    transitLayer.setMap(map);
                    google.maps.event.addDomListener(window, 'load', initialize);
                    infowindow = new google.maps.InfoWindow({
						content: "Loading..."
                    });
                    //loadVehicles();
                };
        </script>	
	</head>
	<body onload="initialize()">
		<div id="map-canvas"></div>
		<div id="menu">
			<input type = "text" id="datePicker" value="Select a date:">
			<select name="select" id="select"></select>
			<div id="output"></div>
		</div>
		
		<script id="source" language="javascript" type="text/javascript">
			$( "#select" ).selectmenu({
				change: function( event, ui ) {
					vehicle = $( "#select option:selected" ).text();
					loadVehicles();
				}
			});
			$('#datePicker').datepicker({
				dateFormat: 'yy-mm-dd',
				onSelect: function (selectedDate) {
					$.ajax({
						url: 'api.php',
						data: "date=" + selectedDate,
						dataType: 'json',
						success: function(data) {
							if(data.length!=0) {
								var newOption = "";
								$("#select").html("");
								newOption = "<option value=''>Select a device...</option>";
								$("#select").append(newOption).selectmenu('refresh');
								for (var i=0; i<data.length; i++) {
									newOption = "<option value='" + data[i].device_name + "'>" + data[i].device_name + "</option>";
									$("#select").append(newOption).selectmenu('refresh');
								}
								htmlStr = "Select a device above.";
								//$('#output').html(htmlStr);
								dateStr = selectedDate;
								
							}
							else {
								htmlStr = "No devices for " + selectedDate;
								//$('#output').html(htmlStr);
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							alert(errorThrown);
						}
					});
				}
			});

			function loadVehicles() {
				$(document).ready(function() {
					$.ajax({
						type: "GET",
						timeout: 2000, //in milliseconds
						// Call ThingTech Vehicle Playback web service (http://ttwebrcvr.thingtech.com:8888/playback/<vehicle_name(IMEI)>/date/<yyyy-mm-dd>)
						url: 'http://ttwebrcvr.thingtech.com:8888/playback/' + vehicle + '/date/' + dateStr + '?callback=?',
						dataType: 'json',
						success: function(d){
							var j = 0;
							vehicle = "";
							var path;
							color = get_random_color();
							var polyOptions = {
								strokeColor: color,
								strokeOpacity: 1.0,
								map: map,
								strokeWeight: 3
							};
							poly = new google.maps.Polyline(polyOptions);
							marker = new google.maps.Marker({
								position: new google.maps.LatLng(d[j].lat,d[j].lon),
								icon: {
									path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
									scale: 5,
									rotation: d[j].hdg,
									strokeColor: color,
									strokeOpacity: 1.0
								},
								title: d[j].vehicle,
								html: contentString,
								map: map
							});	

							label = new Label({
								color: color,
								map: map
							});

							label.bindTo('position', marker, 'position');
							label.bindTo('text', marker, 'title');
							
							
							
							interval = window.setInterval(function () {
							//function play() {
								if(d[j].hdg>=0 && d[j].hdg<22.5) direction="NORTH";
								else if(d[j].hdg>=22.5 && d[j].hdg<67.5) direction="NORTHEAST";
								else if(d[j].hdg>=67.5 && d[j].hdg<112.5) direction="EAST";
								else if(d[j].hdg>=112.5 && d[j].hdg<157.5) direction="SOUTHEAST";
								else if(d[j].hdg>=157.5 && d[j].hdg<202.5) direction="SOUTH";
								else if(d[j].hdg>=202.5 && d[j].hdg<247.5) direction="SOUTHWEST";
								else if(d[j].hdg>=247.5 && d[j].hdg<292.5) direction="WEST";
								else if(d[j].hdg>=292.5 && d[j].hdg<338) direction="NORTHWEST";
								else direction="NORTH";
										
								var latlng = new google.maps.LatLng(d[j].lat,d[j].lon);
								
								var Sym = {
									path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
									scale: 5,
									rotation: d[j].hdg,
									strokeColor: color,
									strokeOpacity: 1.0	
								}
								
								marker.setIcon(Sym);
								marker.setPosition(latlng);
								
								path = poly.getPath();
								path.push(latlng);
								poly.setPath(path);
								//vehicle = d.vehicle;
									
								contentString = '<div id="content">' + '<div id="vehicleInfo">' + '</div>' + '<h2 id="firstHeading" class="firstHeading">Unit: ' + 
									d[j].vehicle + '</h2> <div id="bodyContent">' + '<br><b>Traveling:</b> ' +
									direction + '<br><b>At:</b> ' +
									d[j].spd + ' MPH' + '<br><b>Status:</b> ' + 
									d[j].ev_type + '<h6><b>Last Update:</b> ' + 
									d[j].gps_ts + '</h6></div> </div>';
								
								google.maps.event.addListener(marker, "click", function () {
									infowindow.setContent(contentString);
									infowindow.open(map, this);
								});
								j++;
								if( j >= d.length) {
									window.clearInterval(interval); 
								}
							}, 750);//setTimeout(function (d) {

							if (firstTime == 0) {
								AutoCenter();
								firstTime = 1;
							}
							
							function AutoCenter() {
								//  Create a new viewpoint bound
								var bounds = new google.maps.LatLngBounds();
								//var posit1 = new google.maps.LatLng(d[0].lat,d[0].lon);
								//var posit2 = new google.maps.LatLng(d[d.length-1].lat, d[d.length-1].lon);
								
								//  Go through each...
//								bounds.extend(posit1);
//								bounds.extend(posit2);
								
								for (var k = 0; k < d.length; k++) {
									var posit = new google.maps.LatLng(d[k].lat, d[k].lon);
									bounds.extend(posit);
								}
								
								//  Fit these bounds to the map
								
								map.fitBounds(bounds);
							}
							
							//interval = window.setInterval(play(), 750);
						},
						error: function(error, vehicles){
							console.log(error)
						}
					}); //$.ajax({
				}); //$(document).ready(function() {
			} //function loadVehicles()

			function get_random_color() {
				var letters = '0123456789ABCDEF'.split('');
				var color = '#';
				for (var i = 0; i < 6; i++ ) {
					color += letters[Math.round(Math.random() * 15)];
				}
				return color;
			}

			function handleNoGeolocation(errorFlag) {
			  if (errorFlag) {
				var content = 'Error: The Geolocation service failed.';
			  } else {
				var content = 'Error: Your browser does not support geolocation.';
			  }
			}			
		</script>
	</body>
</html>