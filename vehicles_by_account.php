<?php
  date_default_timezone_set('America/New_York');
  //--------------------------------------------------------------------------
  // 1) Connect to mysql database
  //--------------------------------------------------------------------------
  include 'DB.php';												//PHP page containing db name and credentials
  $con = mysql_connect($host,$user,$pass);
  $dbs = mysql_select_db($databaseName, $con);
  $qDate = $_REQUEST['date'];
  $qAcct = $_REQUEST['account'];
  
  //--------------------------------------------------------------------------
  // 2) Query database for data
  //--------------------------------------------------------------------------
  $result = mysql_query("select distinct d.name, d.esn from tblreadingmsg m JOIN tblDevice d on m.deviceID = d.deviceID JOIN tblDeviceAccount da on d.deviceID = da.deviceID and da.accountID = '$qAcct' where m.msgTimeStamp is not null and left(m.msgTimeStamp, 10) = '$qDate'");
  $json = [];
	
  if (mysql_num_rows($result)!=0) { 
	  while($row = mysql_fetch_assoc($result)){						//fetch result as recordset (associative array)
		   $json[] = $row;
	  }

  }
  echo json_encode($json);

?>