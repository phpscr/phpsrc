<?php
!function_exists('readover') && exit('Forbidden');
/**
* 版块权限判断
*/
if($foruminfo['allowrp'] && !allowcheck($foruminfo['allowrp'],$groupid,$winddb['groups'],$fid,$winddb['reply']) && !$admincheck){
	Showmsg('reply_forum_right');
}
/**
* 用户组权限判断
*/
if(!$foruminfo['allowrp'] && !$admincheck && $gp_allowrp==0){
	Showmsg('reply_group_right');
}
if($article==0){
	$S_sql=',m.groupid,tm.ifsign,tm.content';
	$J_sql='LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid';
} else{
	$S_sql=$J_sql='';
}
$tpcarray = $db->get_one("SELECT t.fid,t.locked,t.ifcheck,t.author,t.authorid,t.postdate,t.ifmail,t.special ,t.subject,t.type,t.rewardinfo,t.ifshield,t.anonymous,t.ptable $S_sql FROM pw_threads t $J_sql WHERE t.tid='$tid'");
$pw_posts = GetPtable($tpcarray['ptable']);

$t_date=$tpcarray['postdate'];//主题发表时间 bbspostguide 中用到

if($tpcarray['fid']!=$fid){
	showmsg('illegal_tid');
}
$replytitle=$tpcarray['subject'];
/**
* convert()需要$tpc_author变量
*/
$tpc_author=$tpcarray['author'];

if($forumset['lock']&& !$admincheck && $timestamp - $tpcarray['postdate'] > $forumset['lock'] * 86400){
	Showmsg('forum_locked');
}
if(!$admincheck  && !$tpcarray['ifcheck']){
	Showmsg('reply_ifcheck');
}
if(!$admincheck && !$SYSTEM['replylock'] && $tpcarray['locked']>0){
	Showmsg('reply_lockatc');
}
$ifreward = $special = 0;
if($tpcarray['special']=='3' && $winduid!=$tpcarray['authorid'] && substr($tpcarray['rewardinfo'],0,1)==1){	
	list(,,,$ifval)=explode('|',$tpcarray['rewardinfo']);
	$ifreward = $ifval>0 ? 1 : 0;
	if($ifreward==1){
		$rew=$db->get_one("SELECT pid FROM $pw_posts WHERE tid='$tid' AND ifreward='1' AND authorid='$winduid' LIMIT 1");
		$rew && $ifreward=0;
	}
}/****** 悬赏 ******/

if(!$_POST['step']){
	##主题分类
	$db_forcetype = 0;
	require_once(R_P.'require/header.php');
	require_once(R_P.'require/bbscode.php');
	$hideemail="disabled";
	if($action=="quote"){
		if($article==0){
			$atcarray=$tpcarray;
		} else{
			!is_numeric($pid) && Showmsg('illegal_tid');
			$atcarray = $db->get_one("SELECT p.author,p.subject,p.ifsign,p.postdate,p.content,p.ifshield,p.anonymous,m.groupid FROM $pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid WHERE p.pid='$pid'");
		}
		if($atcarray['ifshield']){ //单帖屏蔽
			$atcarray['content'] = shield('shield_article');
		}elseif($atcarray['groupid'] == '6'){
			$atcarray['content'] = shield('ban_article');
		}
		$ifsign=$atcarray['ifsign'];
		$old_author=$atcarray['anonymous'] ? $db_anonymousname : $atcarray['author'];
		$replytitle=$atcarray['subject'];
		$wtof_oldfile=get_date($atcarray['postdate']);
		require_once GetLang('post');
		$old_content=stripslashes($atcarray['content']);
		$old_content=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is",$lang['hide_post'],$old_content);
		$old_content=preg_replace("/\[post\](.+?)\[\/post\]/is",$lang['post_post'],$old_content);
		$old_content=preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is",$lang['sell_post'],$old_content);
		$old_content=preg_replace("/\[quote\](.*)\[\/quote\]/is","",$old_content);

		$bit_content = explode("\n",$old_content);
		if(count($bit_content) > 5){
			$old_content = "$bit_content[0]\n$bit_content[1]\n$bit_content[2]\n$bit_content[3]\n$bit_content[4]\n.......";
		}
		if(strpos($old_content,$db_bbsurl)!==false){
			$old_content=str_replace('p_w_picpath',$picpath,$old_content);
			$old_content=str_replace('p_w_upload',$attachname,$old_content);
		}
		$old_content=preg_replace("/\<(.+?)\>/is","",$old_content);
		if($winddb['editor']){
			$atc_content="[quote]".$lang['info_post']."\n{$old_content}[/quote]\n";
		} else{
			$atc_content="[quote]".$lang['info_post']."\n{$old_content}[/quote]\n";
		}
	}
	$post_reply="";
	$query = $db->query("SELECT author,subject,postdate,content,anonymous FROM $pw_posts WHERE tid='$tid' AND ifcheck='1' ORDER BY postdate DESC LIMIT 0,$db_showreplynum");
	while($oldsubject=$db->fetch_array($query)){
		$oldsubject['subject']=stripslashes($oldsubject['subject']);
		$oldsubject['content']=stripslashes($oldsubject['content']);
		$tpc_author = ($oldsubject['anonymous'] && !$admincheck && $windid!=$oldsubject['author']) ? $db_anonymousname : $oldsubject['author'];
		$post_reply.="<table align=center width=70% cellspacing=1 cellpadding=2 style='TABLE-LAYOUT: fixed;WORD-WRAP: break-word'><tr><td width=100%>	$tpc_author:$oldsubject[subject]<br><br>".convert($oldsubject['content'],$db_windpost)."</td></tr></table><hr size=1 color=$tablecolor width=80%>";
	}
	/**
	* 索引设计时为了减少空间,回复的主题可能为空,所以默认为回复主题!
	*/
	$replytitle =='' ? $atc_title = 'Re:'.$tpcarray['subject'] : $atc_title = 'Re:'.$replytitle;
	$guidename = forumindex($foruminfo['fup']);
	$guidename[$fid] = array($tpcarray['subject'],"read.php?fid=$fid&tid=$tid");
	list($msg_guide,$forumlist) = headguide($guidename);

	require_once PrintEot('post');footer();
} elseif ($_POST['step']==2){

	list($atc_title,$atc_content,$ifconvert)=check_data('reply');
	!$atc_iconid && $atc_iconid="0";
	/*
	*下句主要是为了节省数据的重复,可以用智能判断
	*/
	stripslashes($atc_title)=='Re:'.$replytitle && $atc_title='';
	
	$db_tcheck && $winddb['postcheck'] == tcheck($atc_content) && Showmsg('content_same'); //内容验证

	//!$atc_usesign && $atc_usesign=0;
	if($db_replysendmail==1){
		if($tpcarray['ifmail']==1 && $windid!= $tpcarray['author']){
			$receiver = $tpcarray['author'];
			$old_title=$read['subject'];
			$detail = $db->get_one("SELECT email,receivemail FROM pw_members WHERE uid='$tpcarray[authorid]'");
			$send_address= $detail['email'];
			if($detail['receivemail']=="1"){
				require_once(R_P.'require/sendemail.php');
				if(sendemail($send_address, 'email_reply_subject','email_reply_content','email_additional')){
					$tpcarray['ifmail']=0;
				}
			}
		}
	}
	require_once(R_P.'require/postupload.php');
	if($attachs){
		$ifupload=",ifupload='$ifupload'";
	} else{
		$ifupload='';
	}
	if(($foruminfo['f_check'] == 2 || $foruminfo['f_check'] == 3) && $_G['atccheck'] && !$admincheck){
		$ifcheck = 0;
	} else {
		$ifcheck = 1;
	}
	$atc_content=trim($atc_content);
	$anonymous = ($forumset['anonymous'] && $_G['anonymous'] && $atc_anonymous) ? 1 : 0;

	if($db_plist){
		$db->update("INSERT INTO pw_pidtmp(pid) values('')");
		$pid=$db->insert_id();
	}else{
		$pid='';
	}
	$db->update("INSERT INTO $pw_posts (pid,fid,tid,aid,author,authorid,icon,postdate,subject,userip,ifsign,ipfrom,ifconvert,ifcheck,content,ifreward,anonymous) VALUES ('$pid','$fid','$tid','$attachs','".addslashes($windid)."','$winddb[uid]','$atc_iconid','$timestamp','$atc_title', '$onlineip','$atc_usesign','$ipfrom','$ifconvert','$ifcheck','$atc_content','$ifreward','$anonymous')");
	!$pid && $pid = $db->insert_id();
	if($ifcheck==1){
		$atc_author = $anonymous ? $db_anonymousname : $windid;
		$db->update("UPDATE pw_threads SET lastpost='$timestamp',lastposter ='".addslashes($atc_author)."',replies=replies+1 $ifupload ,hits=hits+1 WHERE tid='$tid'");
	}
	if($aids){
		$db->update("UPDATE pw_attachs SET tid='$tid',pid='$pid' WHERE aid IN($aids)");
	}

	bbspostguide();

	unset($j_p);
	if($ifcheck==1){
		if($foruminfo['allowhtm'] && !$foruminfo['cms']){
			include_once(R_P.'require/template.php');
		}
		lastinfo($fid,$foruminfo['allowhtm'],'reply',$foruminfo['cms'].'B');
		if($modify){
			@extract($db->get_one("SELECT COUNT(*) as article FROM $pw_posts WHERE tid='$tid'"));
			ObHeader("post.php?action=modify&fid=$fid&tid=$tid&pid=$pid&article=$article");
		} else{
			if(empty($j_p) || $foruminfo['cms']) $j_p="read.php?tid=$tid&page=e&#a";
			refreshto($j_p,'enter_thread');
		}
	} else{
		refreshto("thread.php?fid=$fid",'post_check');
	}
}
?>