<?php
require_once('global.php');
include_once(R_P.'wap/u2gb_mod.php');
require_once(R_P.'require/postfunc.php');
require_once(R_P.'require/forum.php');

if(!$subject || !$content){
	wap_msg("主题或内容为空!");
}elseif(!$fid){
	wap_msg("没有选择分类!");
} else{

	$username = wap_cv($username);
	$username = U2GB($username);
	$winddb   = postcheck($username,$password,$fid,'new');

	$subject = wap_cv($subject);
	$content = wap_cv($content);
	$subject = U2GB($subject);
	$content = U2GB($content);
	$ipfrom  = cvipfrom($onlineip);
	$db->update("INSERT INTO pw_threads(fid,ifcheck,subject,author,authorid,postdate,lastpost,lastposter) VALUES('$fid','1','$subject','$username','$winddb[uid]','$timestamp','$timestamp','$username')");
	$tid=$db->insert_id();
	$db->update("INSERT INTO pw_tmsgs(tid,content,userip,ipfrom) VALUES('$tid','$content','$onlineip','$ipfrom')");

	$lastpost=$subject."\t".$username."\t".$timestamp."\t"."read.php?tid=$tid&page=e#a";
	$topicadd=",tpost=tpost+1,article=article+1,topic=topic+1";
	$db->update("UPDATE pw_forumdata SET lastpost='$lastpost' $topicadd WHERE fid='$fid'");

	$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid='$fid'");
	$creditset = get_creditset($creditset,$db_creditset);
	$addrvrc  = $creditset['rvrc']['Post'];
	$addmoney = $creditset['money']['Post'];
	customcredit($winddb['uid'],$creditset,'Post');

	$db->update("UPDATE pw_memberdata SET postnum=postnum+1,todaypost=todaypost+1,lastpost='$timestamp',money=money+'$addmoney',rvrc=rvrc+'$addrvrc' WHERE uid='$winddb[uid]'");
	wap_msg("主题发表成功!","read.php?tid=$tid");
}
?>