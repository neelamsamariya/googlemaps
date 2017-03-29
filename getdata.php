 <?php
include "connection.php";

$info=array();
$sql = mysql_query($_GET['q']);
$info['fields']['gmap_id']['type'] = 'number'; 
$info['fields']['geoj']['type'] = 'string'; 
$info['total_rows'] = mysql_num_rows($sql);
$i=0;
while($row=mysql_fetch_array($sql,MYSQL_ASSOC))
{
//array_push($info['rows'],$row);
$info['rows'][$i] = $row;
$i++;
}

echo json_encode($info);

?>