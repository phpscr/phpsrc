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
	<a href="fk1.php">我要进行留言</a>&nbsp;&nbsp;</td>
</tr>
 <?php
//使用方法：
 if($db->ly_system("system",7)==1){
 $sql="select * from leavewords where is_audit=1 order by id desc"; // 查询的数据库表
 }else{
 $sql="select * from leavewords order by id desc"; // 查询的数据库表 
 }
 $queryc=mysql_query($sql); //执行SQL语句
 $nums=mysql_num_rows($queryc); ////总条目数
 $each_disNums=$page=$db->ly_system("system",6);  //每页显示的条目数 
 $sub_pages=2; //当 $subPage_type 为 2 时，每次显示的页数
 $pageNums = ceil($nums/$each_disNums); //总页数
 $subPage_link="index.php?&page="; //每个分页的链接
 $subPage_type=1;//为1时,显示结果1,为2时,显示结果2,显示分页的类型
 $current_page=$_GET['page']!=""?$_GET['page']:1; //当前被选中的页
 $currentNums=($current_page-1)*$each_disNums; //limit计算来用
 if($db->ly_system("system",7)==1){
 $sql="select * from leavewords where is_audit=1 order by id desc limit $currentNums,$each_disNums"; //SQL语句，此处分页计算
 }else{
 $sql="select * from leavewords order by id desc limit $currentNums,$each_disNums"; //SQL语句，此处分页计算
 }
 $query=mysql_query($sql); // 执行SQL主句
 while($rows=mysql_fetch_array($query)){
 ?>
  <tr>
    <td width="304" align="left" bgcolor="#FFFFFF">留言标题:<?php  if($db->ly_system("system",8)==1){echo strip_tags($rows["leave_title"]);}else{echo $rows["leave_title"];}?></td>
    <td width="211" align="left" bgcolor="#FFFFFF">发表于:<?php echo $rows["leave_time"]?></td>
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
			echo "<span style='color:red'>暂无回复</span>";
		}
		else
		{
			while($rows_reply=mysql_fetch_assoc($rs_reply))
			{
				?>
				<table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
  <tr>
    <td bgcolor="#F2F2F2"><?php echo "<font color='red'>管理员回复:</font><br>".$rows_reply['reply_contents']."<br>";?></td>
  </tr>
</table>

				<?php
			}
		}
	?></td>
  </tr>
  <tr>
    <td align="center" bgcolor="#FFFFFF">昵称:<?php echo $rows["username"]?></td>
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
