<?php
require_once('global.php');
include_once(R_P.'wap/u2gb_mod.php');
require_once(R_P.'require/postfunc.php');
require_once(R_P.'require/forum.php');

if($tid){
	if(!$subject || !$content){
		wap_msg("主题或内容为空!");
	} else{
		$username = wap_cv($username);
		$username = U2GB($username);
		$tp=$db->get_one("SELECT fid,locked,ifcheck FROM pw_threads WHERE tid='$tid'");
		$winddb   = postcheck($username,$password,$tp['fid'],'reply');
		if (!$tp['ifcheck']){
			wap_msg('该贴还没通过审核，不可回复！');
		}
		if($tp['locked']>0){
			wap_msg("该贴已被锁定，不可回复!");
		}

		$subject = wap_cv($subject);
		$content = wap_cv($content);
		$subject = U2GB($subject);
		$content = U2GB($content);
		$ipfrom  = cvipfrom($onlineip);

		$db->update("INSERT INTO pw_posts(tid,fid,ifcheck,subject,author,authorid,postdate,userip,ipfrom,content) VALUES('$tid','$tp[fid]','1','$subject','$username','$winddb[uid]','$timestamp','$onlineip','$ipfrom','$content')");

		$db->update("UPDATE pw_threads SET lastpost='$timestamp',lastposter ='$username',replies=replies+1,hits=hits+1 WHERE tid='$tid'");

		$lastpost=$subject."\t".$username."\t".$postdate."\t"."read.php?tid=$tid&page=e#a";
		$topicadd=",tpost=tpost+1,article=article+1,topic=topic+1";
		$db->update("UPDATE pw_forumdata SET lastpost='$lastpost' $topicadd WHERE fid='$tp[fid]'");
		
		$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid='$fid'");
		$creditset = get_creditset($creditset,$db_creditset);
		$addrvrc  = $creditset['rvrc']['Reply'];
		$addmoney = $creditset['money']['Reply'];
		customcredit($winduid,$creditset,'Reply');
		$db->update("UPDATE pw_memberdata SET postnum=postnum+1,todaypost=todaypost+1,lastpost='$timestamp',money=money+'$addmoney',rvrc=rvrc+'$addrvrc' WHERE uid='$winddb[uid]'");
		wap_msg("回复发表成功!","read.php?tid=$tid");
	}
} else{
	wap_msg("回复发表失败");
}
?>