<?php 
	require_once('ly_check.php');
	$ip=$_GET["ip"];
	$pageno=$_GET["pageno"];
	$sql="select * from lockip where lockip='$ip'";
	$rs=mysql_query($sql);
	if(mysql_num_rows($rs)==0)
	{
		$sql="insert into lockip (lockip) values ('$ip')";
		mysql_query($sql);
		?>
		<script language="javascript">
			alert("�����ɹ�");
			location.href="bbs_admin.php?pageno=<?php echo $pageno?>"
		</script>
		<?php
	}
	else
	{
	?>
	<script language="javascript">
		alert("��ip�Ѿ�������")
		location.href="bbs_admin.php?pageno=<?php echo $pageno?>"
	</script>
	<?php
	}
?>
