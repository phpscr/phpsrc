<?php
session_start();
include 'inc/conn.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>luo_book - PHP留言本</title>
<link href="css/book_style.css" rel="stylesheet" type="text/css" />
<script>
function check()
{
if (document.form1.name.value=="") {
	window.alert("请输入昵称");
	document.form1.name.focus();		
	 return (false);
	}
if (document.form1.content.value=="") {
	window.alert("请输入内容");
	document.form1.content.focus();		
	 return (false);
	}
	return true;
}
</script>
<script>
function checklogin()
{
if (document.form1.username.value=="") {
	window.alert("请输入用户名");
	document.form1.username.focus();		
	 return (false);
	}
if (document.form1.password.value=="") {
	window.alert("请输入密码");
	document.form1.password.focus();		
	 return (false);
	}
	return true;
}
</script>
</head>

<body>
<div class='container'>

<?php
	$var_str="<div class='block'><a href='index.php?act=index'>首页</a> | <a href='index.php?act=write'>我要留言</a> | <a href='index.php?act=view'>查看留言</a> | <a href='index.php?act=login'>管理留言</a></div>";
	$var_str1="<div class='block'><a href='index.php?act=index'>首页</a> | <a href='index.php?act=write'>我要留言</a> | <a href='index.php?act=view'>查看留言</a> | <a href='index.php?act=login'>管理留言</a> |  <a href='index.php?act=quit'>退出</a> | 欢迎您回来：<font color='red'>".$_SESSION['username']."</font></div>";
	//echo $_SESSION['username'];
	$act = getvar('act');
	$id = getvar('id');
	//echo $_SESSION['username'];

	switch($act){
		case "login":
			if ($_SESSION['username']==''){
				echo $var_str;
				loginx();
			}
			else{
				echo $var_str1;
				manage_book_list();
			}
			break;
		case "manage":
			login();
			break;
		case "quit":
			quit();
			break;
		case "write":
			if ($_SESSION['username']==''){
				echo $var_str;
				book_form();
			}
			else{
				echo $var_str1;
				book_form();
			}
			break;
		case "view":
			if ($_SESSION['username']==''){
				echo $var_str;
				book_list();
			}
			else{
				echo $var_str1;
				book_list();
			}
			break;
		case "replay":
			if ($_SESSION['username']==''){
				echo $var_str;
				replay($id);
			}
			else{
				echo $var_str1;
				replay($id);
			}
			break;
		default:
			if ($_SESSION['username']==''){
				echo $var_str;
				indexx();
			}
			else{
				echo $var_str1;
				indexx();
			}
			break;
	}
	
?>
<div class="clear"></div>
</div>
</body>
</html>
