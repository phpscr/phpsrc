<?php
require_once('global.php');

list($db_openpost,$db_poststart,$db_postend)=explode("\t",$db_openpost);
if($db_openpost==1 && $db_poststart<$db_postend && ($t['hours']<$db_poststart || $t['hours']>$db_postend)){
	wap_msg("由于工作力度与时间原因，站点开放评论时间为 $db_poststart:00 点到 $db_postend:00 点");
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
wap_output("<p align=\"center\">添加文章<br/></p>\n");
wap_output("<p>用户:<input name=\"username\" type=\"text\" /></p>\n");
wap_output("<p>密码:<input name=\"password\" type=\"text\" /></p>\n");
wap_output("<p>标题:<input name=\"subject\" type=\"text\" /></p>\n");
wap_output("<p>内容:<input name=\"content\" type=\"text\" /></p>\n");
if($tid){
	$refer="addpost.php?tid=$tid";
} else{
	$refer='addtopic.php';
	wap_output("<p>分类:<select name=\"fid\">\n");
	wap_output($cates);
	wap_output("</select></p>\n");
}
wap_output("<p align=\"center\">\n");
wap_output("<anchor title=\"submit\">确定\n");
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