<?php
require_once('ly_check.php');
	$ip=$_GET["ip"];
	$pageno=$_GET["pageno"];
	$sql="select * from lockip where lockip='$ip'";
	$rs=mysql_query($sql);
	if(mysql_num_rows($rs)==0)
	{
		?>
		<script language="javascript">
			alert("��ipû�б�����");
			location.href="bbs_admin.php?pageno=<?php echo $pageno?>"
		</script>
		<?php
	}
	else
	{
		$sql="delete from lockip where lockip='$ip'";
		mysql_query($sql);
		?>
		<script language="javascript">
			alert("�����ɹ�");
			location.href="bbs_admin.php?pageno=<?php echo $pageno?>"
		</script>
		<?php
	}
?>

