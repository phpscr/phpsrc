<?php 
require_once('ly_check.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>�鿴����</title>
<link rel="stylesheet" href="images/css.css" type="text/css">
</head>
<body>
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table">
  <tr>
    <td class="bg_tr">&nbsp;���Թ���</td>
  </tr>
</table>
<?php
	$sql="select * from leavewords order by id desc";
	$rs=mysql_query($sql);
	$recordcount=mysql_num_rows($rs);
	$pagesize=4;
	$pagecount=($recordcount-1)/$pagesize+1;
	$pagecount=(int)$pagecount;
	$pageno=$_GET["pageno"];
	if($pageno<1)
	{
		$pageno=1;
	}
	if($pageno>$pagecount)
	{
		$pageno=$pagecount;
	}
	$startno=($pageno-1)*$pagesize;
	$sql="select * from leavewords order by id desc limit $startno,$pagesize";
	$rs=mysql_query($sql);
?>
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="1" class="td_bgf">
<?php
	while ($rows=mysql_fetch_assoc($rs))
	{
	?>
	  <tr>
    <td class="td_bg">���Ա���:<?php echo $rows["leave_title"]?></td>
    <td class="td_bg">����ʱ��:<?php echo $rows["leave_time"]?></td>
  </tr>
  <tr>
    <td colspan="2" class="td_bg">��������:<br />
    <?php echo $rows["leave_contents"]?></td>
  </tr>
  <tr>
    <td class="td_bg">�ǳ�:<?php echo $rows["username"]?></td>
    <td colspan="2" class="td_bg">
	<input type="button" class="button" onClick="location.href='bbs_reply.php?id=<?php echo $rows["id"]?>&pageno=<?php echo $pageno?>'" value="�ظ�" />
	<input type="button" class="button" onClick="location.href='bbs_del.php?id=<?php echo $rows["id"]?>&pageno=<?php echo $pageno?>'" value="ɾ��" />
	<input name="Submit" type="button" class="button" onClick="location.href='bbs_lock.php?ip=<?php echo $rows["ip"]?>&pageno=<?php echo $pageno?>'" value="����" />
	<input name="Submit2" type="button" class="button" onClick="location.href='bbs_unlock.php?ip=<?php echo $rows["ip"]?>&pageno=<?php echo $pageno?>'" value="����"  />
	<input name="Submit3" type="button" class="button" onClick="location.href='bbs_is_audit.php?id=<?php echo $rows["id"]?>&pageno=<?php echo $pageno?>'" value="<?php if($rows["is_audit"]=='1'){echo "�����";}else{echo "δ���";}?>"  />
	</td>
  </tr>
  <tr>
  	<td colspan="3" class="td_bg">
	�ظ�����:<br/>
	<?php
		$sql="select * from reply where leaveid=".$rows['id']." order by id desc";
		$rs_reply=mysql_query($sql);
		if(mysql_num_rows($rs_reply)==0)
		{
			echo "<span style=color:red>���޻ظ�</span>";
		}
		else
		{
			while ($rows_reply=mysql_fetch_assoc($rs_reply))
			{
				?>
				<?php echo "<font color='red'>" .$rows_reply["reply_contents"]?><br/>
				<?php
			}
		}
	?>
	</td>
  </tr>
	<?php
	}
?>
<tr>
<td colspan="3" align="center" class="bg_tr">
<?php
		for($i=1;$i<=$pagecount;$i++)
		{
		?>
		<a href="?pageno=<?php echo $i?>"><?php echo $i?></a>
		<?php
		}
	?>	
	<?php
		if($pageno==1)
		{
		?>
		��ҳ | ��ҳ | <a href="?pageno=<?php echo $pageno+1?>">��ҳ</a> | <a href="?pageno=<?php echo $pagecount?>">ĩҳ</a>
		<?php
		}
		else if($pageno==$pagecount)
		{
		?>
		<a href="?pageno=1">��ҳ</a> | <a href="?pageno=<?php echo $pageno-1?>">��ҳ</a> | ��ҳ | ĩҳ
		<?php
		}
		else
		{
		?>
		<a href="?pageno=1">��ҳ</a> | <a href="?pageno=<?php echo $pageno-1?>">��ҳ</a> | <a href="?pageno=<?php echo $pageno+1?>">��ҳ</a> | <a href="?pageno=<?php echo $pagecount?>">ĩҳ</a>
		<?php
		}
	?></td>
</tr>
</table>
</body>
</html>
