<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Google Maps</title>
<style type="text/css">
.contain { width:500px; margin:0 auto; border:2px green dashed; margin-top:15px; min-height:200px; padding:10px; }
input { font-size:15px; color:#06C; padding:10px; width:200px; margin-top:25px;   }
.labelname { font-size:20px; font-weight:bold; font-family:Tahoma, Geneva, sans-serif; color:#33C; padding:10px; }
#submit { width:100px; height:30px; padding:2px; background-color:#999;}
h2 { color:#F00; font-size:22px; background-color:#CCC; padding:10px; }
h4 { color:#333; font-size:18px;}
</style>
</head>

<body>
<?php
if(isset($_GET['login']))
{
	echo '<h2 align="center"> Invalid username or password </h2>';
}
?>

<div class="contain" align="center">
<h2>Google Maps</h2>
<form method="post" name="login" action="login.php">
<label for="name" class="labelname"> Username </label>
<input type="text" name="username" id="userid" required="required" /><br />
<label for="name" class="labelname"> Password </label>
<input type="password" name="password" id="passid" required="required"  /><br />
<input type="submit" name="submit" id="submit"  value="Login" />
</form>
</div>
</body>
</html>