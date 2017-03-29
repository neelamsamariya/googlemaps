<?php
include "connection.php";
$values = "";
if((isset($_POST['id'])) && (isset($_POST['action'])))
{
	
	$sql = mysql_query("select text from write_shapes where gmap_id=".$_POST['id']);
	$row = mysql_fetch_row($sql);
	$values = $row[0];	
	$result=mysql_query("delete from write_shapes where gmap_id = ".$_POST['id']);
	//echo $values;
	if($result)
	{
		echo $values;
	}
}
?>   