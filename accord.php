
<!DOCTYPE html>
<html>
<head>
  <!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>-->
  <!--<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">-->
  <!--<script src="//code.jquery.com/jquery-1.10.2.js"></script>-->
  <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>-->
  <!--<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>-->
  <!--<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>-->
  <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
  
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    
  
  <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  
         <style type='text/css'>
            html, body {
                width: 100%;
                height: 100%;
            }

		#mapCanvas {
			border:  1px dashed #777;
			width:  500px;
			height:  350px;
			margin:  0 auto;
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
        </style>
  
  <script>
var map = null;  
var latlng = new google.maps.LatLng(32.334167, -95.3);
function initializeMap() {
	var myOptions = {
		zoom: 8,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	return new google.maps.Map($('#mapCanvas')[0], myOptions);
}

$(document).ready(function() {
	
	
	//$("#accordion").accordion();
	
	$('#accordion').on('show.bs.collapse', function () {
		map = initializeMap();
	});
	
	$('#accordion').on('hide.bs.collapse', function () {
		map = initializeMap();
	});
	/*
	$("#accordion").bind('accordionchange', function(event, ui) {
		if (ui.newContent.attr('id') == 'tabOne' && !map)
		{
			
		}
	});
	*/
	$( "#select" ).selectmenu();
	
	$("#datePicker").datepicker({
		dateFormat: 'yy-mm-dd', 
		onSelect: function(selectedDate, picker) {
		//$( "#select" ).selectmenu();
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
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		}
	});
});
  </script>
  
</head>
<body style="font-size:62.5%;">
 <div class="panel-group" id="accordion">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
          Collapsible Group Item #1
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in">
      <div class="panel-body">
	  		<h4>Map to Somewhere</h4>
		<div id="mapCanvas"></div>
	  
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Collapsible Group Item #2
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse">
      <div class="panel-body">
			<input type = "text" id="datePicker" value="Select a date:">
			<select name="select" id="select"></select>
      </div>
    </div>
  </div>
</div>
<!-- 
<div id="accordion">
	<h3><a href="#">Content 1</a></h3>
	<div id="tabOne">
		<h4>Map to Somewhere</h4>
		<div id="mapCanvas"></div>
	</div>

	<h3><a href="#">Map On This Tab</a></h3>
	<div id="tabThree">
		<p>
			<input type = "text" id="datePicker" value="Select a date:">
			<select name="select" id="select"></select>
		</p>	
	</div>

</div>
-->
</body>



