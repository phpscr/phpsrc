<?php
	require("include/global.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title><?php echo $db->ly_system("system",2)?></title>
<META name=keywords content="<?php echo $db->ly_system("system",3)?>">
<meta name="description" content="<?php echo $db->ly_system("system",4)?>">
<link href="images/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="images/check.js"></script>
</head>
<body>
<table width="100%" height="100%" border="0" align="center" cellpadding="8" cellspacing="0">
        <tr>
          <td height="470" align="left" valign="top" bgcolor="#FFFFFF"><?php
	$ip=$_SERVER['REMOTE_ADDR'];
	$sql="select * from lockip where lockip='$ip'";
	$rs=mysql_query($sql);
	if(mysql_num_rows($rs)>0)
	{
		?>
		<script language="javascript">
			alert("��Ǹ!���Ѿ�������Ա����,������Ϊ�������˲���������!\n�������Ա��ϵ");
			location.href="index.php"
		</script>
		<?php
		die();
	}
	if($_POST["Submit"])
	{
		$username=$_POST["username"];
		$qq=$_POST["qq"];
		$email=$_POST["email"];
		$homepage=$_POST["homepage"];
		$face=$_POST["face"];
		$title=$_POST["title"];
		$content=$_POST["content"];
		$time=date('Y-m-d H:i:s');
		$ip=$_SERVER['REMOTE_ADDR'];
		$sql="insert into leavewords (username,qq,email,homepage,face,leave_title,leave_contents,leave_time,ip) values ('$username',$qq,'$email','$homepage','$face','$title','$content','$time','$ip')";
		mysql_query($sql);
		echo "<script language=javascript>alert('����������������У����Եȣ�');window.location='index.php'</script>";
		?>
		<?php
	}
?>
<form id="form1" name="form1" method="post" action="" onsubmit=return(CheckInput()) style="margin-top:0px;">
  <table width="100%" border="0" align="center" cellpadding="5" cellspacing="1" bordercolor="#000000" bgcolor="#CCCCCC">
    <tr>
      <td colspan="2" align="right" bgcolor="#FFFFFF"><a href="index.php">�������԰�</a>&nbsp;&nbsp;</td>
    </tr>
    <tr>
      <th colspan="2" bgcolor="#FFFFFF">�������(����ɫ * ��Ϊ������)</th>
    </tr>
    <tr>
      <td width="74" align="center" bgcolor="#FFFFFF">�����ǳ�:</td>
      <td width="604" bgcolor="#FFFFFF"><input name="username" type="text" id="username" />
        &nbsp;<span class="style1">*</span></td>
    </tr>
    <tr>
      <td align="center" bgcolor="#FFFFFF">���ѿۿ�:</td>
      <td bgcolor="#FFFFFF"><input name="qq" type="text" id="qq" /></td>
    </tr>
    <tr>
      <td align="center" bgcolor="#FFFFFF">��������:</td>
      <td bgcolor="#FFFFFF"><input name="email" type="text" id="email" />
        &nbsp;<span class="style1">*</span></td>
    </tr>
    <tr>
      <td align="center" bgcolor="#FFFFFF">������ҳ:</td>
      <td bgcolor="#FFFFFF"><input name="homepage" type="text" id="homepage" value="http://" /></td>
    </tr>
    <input type="hidden" value="1" name="face" checked="checked" />
    <tr>
      <td align="center" bgcolor="#FFFFFF">���Ա���:</td>
      <td bgcolor="#FFFFFF"><input name="title" type="text" id="title" />
        &nbsp;<span class="style1">*</span></td>
    </tr>
	<tr>
		<td colspan="2" bgcolor="#FFFFFF">
		&nbsp;��������:		</td>
	</tr>
    <tr>
      <td colspan="2" bgcolor="#FFFFFF"><textarea name="content" cols="60" rows="5"></textarea></td>
    </tr>
    <tr>
      <td colspan="2" align="center" bgcolor="#FFFFFF"><input type="submit" name="Submit" value="�ύ" />
      <input type="reset" name="Submit2" value="����" /></td>
    </tr>
  </table>
</form></td>
        </tr>
      </table>
	  
<hr><p align="center"><?php echo $db->ly_system("system",60)?></p>

</body>
</html>
