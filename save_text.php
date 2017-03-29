<?php
include "connection.php";
if($_POST) //run only if there's a post data
{
	$data_values = array();
	// get marker position and split it for database
	$mLatLang	= explode(',',$_POST["latlang"]);
	$mLat 		= filter_var($mLatLang[0], FILTER_VALIDATE_FLOAT);
	$mLng 		= filter_var($mLatLang[1], FILTER_VALIDATE_FLOAT);
	$mName 		= filter_var($_POST["name"], FILTER_SANITIZE_STRING);	
	$data_values['mLat'] = $mLat;
	$data_values['mLng'] = $mLng;
	$data_values['mName'] = $mName;	
	$final_text = json_encode($data_values);
	//echo "update write_shapes set text='".$final_text."' where gmap_id=".$_POST['id'];
	$results = mysql_query("update write_shapes set text='".$final_text."' where gmap_id=".$_POST['id']);
	if (!$results) {  
		  header('HTTP/1.1 500 Error: Could not create marker!'); 
		  exit();
	} 
	
	$output = '<h1 class="marker-heading">'.$mName.'</h1>';
	exit($output);
}
################ Continue generating Map XML #################


?>