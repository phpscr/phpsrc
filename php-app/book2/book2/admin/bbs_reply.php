<?php 
require_once('ly_check.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>�鿴����</title>
</head>
<body>
<?php
	$id=$_GET["id"];
	$pageno=$_GET["pageno"];
	if($_POST["Submit2"])
	{
		$content=$_POST["content"];
		$sql="update reply set reply_contents='$content' where leaveid=$id";
		mysql_query($sql);
		header("location:bbs_admin.php?pageno=$pageno");
	}
	$sql="select * from leavewords where id=$id";
	$rs=mysql_query($sql);
	$rows=mysql_fetch_assoc($rs);
?>
<?php
	$id=$_GET["id"];
	$pageno=$_GET["pageno"];
	if($_POST["Submit"])
	{
		$content=$_POST["content"];
		
		$sql="insert into reply (leaveid,leaveuser,reply_contents) values ($id,'����Ա','$content')";
		mysql_query($sql);
		echo "<script language=javascript>alert('�ظ��ɹ���');window.location='bbs_admin.php?pageno=$pageno'</script>";
	}
	$sql="select * from leavewords where id=$id";
	$rs=mysql_query($sql);
	$rows=mysql_fetch_assoc($rs);
?>
		 <?php
	  	$sql="select * from reply where leaveid=".$rows["id"]." order by id desc";
		$rs_reply=mysql_query($sql);
		if(mysql_num_rows($rs_reply)==0)
		{
		?>
<form id="form1" name="form1" method="post" action="" onsubmit="return CheckForm()">
  <table width="100%" border="0" align="center" cellpadding="5" cellspacing="1" bordercolor="#000000" bgcolor="#cccccc">
    <tr>
      <th colspan="2" bgcolor="#FFFFFF">����Ա�ظ�</th>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#FFFFFF" class="forumRowHighlight"><?php echo $rows["leave_contents"]?></td>
    </tr>
    <tr>
      <td bgcolor="#FFFFFF" class="forumRowHighlight">�ظ�����:</td>
      <td bgcolor="#FFFFFF" class="forumRowHighlight">&nbsp;</td>
    </tr>
	<tr>
		<td colspan="2" bgcolor="#FFFFFF" class="forumRowHighlight">
			  </td>
	</tr>
    <tr>
      <td colspan="2" align="left" bgcolor="#FFFFFF" class="forumRowHighlight"><label>
        <textarea name="content" cols="35" rows="5">���޻ظ�����</textarea>
      </label></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input name="Submit" type="submit" class="button" value="�ύ" />
      &nbsp;
      <input name="Submit2" type="reset" class="button" value="����" /></td>
    </tr>
  </table> 
  <?php
			
		}
		else
		{
		?>
				<?php
		
			while($rows_reply=mysql_fetch_assoc($rs_reply))
			{
			?>
		<form id="form1" name="form1" method="post" action="" onSubmit="return CheckForm()">
  <table width="100%" border="0" align="center" cellpadding="5" cellspacing="1" bordercolor="#000000" bgcolor="#cccccc">
    <tr>
      <th colspan="2" bgcolor="#FFFFFF">����Ա�ظ�</th>
    </tr>
    
	<tr>
		<td colspan="2" bgcolor="#FFFFFF" class="forumRowHighlight">			  </td>
	</tr>
    <tr>
      <td colspan="2" align="left" bgcolor="#FFFFFF" class="forumRowHighlight"><label>
        <textarea name="content" cols="35" rows="5"><?php echo $rows_reply["reply_contents"]?></textarea>
      </label></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input name="Submit2" type="submit" class="button" id="Submit2" value="�ύ" />
      &nbsp;
      <input name="Submit2" type="reset" class="button" value="����" /></td>
    </tr>
  </table>		
  <?php
			}
		}
	  ?>	
</form>
</body>
</html>