<?php
  date_default_timezone_set('America/New_York');
  //--------------------------------------------------------------------------
  // 1) Connect to mysql database
  //--------------------------------------------------------------------------
  include 'DB.php';												//PHP page containing db name and credentials
  $con = mysql_connect($host,$user,$pass);
  $dbs = mysql_select_db($databaseName, $con);
  $qDate = $_REQUEST['date'];
  
  //echo $qDate;
  //--------------------------------------------------------------------------
  // 2) Query database for data
  //--------------------------------------------------------------------------
  //$result = mysql_query("SELECT * FROM $tableName");          //query
  //$result = mysql_query("select left(msgTimeStamp, 10) as date, device_name from tblreadingmsg where msgTimeStamp is not null group by device_name, left(msgTimeStamp, 10)");
  $result = mysql_query("select distinct d.name, d.esn from tblreadingmsg m JOIN tblDevice d on m.deviceID = d.deviceID where m.msgTimeStamp is not null and left(m.msgTimeStamp, 10) = '$qDate'");
  //$result = mysql_query("select distinct device_name from tblreadingmsg where msgTimeStamp is not null and left(msgTimeStamp, 10) = '2014-08-22'");
  //$array = mysql_fetch_row($result);                          //fetch result
  $json = [];
	
  if (mysql_num_rows($result)!=0) { 
	  while($row = mysql_fetch_assoc($result)){						//fetch result as recordset (associative array)
		   $json[] = $row;
	  }

	  //--------------------------------------------------------------------------
	  // 3) echo result as json
	  //--------------------------------------------------------------------------
	  //echo json_encode($array);
  }
  echo json_encode($json);

?>