<?php
	require("include/global.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link href="images/style.css" rel="stylesheet" type="text/css" />
<title><?php echo $db->ly_system("system",2)?></title>
<META name=keywords content="<?php echo $db->ly_system("system",3)?>">
<meta name="description" content="<?php echo $db->ly_system("system",4)?>">
</head>
<body>
<table width="98%" border="0" align="center" cellpadding="5" cellspacing="1" bordercolor="#000000" bgcolor="#CCCCCC">
<tr>
	<td colspan="3" align="right" bgcolor="#FFFFFF">
	<a href="fk1.php">��Ҫ��������</a>&nbsp;&nbsp;</td>
</tr>
 <?php
//ʹ�÷�����
 if($db->ly_system("system",7)==1){
 $sql="select * from leavewords where is_audit=1 order by id desc"; // ��ѯ�����ݿ��
 }else{
 $sql="select * from leavewords order by id desc"; // ��ѯ�����ݿ�� 
 }
 $queryc=mysql_query($sql); //ִ��SQL���
 $nums=mysql_num_rows($queryc); ////����Ŀ��
 $each_disNums=$page=$db->ly_system("system",6);  //ÿҳ��ʾ����Ŀ�� 
 $sub_pages=2; //�� $subPage_type Ϊ 2 ʱ��ÿ����ʾ��ҳ��
 $pageNums = ceil($nums/$each_disNums); //��ҳ��
 $subPage_link="index.php?&page="; //ÿ����ҳ������
 $subPage_type=1;//Ϊ1ʱ,��ʾ���1,Ϊ2ʱ,��ʾ���2,��ʾ��ҳ������
 $current_page=$_GET['page']!=""?$_GET['page']:1; //��ǰ��ѡ�е�ҳ
 $currentNums=($current_page-1)*$each_disNums; //limit��������
 if($db->ly_system("system",7)==1){
 $sql="select * from leavewords where is_audit=1 order by id desc limit $currentNums,$each_disNums"; //SQL��䣬�˴���ҳ����
 }else{
 $sql="select * from leavewords order by id desc limit $currentNums,$each_disNums"; //SQL��䣬�˴���ҳ����
 }
 $query=mysql_query($sql); // ִ��SQL����
 while($rows=mysql_fetch_array($query)){
 ?>
  <tr>
    <td width="304" align="left" bgcolor="#FFFFFF">���Ա���:<?php  if($db->ly_system("system",8)==1){echo strip_tags($rows["leave_title"]);}else{echo $rows["leave_title"];}?></td>
    <td width="211" align="left" bgcolor="#FFFFFF">������:<?php echo $rows["leave_time"]?></td>
  </tr>
  <tr>
    <td colspan="2" align="left" valign="top" bgcolor="#FFFFFF">
	
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td><?php if($db->ly_system("system",8)==1){echo strip_tags($rows["leave_contents"]);}else{echo $rows["leave_contents"];}?></td>
  </tr>
</table>

	
    <?php
		$id=$rows["id"];
		$sql="select * from reply where leaveid=$id order by id desc";
		$rs_reply=mysql_query($sql);
		if(mysql_num_rows($rs_reply)==0)
		{
			echo "<span style='color:red'>���޻ظ�</span>";
		}
		else
		{
			while($rows_reply=mysql_fetch_assoc($rs_reply))
			{
				?>
				<table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
  <tr>
    <td bgcolor="#F2F2F2"><?php echo "<font color='red'>����Ա�ظ�:</font><br>".$rows_reply['reply_contents']."<br>";?></td>
  </tr>
</table>

				<?php
			}
		}
	?></td>
  </tr>
  <tr>
    <td align="center" bgcolor="#FFFFFF">�ǳ�:<?php echo $rows["username"]?></td>
    <td colspan="2" align="right" bgcolor="#FFFFFF">
	<a href="mailto:<?php echo $rows["email"]?>" title="<?php echo $rows["email"]?>"><?php echo $rows["email"]?></a>&nbsp;&nbsp;&nbsp;
	<a href="<?php echo $rows["homepage"]?>" title="<?php echo $rows["homepage"]?>"><?php echo $rows["homepage"]?></a>&nbsp;&nbsp;
	<?php echo $rows["qq"]?>&nbsp;&nbsp;</td>
  </tr>
  <tr>
  	<td colspan="3" bgcolor="#FFFFFF" height="15"></td>
  </tr>
	<?php
	}
  ?>
</table>


<p align="center"><?php $pg=new SubPages($each_disNums,$nums,$current_page,$sub_pages,$subPage_link,$subPage_type);?></p>
<hr><p align="center"><?php echo $db->ly_system("system",60)?></p>

</body>
</html>
