<?php
require_once('global.php');
require_once(R_P.'require/postfunc.php');
require_once(R_P.'require/forum.php');

!$windid && wap_msg('not_login');
list($db_openpost,$db_poststart,$db_postend)=explode("\t",$db_openpost);
if($db_openpost==1 && $db_poststart<$db_postend && ($t['hours']<$db_poststart || $t['hours']>$db_postend)){
	wap_msg("post_openpost");
}
$groupid=='6' && wap_msg('post_ban');
$groupid=='7' && wap_msg("post_check");
if($db_postallowtime && $timestamp-$winddb['regdate']<$db_postallowtime*3600){
	wap_msg('post_newrg_limit');
}
if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']){
	wap_msg('post_gp_limit');
}
if ($gp_postpertime && $timestamp-$winddb['lastpost']<=$gp_postpertime){
	wap_msg('post_limit');
}

!$action && $action='new';
if($action=='new'){
	if(!$_POST['subject'] || !$_POST['content']){
		$fids=array();
		$query=$db->query("SELECT fid FROM pw_forums WHERE password='' AND allowvisit='' AND f_type!='hidden'");
		while($rt=$db->fetch_array($query)){
			$fids[]=$rt['fid'];
		}
		$cates='';
		foreach($forum as $key => $value){
			if(in_array($key,$fids) && $value['type']!='category' && !$value['cms']){
				$add=$value['type']=='forum' ? "&gt;" : ($forum[$value['fup']]['type']=='forum' ? "&gt;&gt;" : "&gt;&gt;&gt;");
				$cates.="<option value=\"$key\">$add$value[name]</option>\n";
			}
		}
		$refer="post.php?action=new&amp;tmp=$timestamp";
		wap_header('post',$db_bbsname);
		require_once PrintEot('wap_post');
		wap_footer();
	}else{
		if(!is_numeric($fid)){
			wap_msg("post_nofid!");
		}
		postcheck($fid,'new');
		$subject = wap_cv($subject);
		$content = wap_cv($content);

		$ipfrom  = cvipfrom($onlineip);
		$db->update("INSERT INTO pw_threads(fid,ifcheck,subject,author,authorid,postdate,lastpost,lastposter) VALUES('$fid','1','$subject','$windid','$winduid','$timestamp','$timestamp','$windid')");
		$tid=$db->insert_id();
		$db->update("INSERT INTO pw_tmsgs(tid,content,userip,ipfrom) VALUES('$tid','$content','$onlineip','$ipfrom')");

		$lastpost=$subject."\t".addslashes($windid)."\t".$timestamp."\t"."read.php?tid=$tid&page=e#a";
		$topicadd=",tpost=tpost+1,article=article+1,topic=topic+1";
		$db->update("UPDATE pw_forumdata SET lastpost='$lastpost' $topicadd WHERE fid='$fid'");

		$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid='$fid'");
		$creditset = get_creditset($fm['creditset'],$db_creditset);
		$addrvrc  = $creditset['rvrc']['Post'];
		$addmoney = $creditset['money']['Post'];
		customcredit($winduid,$creditset,'Post');

		$db->update("UPDATE pw_memberdata SET postnum=postnum+1,todaypost=todaypost+1,lastpost='$timestamp',money=money+'$addmoney',rvrc=rvrc+'$addrvrc' WHERE uid='$winduid'");
		wap_msg('post_success',"read.php?tid=$tid");
	}
}elseif($action=='reply'){
	if(!$tid){
		wap_msg('undefined_action');
	}
	$tp=$db->get_one("SELECT fid,locked,ifcheck,ptable FROM pw_threads WHERE tid='$tid'");
	!$tp && wap_msg('illegal_tid');
	if(!$_POST['content']){
		$refer="post.php?action=reply&amp;tid=$tid&amp;tmp=$timestamp";
		wap_header('post',$db_bbsname);
		require_once PrintEot('wap_post');
		wap_footer();
	}else{
		if (!$tp['ifcheck']){
			wap_msg('reply_ifcheck');
		}
		if($tp['locked']>0){
			wap_msg("reply_lockatc");
		}
		postcheck($tp['fid'],'reply');

		$subject = wap_cv($subject);
		$content = wap_cv($content);
		$ipfrom  = cvipfrom($onlineip);

		$pw_posts = GetPtable($tp['ptable']);
		if($db_plist){
			$db->update("INSERT INTO pw_pidtmp(pid) values('')");
			$pid=$db->insert_id();
		}else{
			$pid='';
		}
		$db->update("INSERT INTO $pw_posts (pid,tid,fid,ifcheck,subject,author,authorid,postdate,userip,ipfrom,content) VALUES ('$pid','$tid','$tp[fid]','1','$subject','$windid','$winduid','$timestamp','$onlineip','$ipfrom','$content')");

		$db->update("UPDATE pw_threads SET lastpost='$timestamp',lastposter ='".addslashes($windid)."',replies=replies+1,hits=hits+1 WHERE tid='$tid'");

		$lastpost=$subject."\t".addslashes($windid)."\t".$timestamp."\t"."read.php?tid=$tid&page=e#a";
		$topicadd=",tpost=tpost+1,article=article+1,topic=topic+1";
		$db->update("UPDATE pw_forumdata SET lastpost='$lastpost' $topicadd WHERE fid='$tp[fid]'");
		
		$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid='$fid'");
		$creditset = get_creditset($creditset,$db_creditset);
		$addrvrc  = $creditset['rvrc']['Reply'];
		$addmoney = $creditset['money']['Reply'];
		customcredit($winduid,$creditset,'Reply');
		$db->update("UPDATE pw_memberdata SET postnum=postnum+1,todaypost=todaypost+1,lastpost='$timestamp',money=money+'$addmoney',rvrc=rvrc+'$addrvrc' WHERE uid='$winduid'");
		wap_msg('post_success',"read.php?tid=$tid");
	}
}
?>