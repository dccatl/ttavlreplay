<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <!--
            thingtech Vehicle Playback Demo

            Prototype of moving vehicle AVL playback using POC ReST'ful Web Service

            Revision History:
            ===============================================================
            2014-09-26: DCC - Genesis
			2014-12-02: DCC - Added 2nd polyline for digital input 1 high ("plough down")
        -->
        <title>thingtech Vehicle Location Playback</title>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWwf1Qu7l8msXt2Vu20W1aGodsSVcfd8U&v=3.exp&sensor=false"></script>
        <script type="text/javascript" src="label.js"></script> <!-- custom google map marker label class makes use of google overlay view and binds position to marker position --> 
        <script type="text/javascript" src="https://google-maps-utility-library-v3.googlecode.com/svn/trunk/styledmarker/src/StyledMarker.js"></script>
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
			
			label { 
				display: block; 
			}
			
			select { 
				width: 400px; 
				border: 1px solid #000;
			}
			
			#datePicker {
				font-family: Verdana,Arial,sans-serif;
				font-size: 1em;
				font-weight: bold;
			}
			
			#sliderValue { 
				width: fit-content; 
				position: absolute; 
				text-align: center;
				/*border: 1px solid #f00;*/
				font-family: Verdana,Arial,sans-serif;
				font-size: 1em;
				font-weight: bold;
			}
			
			#menu {
				width: 400px; 
				position: absolute; 
				top: 10px; 
				left: 100px; 
				z-index: 99; 
			}			
        </style>		
	    <script type='text/javascript'>
                var vMarkers = [];
                var vPolys = [];
				var polyCtr = 0;
                var map;
                var path;
				//var path2;
                var marker;
 				var label;
                var firstTime;
                var infowindow;
                var contentString;
                var minutes = "";
                var vehicle = "";
				var dateStr = "";
                var color;
   				// calculate map height and width based on screen size:
				var hScale = 0.95 //height scale
				var wScale = 0.72 //width scale
				var mapWidth = screen.width;
				var mapHeight = screen.height;
				var thumbStr = "";
				var hour = 0;
				var min = 0;
				var sec = 0;
				var miles = 0;
				var service = new google.maps.DirectionsService(), poly;
				var snap = true;
				var input1on = false;
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
                };
        </script>	
	</head>
	<body onload="initialize()">
		<div id="map-canvas"></div>
		<div id="menu">
			<input type = "text" id="datePicker" value="Select a date:">
			<label for="check" class="ui-widget">Snap to Roads</label><input type="checkbox" id="check">
			<select name="select" id="select"></select>
			<div id="slider-1"></div>
			<div id="sliderValue"></div>
		</div>

		<script id="source" language="javascript" type="text/javascript">
			$( "#select" ).selectmenu({
				change: function( event, ui ) {
					vehicle = $( "#select option:selected" ).val();
					loadVehicles();
				}
			});
			$( "#check" ).button();
			$('#check').bind('change', function(){
				if($(this).is(':checked')){
					snap = true;
				}
				else {
					snap = false;
				}
			});
			
			$('#datePicker').datepicker({
				dateFormat: 'yy-mm-dd',
				onSelect: function (selectedDate) {
					$.ajax({
						url: 'api.php', //URL to return distinct vehicle (IMEI) from db for specified date
						data: "date=" + selectedDate,
						dataType: 'json',
						success: function(data) {
							if(data.length!=0) {
								var newOption = "";
								$("#select").html("");
								newOption = "<option value=''>Select a device...</option>";
								$("#select").append(newOption).selectmenu('refresh');
								for (var i=0; i<data.length; i++) {
									newOption = "<option value='" + data[i].esn + "'>" + data[i].name + "</option>";
									$("#select").append(newOption).selectmenu('refresh');
								}
								dateStr = selectedDate;
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							alert(errorThrown);
						}
					});
				}
			});

			$( "#slider-1" ).slider({
				//animate: "slow",
				change: function(event, ui) {
					var delay = function() {
						var label = '#sliderValue';
						$(label).html(thumbStr).position({
							my: 'center top',
							at: 'center bottom',
							of: ui.handle,
							offset: "0, 10"
						});
					};
					// wait for the ui.handle to set its position
					setTimeout(delay, 5);
				},
				slide: function(event, ui) {
					var delay = function() {
						var label = '#sliderValue';
						$(label).html(thumbStr).position({
							my: 'center top',
							at: 'center bottom',
							of: ui.handle,
							offset: "0, 10"
						});
					};
					// wait for the ui.handle to set its position
					setTimeout(delay, 5);
				}
			});
			
			$('#sliderValue').html(thumbStr).position({
				my: 'center top',
				at: 'center bottom',
				of: $('#slider-1 a:eq(0)'),
				offset: "0, 10"
			});	
			
			function loadVehicles() {
				$(document).ready(function() {
					$.ajax({
						type: "GET",
						timeout: 10000, //in milliseconds
						// Call ThingTech Vehicle Playback web service (http://ttwebrcvr.thingtech.com:8888/playback/<vehicle_name(IMEI)>/date/<yyyy-mm-dd>)
						url: 'https://ttwebrcvr.thingtech.com:9898/playback/' + vehicle + '/date/' + dateStr + '?callback=?',
						dataType: 'json',
						success: function(d){
							var j = 0;
							
							vehicle = "";
							//color = get_random_color();
							color = '#0000FF'; 		//Blue
							var titleStr = "";
							var postedSpeed = "";
							var speedPath = "";
							var polyOptions = {		//Options for "plough up" polyline
								strokeColor: color,
								strokeOpacity: 1.0,
								map: map,
								strokeWeight: 2,
								zIndex: 100				//Draw thin blue line on top of thick green one
							};
							
							var poly2Options = {	//Options for "plough up" polyline
								strokeColor: '#00FF00', //Green
								strokeOpacity: 1.0,
								map: map,
								strokeWeight: 8,
								zIndex: 50				//Draw thick green line behind thin blue one.
							};
							
							poly = new google.maps.Polyline(polyOptions);
							
							marker = new google.maps.Marker({
								position: new google.maps.LatLng(d[j].lat,d[j].lon),
								//Using SVG icon allows rotation, stroke and fill color and scaling of icon.
								icon: {
									path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, //PATH can be any SVG path or one of Google maps standards
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

								if (d[j].di01 == 1 && input1on == false) { 		//If "plough goes down" then...
									var poly2 = new google.maps.Polyline(poly2Options);	//Instantiate a new polyline with the options for a thick green line and...
									var path2 = poly2.getPath();						//Get a new path from the new polyline
									vPolys.push([poly2, path2])							//Push the new polyline and path into the array
									input1on = true;									//Toggle the "plough down" variable
								}
								else if (d[j].di01 == 0 && input1on == true) {	//Otherwise, if the plough goes up after it was down then....
									input1on = false;									//Toggle the "plough down" variable and...
									polyCtr++;											//Increment the counter so that the next time the "plough" goes down there will be a new polyline in the array.
								}
								
								var Sym = {
									path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
									scale: 5,
									rotation: d[j].hdg,
									strokeColor: color,
									strokeOpacity: 1.0	
								}; // instantiate marker icon rotated to current heading.
								
								marker.setIcon(Sym); //Set the markers icon to the new one created above
								
								path = poly.getPath(); //Get the polyline's current path attribute.
								
								if(snap == true) { // If snapping to streets use the google driving directions service to get the smoothed route line between the last point and current point.
									if (path.getLength() === 0) {
									  path.push(latlng);
									  poly.setPath(path);
									  if (input1on == true) { //If "plough is down" then push another green segment onto the current path and set it to the current "plough down" polyline
										vPolys[polyCtr][1].push(latlng);
										vPolys[polyCtr][0].setPath(vPolys[polyCtr][1]);
									  }
									} 
									else {
										service.route({
											origin: path.getAt(path.getLength() - 1),
											destination: latlng,
											travelMode: google.maps.DirectionsTravelMode.DRIVING
										}, function(result, status) {
											if (status == google.maps.DirectionsStatus.OK) {
												for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
													path.push(result.routes[0].overview_path[i]);
													if (input1on == true) { //If "plough is down" then push another green segment onto the current polyline
														vPolys[polyCtr][1].push(result.routes[0].overview_path[i]);
													}
													marker.setPosition(result.routes[0].overview_path[i]);
												}
												miles += result.routes[0].legs[0].distance.value;
											}
										});

										speedPath = path.getAt(path.getLength() - 1).toUrlValue() + "|" + latlng.toUrlValue();
										$.ajax({ // Call Google Roads API Service to get speed limit for this segment https://roads.googleapis.com/v1/speedLimits?path=x,y|x,y&key=AIzaSyBmqqF0Fzzfp7fHphzTcByHYjkzUChGIOI
											url: 'https://roads.googleapis.com/v1/speedLimits', //URL for Google MAPS API Roads service
											data: { path: speedPath, units: "MPH", key: "AIzaSyBmqqF0Fzzfp7fHphzTcByHYjkzUChGIOI" },
											dataType: 'json',
											success: function(response) {
												if(response.status == "OK") {
													postedSpeed = response.results[0].speedLimit + " " + response.results[0].units;
												}
											},
											error: function(XMLHttpRequest, textStatus, errorThrown) {
												alert(errorThrown);
											}
										});
										
									}
								}
								else {
									path.push(latlng); //Push the latest x/y position onto the path.
									poly.setPath(path); //Set the polyline's path to the new extended path.
									marker.setPosition(latlng); //Set the marker's position to the current data element's x/y
									if (input1on == true) {
										vPolys[polyCtr][1].push(latlng);
										vPolys[polyCtr][0].setPath(vPolys[polyCtr][1]);									
									}
								}
					
								contentString = '<div id="content">' + '<div id="vehicleInfo">' + '</div>' + '<h2 id="firstHeading" class="firstHeading">Unit: ' + 
									d[j].vehicle + '</h2> <div id="bodyContent">' + '<br><b>Traveling:</b> ' +
									direction + '<br><b>At:</b> ' +
									d[j].spd + ' MPH' + '<br><b>Status:</b> ' + 
									d[j].ev_type + '<h6><b>Last Update:</b> ' + 
									d[j].gps_ts + '<h6><b>ESN:</b> ' + 
									d[j].esn +
									'<br>' + miles/1609.34 + ' miles' +
									'<br>' + postedSpeed +
									'</h6></div> </div>';  //Generate the infowindow's html content.
								
								google.maps.event.addListener(marker, "click", function () { //listener for the click event on the marker to open the infowindow
									infowindow.setContent(contentString);
									infowindow.open(map, this);
								});
								
								if (j == 0) {
									$( "#slider-1" ).slider( "option", {
										"min": 0, 
										"max": d.length-1
									});
								}
								$( "#slider-1" ).slider("value", j);
								hour = (d[j].msg_ts.substring(11, 13)) - 4;
								if (hour < 0) {
									hour += 24;
								}
								else if (hour >= 0 && hour < 10) {
									hour = "0" + hour;
								}
								min = d[j].msg_ts.substring(14, 16);
								sec = d[j].msg_ts.substring(17, 19);
								thumbStr = hour + ":" + min + ":" + sec;
								
								$.ajax({ //Call to Google Maps geocoding service to reverse geocode the current point.
									url: 'https://maps.googleapis.com/maps/api/geocode/json', //URL for Google MAPS API geocoding service
									data: "latlng=" + d[j].lat + "," + d[j].lon,
									dataType: 'json',
									success: function(response) {
										if(response.status == "OK") {
											titleStr = "Point #" + j.toString() + " @ " + hour + ":" + min + ":" + sec + "\n" + response.results[0].formatted_address + "\n" + miles/1609.34 + " miles";
										}
									},
									error: function(XMLHttpRequest, textStatus, errorThrown) {
										alert(errorThrown);
									}
								});
								
								pointMrkr = new google.maps.Marker({
									position: latlng,
									icon: {
										path: google.maps.SymbolPath.CIRCLE, //PATH can be any SVG path or one of Google maps standards
										fillColor: 'red',
										fillOpacity: 0.8,
										scale: 3,
										strokeColor: 'red',
										strokeWeight: 2
									},
									title: titleStr,
									map: map
								});								
								
								j++;
								if( j == d.length) {
									window.clearInterval(interval); 
									firstTime = 0;
								}
							}, 750);//setTimeout to iterate over data set and move vehicle one position for each iteration.

							if (firstTime == 0) {
								AutoCenter(); // only need to center/zoom once
								firstTime = 1;
							}
							
							function AutoCenter() { // center and zoom the map to fit all the lat/lon points in the data set
								//  Create a new viewpoint bound
								var bounds = new google.maps.LatLngBounds();
								//  Go through each...
								for (var k = 0; k < d.length; k++) {
									var posit = new google.maps.LatLng(d[k].lat, d[k].lon);
									bounds.extend(posit);
								}
								//  Fit these bounds to the map
								map.fitBounds(bounds);
							}
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
		</script>
	</body>
</html>

