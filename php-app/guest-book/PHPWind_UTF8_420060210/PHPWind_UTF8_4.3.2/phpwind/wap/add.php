<?php
require_once('global.php');

list($db_openpost,$db_poststart,$db_postend)=explode("\t",$db_openpost);
if($db_openpost==1 && $db_poststart<$db_postend && ($t['hours']<$db_poststart || $t['hours']>$db_postend)){
	wap_msg("���ڹ���������ʱ��ԭ��վ�㿪������ʱ��Ϊ $db_poststart:00 �㵽 $db_postend:00 ��");
}

$fids=array();
$query=$db->query("SELECT fid FROM pw_forums WHERE password='' AND allowvisit='' AND f_type!='hidden'");
while($rt=$db->fetch_array($query)){
	$fids[]=$rt['fid'];
}

$cates='<option value="0"></option>';
foreach($forum as $key => $value){
	if(in_array($key,$fids) && $value['type']!='category' && !$value['cms']){
		$add=$value['type']=='forum' ? "&gt;" : ($forum[$value['fup']]['type']=='forum' ? "&gt;&gt;" : "&gt;&gt;&gt;");
		$cates.="<option value=\"$key\">$add$value[name]</option>\n";
	}
}

wap_header('add',$db_bbsname);
wap_output("<p align=\"center\">�������<br/></p>\n");
wap_output("<p>�û�:<input name=\"username\" type=\"text\" /></p>\n");
wap_output("<p>����:<input name=\"password\" type=\"text\" /></p>\n");
wap_output("<p>����:<input name=\"subject\" type=\"text\" /></p>\n");
wap_output("<p>����:<input name=\"content\" type=\"text\" /></p>\n");
if($tid){
	$refer="addpost.php?tid=$tid";
} else{
	$refer='addtopic.php';
	wap_output("<p>����:<select name=\"fid\">\n");
	wap_output($cates);
	wap_output("</select></p>\n");
}
wap_output("<p align=\"center\">\n");
wap_output("<anchor title=\"submit\">ȷ��\n");
wap_output("<go href=\"$refer\" method=\"post\">\n");
wap_output("<postfield name=\"username\" value=\"$(username)\" />\n");
wap_output("<postfield name=\"password\" value=\"$(password)\" />\n");
wap_output("<postfield name=\"subject\" value=\"$(subject)\" />\n");
wap_output("<postfield name=\"content\" value=\"$(content)\" />\n");
wap_output("<postfield name=\"fid\" value=\"$(fid)\" />\n");
wap_output("</go></anchor>\n");
wap_output("</p>\n");
wap_footer();
?>