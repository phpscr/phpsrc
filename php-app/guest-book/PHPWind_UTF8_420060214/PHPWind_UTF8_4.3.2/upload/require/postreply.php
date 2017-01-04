<?php
!function_exists('readover') && exit('Forbidden');
/**
* 版块权限判断
*/
if($foruminfo['allowrp'] && strpos($foruminfo['allowrp'],','.$groupid.',')===false && !$admincheck){
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
$tpcarray = $db->get_one("SELECT t.fid,t.locked,t.ifcheck,t.author,t.authorid,t.postdate,t.ifmail,t.pollid ,t.subject,t.type $S_sql FROM pw_threads t $J_sql WHERE t.tid='$tid'");
$t_date=$tpcarray['postdate'];//主题发表时间 bbspostguide 中用到
//$tpcarray['fid']=abs($tpcarray['fid']);
if($tpcarray['fid']!=$fid){
	showmsg('illegal_tid');
}
$replytitle=$tpcarray['subject'];
/**
* convert()需要$tpc_author变量
*/
$tpc_author=$tpcarray['author'];

if ($forumset['lock']&& !$admincheck && $timestamp - $tpcarray['postdate'] > $forumset['lock'] * 86400){
	Showmsg('forum_locked');
}
if (!$admincheck  && !$tpcarray['ifcheck']){
	Showmsg('reply_ifcheck');
}
if (!$admincheck && !$SYSTEM['replylock'] && $tpcarray['locked']>0){
	Showmsg('reply_lockatc');
}
if(!$_POST['step']){
	##主题分类
	require_once(R_P.'require/header.php');
	require_once R_P.'require/bbscode.php';
	$hideemail="disabled";
	if ($action=="quote"){
		if($article==0){
			$atcarray=$tpcarray;
		} else{
			!is_numeric($pid) && Showmsg('illegal_tid');
			$atcarray = $db->get_one("SELECT p.author,p.subject,p.ifsign,p.postdate,p.content,m.groupid FROM pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid WHERE p.pid='$pid'");
		}
		if($atcarray['groupid'] == '6'){
			$atcarray['content']='No permission to view this article';
		}
		$ifsign=$atcarray['ifsign'];
		$old_author=$atcarray['author'];
		$replytitle=$atcarray['subject'];
		$wtof_oldfile=get_date($atcarray['postdate']);
		require_once GetLang('post');
		$old_content=stripslashes($atcarray['content']);
		$old_content=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is",$lang['hide_post'],$old_content);
		$old_content=preg_replace("/\[post\](.+?)\[\/post\]/is",$lang['post_post'],$old_content);
		$old_content=preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is",$lang['sell_post'],$old_content);
		$old_content=preg_replace("/\[quote\](.*)\[\/quote\]/is","",$old_content);

		//
		$bit_content = explode("\n",$old_content);
		if (count($bit_content) > 5){
			$old_content = "$bit_content[0]\n$bit_content[1]\n$bit_content[2]\n$bit_content[3]\n$bit_content[4]\n.......";
		}
		//
		if(strpos($old_content,$db_bbsurl)!==false){
			$old_content=str_replace('p_w_picpath',$picpath,$old_content);
			$old_content=str_replace('p_w_upload',$attachname,$old_content);
		}
		//
		$old_content=preg_replace("/\<(.+?)\>/is","",$old_content);
		if($winddb['editor']){
			$atc_content="[quote]".$lang['info_post']."\n{$old_content}[/quote]\n";
		} else{
			$atc_content="[quote]".$lang['info_post']."\n{$old_content}[/quote]\n";
		}
	}
	$post_reply="";
	$query = $db->query("SELECT author,subject,postdate,content FROM pw_posts WHERE tid='$tid' AND ifcheck='1' ORDER BY postdate DESC LIMIT 0 ,$db_showreplynum");
	while($oldsubject=$db->fetch_array($query)){
		$oldsubject['subject']=stripslashes($oldsubject['subject']);
		$oldsubject['content']=stripslashes($oldsubject['content']);
		$tpc_author=$oldsubject['author'];
		$post_reply.="<table align=center width=70% cellspacing=1 cellpadding=2 style='TABLE-LAYOUT: fixed;WORD-WRAP: break-word'><tr><td width=100%>	$oldsubject[author]:$oldsubject[subject]<br><br>".convert($oldsubject['content'],$db_windpost)."</td></tr></table><hr size=1 color=$tablecolor width=80%>";
	}
	/**
	* 索引设计时为了减少空间,回复的主题可能为空,所以默认为回复主题!
	*/

	$replytitle==''?$atc_title='Re:'.$tpcarray['subject']:$atc_title='Re:'.$replytitle;
	$guidename=forumindex($foruminfo['fup']);
	$guidename[$tpcarray['subject']]="read.php?fid=$fid&tid=$tid";

	$msg_guide=headguide($guidename);
	require_once PrintEot('post');footer();
} elseif ($_POST['step']==2){

	list($atc_title,$atc_content)=check_data();
	!$atc_iconid && $atc_iconid="0";
	/*
	*下句主要是为了节省数据的重复,可以用智能判断
	*/
	stripslashes($atc_title)=='Re:'.$replytitle && $atc_title='';

	if ($_POST['atc_convert']=="1"){
		$_POST['atc_autourl'] && $atc_content=autourl($atc_content);
		$atc_content = html_check($atc_content);

		/*
		* [post]、[hide、[sell=位置不能换
		*/
		if(!$foruminfo['allowhide'] || !$gp_allowhidden){
			$atc_content=str_replace("[post]","[\tpost]",$atc_content);
		} elseif($atc_hide=='1'){
			$atc_content="[post]".$atc_content."[/post]";
		}

		if(!$foruminfo['allowencode'] || !$gp_allowencode){
			$atc_content=str_replace("[hide=","[\thide=",$atc_content);
		} elseif($atc_requirervrc=='1' ){
			$atc_content="[hide=".$atc_rvrc."]".$atc_content."[/hide]";
		}
		//回复帖不能出售
		$atc_content=str_replace("[sell=","[\tsell=",$atc_content);

		//去掉注释就可以在回复里加出售贴了

		$lxcontent=convert($atc_content,$db_windpost);
		$ifconvert=$lxcontent==$atc_content ? 1:2;
	}else{
		$ifconvert=1;
	}
	!$atc_usesign && $atc_usesign=0;
	if ($db_replysendmail==1){
		if ($tpcarray['ifmail']==1 && $windid!= $tpcarray['author']){
			$receiver = $tpcarray['author'];
			$old_title=$read['subject'];
			$detail = $db->get_one("SELECT email,receivemail FROM pw_members WHERE uid='$tpcarray[authorid]'");
			$send_address= $detail['email'];
			if ($detail['receivemail']=="1"){
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
	if (($foruminfo['f_check'] == 2 || $foruminfo['f_check'] == 3) && $_G['atccheck'] && !$admincheck){
		$ifcheck = 0;
	} else {
		$ifcheck = 1;
	}
	$atc_content=trim($atc_content);
	$db->update("INSERT INTO pw_posts (fid, tid, aid, author,authorid, icon, postdate,subject,userip,ifsign,ipfrom,ifconvert,ifcheck,content) VALUES ('$fid', '$tid','$attachs','".addslashes($windid)."', '$winddb[uid]', '$atc_iconid', '$timestamp','$atc_title', '$onlineip', '$atc_usesign', '$ipfrom', '$ifconvert','$ifcheck','$atc_content')");
	$pid = $db->insert_id();
	if($ifcheck==1){
		$db->update("UPDATE pw_threads SET lastpost='$timestamp',lastposter ='".addslashes($windid)."',replies=replies+1 $ifupload ,hits=hits+1 WHERE tid='$tid'");
	}
	if($aids){
		$db->update("UPDATE pw_attachs SET tid='$tid',pid='$pid' WHERE aid IN($aids)");
	}

	bbspostguide();

	unset($j_p);
	if($ifcheck==1){
		if($foruminfo['allowhtm']){
			if($foruminfo['cms']){
				require_once R_P.'require/c_buildhtml.php';
				BuildTopicHtml($tid,$foruminfo);
			} else {
				include_once R_P.'require/template.php';
			}
		}
		lastinfo($fid,$foruminfo['allowhtm'],'reply',$foruminfo['cms'].'B');
		if ($modify){
			@extract($db->get_one("SELECT COUNT(*) as article FROM pw_posts WHERE tid='$tid'"));
			ObHeader("post.php?action=modify&fid=$fid&tid=$tid&pid=$pid&article=$article");
		} else {
			if(empty($j_p) || $foruminfo['cms']) $j_p="read.php?tid=$tid&page=e&#a";
			refreshto($j_p,'enter_thread');
		}
	} else{
		refreshto("thread.php?fid=$fid",'post_check');
	}
}
?>