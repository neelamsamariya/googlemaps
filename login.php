<?php 
include('connection.php');
session_start();
{
	$user=$_POST['username'];
	$pass=$_POST['password'];
    $fetch=mysql_query("select * from login where username='$user' and password='$pass'");
    $count=mysql_num_rows($fetch);
	
    if($count!="")
    {
	
    $_SESSION['login_username']=$user;
	while($row = mysql_fetch_assoc($fetch))
	{
		if($row['usertype'] == "A")
		{
			header("Location:dashboard.php");	
		}
		if($row['usertype'] == "U")
		{
			header("Location:dashboard_user.php");	
		}
	}	
    }
    else
    {
	   header('Location:index.php?login');
	}
}
?>
