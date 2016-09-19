<?php
if(isset($_GET['infoid']))
{
	//$Id: mdbchina.php 5 2006-06-02 04:13:15Z USE20 $
	$infoid=$_GET['infoid'];
	$a=join('', file('http://www.mdbchina.com/cmd.asp?cmd=addplace&typeno=7&infoid='.$infoid));
	$a=addslashes($a);
	$a=preg_replace("/(\s*?\r?\n\s*?)+/", "\\n\\n", $a);
	echo <<<HERE
<script language='javascript'>
var isIE=window.ActiveXObject?true:false;
var res='$a';
if(isIE)
	window.returnValue=res;
else
	top.opener.mdbchinaInsert(res);
top.close()
</script>
HERE;
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>欢迎使用中国影视库资源搜索</title>
</head>
<frameset rows="*" cols="*" framespacing="0" frameborder="no" border="0">
  <frame src="mdbget.php" name="mainFrame" id="mainFrame" title="mainFrame" />
</frameset>
<noframes><body>
</body>
</noframes></html>