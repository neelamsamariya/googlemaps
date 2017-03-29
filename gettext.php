 <?php
include "connection.php";
if($_POST) //run only if there's a post data
{
	//Delete Marker
	if(isset($_POST["del"]) && $_POST["del"]==true)
	{
		
		$results = mysql_query("update write_shapes set text = NULL WHERE gmap_id = ".$_POST['id']);
		
		echo mysql_error();
		exit("Done!");
	}
	
}
if((isset($_GET['action'])) && ($_GET['action'] == "gettext"))
{
	
	$sql = mysql_query("select text from write_shapes where gmap_id=".$_GET['id']);
	$row = mysql_fetch_row($sql);
	if($row)
	{
		echo $row[0];
	}
	else{
		echo "";
	}
	
}
?>