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
</head>
<body>
<table width="1003" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="109" align="right" valign="top" background="images/bjl2.jpg">&nbsp;</td>
    <td align="center" valign="top"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="40"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td width="48" height="28" background="images/ttl1.jpg">&nbsp;</td>
            <td align="center" background="images/ttm.jpg" class="wfont">���������ǵķ������κβ�������������ԣ�</td>
            <td width="43" height="28" background="images/ttr1.jpg">&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td height="350" align="center" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="0">
			<table width="96%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td width="18" height="16" align="right" valign="bottom"><img src="images/1.jpg" width="18" height="16" /></td>
                <td height="12" background="images/1r.jpg">&nbsp;</td>
                <td width="17" height="16" align="left" valign="bottom"><img src="images/2.jpg" width="17" height="16" /></td>
              </tr>
              <tr>
                <td width="13" background="images/4s.jpg">&nbsp;</td>
                <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td height="7"></td>
                  </tr>
                </table>
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
    <td width="101" rowspan="2" align="center" bgcolor="#FFFFFF"><img src="images/face/face<?php echo $rows["face"]?>.gif" /></td>
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
    <td colspan="2" align="right" bgcolor="#FFFFFF"><a href="mailto:<?php echo $rows["email"]?>" title="<?php echo $rows["email"]?>"><img src="images/face/email.gif" width="16" height="16" border="0"/></a>&nbsp;&nbsp;&nbsp;<a href="<?php echo $rows["homepage"]?>" title="<?php echo $rows["homepage"]?>"><img src="images/face/homepage.gif" width="16" height="16" border="0" /></a>&nbsp;&nbsp;<a href="http://sighttp.qq.com/msgrd?v=1&;uin=<?php echo $rows["qq"]?>%20&site=http://www.qq.com&menu=yes" title="<?php echo $rows["qq"]?>" target="_blank"><img src="images/face/oicq.gif" width="16" height="16"  border="0"/></a>&nbsp;&nbsp;</td>
  </tr>
  <tr>
  	<td colspan="3" bgcolor="#FFFFFF" height="15"></td>
  </tr>
	<?php
	}
  ?>
</table>
<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="40" align="center"><?php $pg=new SubPages($each_disNums,$nums,$current_page,$sub_pages,$subPage_link,$subPage_type);?></td>
  </tr>
</table>
</td>
<td background="images/2x.jpg">&nbsp;</td>
              </tr>
              <tr>
                <td width="18" height="15" align="right" valign="top"><img src="images/4.jpg" width="18" height="15" /></td>
                <td height="12" background="images/3z.jpg">&nbsp;</td>
                <td width="17" height="15" align="left" valign="top"><img src="images/3.jpg" width="17" height="15" /></td>
              </tr>
            </table>
			  </td>
          </tr>
        </table></td>
      </tr>
    </table></td>
    <td width="108" align="left" valign="top" background="images/bjr2.jpg">&nbsp;</td>
  </tr>
</table>
<table width="1003" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="114" valign="top" background="images/xm.jpg"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="55" align="center" class="wfont"><?php echo $db->ly_system("system",60)?>
          <!-- Դ�ļ��ڲ��ϸ����У���Դ����ѿ�Դ��������Ȩ��Ϣ�����Ƕ�ԭ���ߵ�һ��֧�֣���������Ի�ñ�վ��Ѽ���֧�ֺ�ԭ������������-->
          <script type="text/javascript" src="http://www.xiariboke.com/net/cpt.js"></script></td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>
