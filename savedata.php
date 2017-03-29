<?php
include "connection.php";
if(isset($_POST['gmap_id']))
{
	
	$result=mysql_query("update write_shapes set geoj ='".$_POST['geojson']."' where gmap_id = ".$_POST['gmap_id']);
	if($result)
	{
		echo $_POST['gmap_id'];
	}
}
else
{
	
	$result = mysql_query("insert into write_shapes set geoj ='".$_POST['geojson']."'");
	$id = mysql_insert_id();
	if($result)
	{
		echo $id;
	}
}

?>   