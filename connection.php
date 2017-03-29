<?php

$dbhost='';
$dbuser='';
$dbpass='';
$con=mysql_connect($dbhost,$dbuser,$dbpass)
or die('could not connect to mysql');
$db='googlemap';
mysql_select_db($db);
?>
