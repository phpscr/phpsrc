<?php
require_once('global.php');
require_once(R_P.'require/header.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/msg.php');
require_once(R_P.'require/writelog.php');
require_once(R_P.'require/updateforum.php');
include_once(D_P.'data/bbscache/forum_cache.php');

if (!in_array($action,array('banuser','delatc','shield','remind'))){
	Showmsg('undefined_action');
}

$foruminfo = $db->get_one("SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid' AND f.type<>'category'");
!$foruminfo && Showmsg('data_error');
wind_forumcheck($foruminfo);

$secondurl="thread.php?fid=$fid";
/**
* 安全验证
*/
$groupid=='guest'&&	Showmsg('not_login');
empty($action) && Showmsg('undefined_action');

/**
* 获取创始人和版主权限
*/
if($windid==$manager){
	$admincheck=1;
} elseif(admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid)){
	$admincheck=1;
} else{
	$admincheck=0;
}
!$windid && $admincheck=0;

if(!$admincheck && $groupid!=5){
	/*
	* 获取用户组权限
	*/
	if($SYSTEM['rightwhere'] && strpos(",".$SYSTEM['rightwhere'].",",",".$fid.",")===false){
		Showmsg('rightwhere');
	}
	if($action=="banuser" && $SYSTEM['banuser']){
		$admincheck=1;
	} elseif($action=="delatc" && $SYSTEM['modother']){
		$admincheck=1;
	} elseif($SYSTEM[$action]){
		$admincheck=1;
	}
}
if(!$admincheck){
	Showmsg('mawhole_right');
}
$actiondb='';

require_once GetLang('masigle');
list($db_moneyname,,$db_rvrcname,,$db_creditname)=explode("\t",$db_credits);

if($_POST['step']=='3'){
	if($db_enterreason && !$atc_content){
		Showmsg('enterreason');
	}
	$atc_content = Char_cv($atc_content);
}

if($action=="banuser"){
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	if(is_numeric($pid)){
		$pw_posts = GetPtable('N',$tid);
		$sqltab="$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd=" AND t.pid='$pid'";
	}else{
		$sqltab="pw_threads t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd='';
	}
	$userdb = $db->get_one("SELECT t.anonymous,m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid='$tid' $sqladd");
	!$userdb && Showmsg('undefined_action');
	$username = $userdb['anonymous'] ? $db_anonymousname : $userdb['username'];
	if(!$_POST['step']){
		if($userdb['groupid']=='6'){
			Showmsg('member_havebanned');
		} elseif($userdb['groupid']!='-1'){
			Showmsg('masigle_ban_fail');
		}
		require_once PrintEot('masingle');footer();
	} else{
		if($userdb['groupid']=='-1'){
			if($limit>$SYSTEM['banmax']){
				Showmsg('masigle_ban_limit');
			}
			if(!$SYSTEM['bantype'] && $type==2){
				Showmsg('masigle_ban_right');
			}
			$db->update("UPDATE pw_members SET groupid='6' WHERE uid='$userdb[uid]'");
			$db->update("REPLACE INTO pw_banuser VALUES('$userdb[uid]','$type','$timestamp','$limit','".addslashes($windid)."','$atc_content')");
			if($ifmsg){
				if($type==1){
					$msginfo=$lang['banuser_1'];
					$msginfo=str_replace('_limit',$limit,$msginfo);
				} else{
					$msginfo=$lang['banuser_2'];
				}
				$message=array($userdb['username'],$winduid,$lang['banuser_2'],$timestamp,addslashes($msginfo)."\n".$atc_content,'',$windid);
				writenewmsg($message,1);
			}
			if($foruminfo['allowhtm']){
				if($foruminfo['cms']){
					require_once(R_P.'require/c_buildhtml.php');
					BuildTopicHtml($tid,$foruminfo);
				} else {
					require_once(R_P.'require/template.php');
				}
			}
			refreshto("profile.php?action=show&uid=$userdb[uid]",'masigle_ban_success');
		} elseif($userdb['groupid']=='6'){
			Showmsg('member_havebanned');
		} else{
			Showmsg('masigle_ban_fail');
		}
	}
} elseif($action=="shield"){
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	if(is_numeric($pid)){
		$pw_posts = GetPtable('N',$tid);
		$sqlsel="t.subject,t.content,t.postdate,t.ifshield,t.anonymous,";
		$sqltab="$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd=" AND t.pid='$pid'";
	}else{
		$sqlsel="t.subject,t.postdate,t.ifshield,t.anonymous,";
		$sqltab="pw_threads t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd='';
	}
	$readdb=$db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid='$tid' $sqladd");
	!$readdb && Showmsg('illegal_tid');

	if($groupid != 3 && $groupid != 4){				
		if($readdb['groupid'] == 3 || $readdb['groupid'] == 4){
			Showmsg('modify_admin');
		}
	}
	$readdb['subject']=substrs($readdb['subject'],35);
	!$readb['subject'] && is_numeric($pid) && $readdb['subject']=substrs($readdb['content'],35);
	if(!$step){
		$readdb['ifshield'] ? $check_N = 'checked' : $check_Y = 'checked';
		$readdb['postdate'] = get_date($readdb['postdate']);
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			}else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot('masingle');footer();
	}elseif($_POST['step']){
		if($_POST['step']==3){
			$readdb['ifshield']==1 && Showmsg('read_shield');
			$ifshield = 1;
		}else{
			$readdb['ifshield']==0 && Showmsg('read_unshield');
			$ifshield = 0;
		}
		if(is_numeric($pid)){
			$db->update("UPDATE $pw_posts SET ifshield='$ifshield' WHERE pid='$pid' AND tid='$tid'");
		}else{
			$db->update("UPDATE pw_threads SET ifshield='$ifshield' WHERE tid='$tid'");
		}
		if($ifmsg){
			$msg = array(
				$readdb['username'],
				$winduid,
				'shield_title_'.$ifshield,
				$timestamp,
				'shield_content_'.$ifshield,
				'N',
				$windid,
				'fid'		=> $fid,
				'tid'		=> $tid,
				'subject'	=> $readdb['subject'],
				'postdate'	=> get_date($readdb['postdate']),
				'forum'		=> $forum[$fid]['name'],
				'admindate'	=> get_date($timestamp),
				'reason'	=> $atc_content
			);
			writenewmsg($msg,1);
		}
		if($_POST['step']==3){
			$log = array(
				'type'      => 'shield',
				'username1' => $readdb['username'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'shield_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'forum'		=> $forum[$fid]['name'],
				'subject'	=> substrs($readdb['subject'],28),
				'reason'	=> $atc_content
			);
			writelog($log);
		}
		if($foruminfo['allowhtm'] && $page==1){
			if($foruminfo['cms']){
				require_once(R_P.'require/c_buildhtml.php');
				BuildTopicHtml($tid,$foruminfo);
			} else {
				require_once(R_P.'require/template.php');
			}
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		} else{
			refreshto("read.php?tid=$tid&page=$page",'operate_success');
		}
	}
	
}elseif($action=='remind'){
	(!$tid || !$pid) && Showmsg('masingle_data_error');
	if(is_numeric($pid)){
		$pw_posts = GetPtable('N',$tid);
		$sqlsel="t.subject,t.content,t.postdate,t.remindinfo,t.anonymous,";
		$sqltab="$pw_posts t LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd=" AND t.pid='$pid'";
	}else{
		$sqlsel="t.subject,t.postdate,t.anonymous,tm.remindinfo,";
		$sqltab="pw_threads t LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON t.authorid=m.uid";
		$sqladd='';
	}
	$readdb=$db->get_one("SELECT $sqlsel m.uid,m.username,m.groupid FROM $sqltab WHERE t.tid='$tid' $sqladd");
	!$readdb && Showmsg('illegal_tid');

	if($groupid != 3 && $groupid != 4){				
		if($readdb['groupid'] == 3 || $readdb['groupid'] == 4){
			Showmsg('modify_admin');
		}
	}
	$readdb['subject']=substrs($readdb['subject'],35);
	!$readb['subject'] && is_numeric($pid) && $readdb['subject']=substrs($readdb['content'],35);

	if(!$step){
		$readdb['remindinfo'] ? $check_N = 'checked' : $check_Y = 'checked';
		$readdb['postdate'] = get_date($readdb['postdate']);
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		list($remindinfo)=explode("\t",$readdb['remindinfo']);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			}else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot('masingle');footer();
	}elseif($_POST['step']==3){
		!$atc_content && Showmsg('remind_data_empty');
		$remindinfo = $atc_content."\t".$windid."\t".$timestamp;
		if(strlen($remindinfo)>150)Showmsg('remind_length');
		if(is_numeric($pid)){
			$db->update("UPDATE $pw_posts SET remindinfo='$remindinfo' WHERE pid='$pid' AND tid='$tid'");
		}else{
			$db->update("UPDATE pw_tmsgs SET remindinfo='$remindinfo' WHERE tid='$tid'");
		}
		$log = array(
			'type'      => 'remind',
			'username1' => $readdb['username'],
			'username2' => $windid,
			'field1'    => $fid,
			'field2'    => '',
			'field3'    => '',
			'descrip'   => 'remind_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'tid'		=> $tid,
			'forum'		=> $forum[$fid]['name'],
			'subject'	=> substrs($readdb['subject'],28),
			'reason'	=> $atc_content
		);
		writelog($log);
		refreshto("read.php?tid=$tid&page=$page",'operate_success');
	}elseif($_POST['step']==5){
		if(is_numeric($pid)){
			$db->update("UPDATE $pw_posts SET remindinfo='' WHERE pid='$pid' AND tid='$tid'");
		}else{
			$db->update("UPDATE pw_tmsgs SET remindinfo='' WHERE tid='$tid'");
		}
		refreshto("read.php?tid=$tid&page=$page",'operate_success');
	}
}elseif($action=="delatc"){
	empty($delatc) && Showmsg('mawhole_nodata');
	$tpcdb = $db->get_one("SELECT t.tid,t.fid,t.author,t.authorid,t.postdate,t.subject,t.topped,t.anonymous,t.ptable,tm.aid FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid='$tid'");
	if(!$tpcdb || $tpcdb['fid'] != $fid){
		Showmsg('undefined_action');
	}
	$pw_posts = GetPtable($tpcdb['ptable']);
	$deltpc = $pids = '';
	foreach($delatc as $k=>$v){
		if($v=='tpc'){
			$deltpc='1';
		} elseif(is_numeric($v)){
			$pids .= $pids ? ','.$v : $v;
		}
	}
	$threaddb=array();
	if($deltpc){
		if($groupid != 3 && $groupid != 4){
			$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid='$tpcdb[authorid]'");
			if($authordb['groupid'] == 3 || $authordb['groupid'] == 4){
				Showmsg('modify_admin');
			}
		}
		$tpcdb['pid'] = 'tpc';
		$tpcdb['postdate'] = get_date($tpcdb['postdate']);
		$threaddb[] = $tpcdb;
	}
	if($pids){
		$query = $db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous FROM $pw_posts WHERE tid='$tid' AND fid='$fid' AND pid IN($pids)");
		while($rt = $db->fetch_array($query)){
			if($groupid != 3 && $groupid != 4){
				$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid='$rt[authorid]'");
				if($authordb['groupid'] == 3 || $authordb['groupid'] == 4){
					Showmsg('modify_admin');
				}
			}
			if(!$rt['subject']){
				$rt['subject'] = substrs($rt['content'],35);
			}
			$rt['postdate'] = get_date($rt['postdate']);
			$threaddb[] = $rt;
		}
	}
	if(!$_POST['step']){
		$reason_sel='';
		$reason_a=explode("\n",$db_adminreason);
		foreach($reason_a as $k=>$v){
			if($v=trim($v)){
				$reason_sel .= "<option value=\"$v\">$v</option>";
			}else{
				$reason_sel .= "<option value=\"\">-------</option>";
			}
		}
		require_once PrintEot('masingle');footer();
	}else{
		include_once(D_P.'data/bbscache/creditdb.php');
		if($db_ftpweb){
			require_once(R_P.'require/ftp.php');
		}
		$creditset = get_creditset($foruminfo['creditset'],$db_creditset);
		foreach($threaddb as $key=>$val){
			if($val['pid'] == 'tpc'){
				if($val['aid']){
					$attachs= unserialize(stripslashes($val['aid']));
					foreach($attachs as $key=>$value){
						if($ftp && !file_exists($attachdir."/".$value['attachurl'])){
							$ftp->delete($value['attachurl']);
						}else{
							P_unlink("$attachdir/$value[attachurl]");
						}
						$db->update("DELETE FROM pw_attachs WHERE aid='$key'");
					}
				}
				$db->update("DELETE FROM pw_tmsgs WHERE tid='$tid'");
				$db->update("DELETE FROM pw_threads WHERE tid='$tid'");

				dtchange($val['authorid'],-$creditset['rvrc']['Delete'],-1,-$creditset['money']['Delete']); 
				customcredit($val['authorid'],$creditset,'Delete');
				$msg_delrvrc  = floor($creditset['rvrc']['Delete']/10);
				$msg_delmoney = $creditset['money']['Delete'];

				if($ifmsg){
					$msg = array(
						$val['author'],
						$winduid,
						'deltpc_title',
						$timestamp,
						'deltpc_content',
						'N',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $tid,
						'subject'	=> $val['subject'],
						'postdate'	=> $val['postdate'],
						'forum'		=> $forum[$fid]['name'],
						'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
					writenewmsg($msg,1);
				}
				$log = array(
					'type'      => 'delete',
					'username1' => $val['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'deltpc_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $tid,
					'forum'		=> $forum[$fid]['name'],
					'subject'	=> substrs($val['subject'],28),
					'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
					'reason'	=> $atc_content
				);
				writelog($log);
			}else{
				if($val['aid']){
					$attachs= unserialize(stripslashes($val['aid']));
					foreach($attachs as $key=>$value){
						if($ftp && !file_exists($attachdir."/".$value['attachurl'])){
							$ftp->delete($value['attachurl']);
						}else{
							P_unlink("$attachdir/$value[attachurl]");
						}
						$db->update("DELETE FROM pw_attachs WHERE aid='$key'");
					}
				}
				$db->update("DELETE FROM $pw_posts WHERE pid='$val[pid]'");

				dtchange($val['authorid'],-$creditset['rvrc']['Deleterp'],-1,-$creditset['money']['Deleterp']);
				customcredit($val['authorid'],$creditset,'Deleterp');

				$msg_delrprvrc = floor($creditset['rvrc']['Deleterp']/10);
				$msg_delmoney  = $creditset['money']['Deleterp'];
				
				if($ifmsg){
					$msg = array(
						$val['author'],
						$winduid,
						'delrp_title',
						$timestamp,
						'delrp_content',
						'N',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $tid,
						'subject'	=> $val['subject'],
						'postdate'	=> $val['postdate'],
						'forum'		=> $forum[$fid]['name'],
						'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
					writenewmsg($msg,1);
				}
				$log = array(
					'type'      => 'delete',
					'username1' => $val['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'delrp_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $tid,
					'forum'		=> $forum[$fid]['name'],
					'subject'	=> $val['subject'] ? substrs($val['subject'],28) : substrs($val['content'],28),
					'affect'	=> "{$db_rvrcname}：-{$msg_delrprvrc}，{$db_moneyname}：-{$msg_delmoney}",
					'reason'	=> $atc_content
				);
				writelog($log);
			}
		}
		P_unlink(R_P."$htmdir/$fid/".date('ym',$tpcdb['postdate'])."/$tid.html");

		$rt = $db->get_one("SELECT count(*) AS replies FROM $pw_posts WHERE tid='$tid' AND ifcheck='1'");
		$replies = $rt['replies'];
		$rt = $db->get_one("SELECT tid,author,anonymous FROM pw_threads WHERE tid='$tid'");
		if($rt){
			if(!$replies){
				$rt['anonymous'] && $rt['author'] = $db_anonymousname;
				$db->update("UPDATE pw_threads SET replies='0',lastpost=postdate,lastposter='$rt[author]' WHERE tid='$tid'");
			}else{
				$pt=$db->get_one("SELECT postdate,author,anonymous FROM $pw_posts WHERE tid='$tid' ORDER BY postdate DESC LIMIT 1");
				Add_S($pt);
				$pt['anonymous'] && $pt['author'] = $db_anonymousname;
				$db->update("UPDATE pw_threads SET replies='$replies',lastpost='$pt[postdate]',lastposter='$pt[author]' WHERE tid='$tid'");
			}
		}elseif($replies){
			$rt=$db->get_one("SELECT * FROM $pw_posts WHERE tid='$tid' ORDER BY postdate LIMIT 1");
			Add_S($rt);
			@extract($rt);

			$db->update("DELETE FROM $pw_posts WHERE pid='$pid'");
			if($replies > 1){
				$lt=$db->get_one("SELECT postdate,author,anonymous FROM $pw_posts WHERE tid='$tid' ORDER BY postdate DESC LIMIT 1");
				$lastpost	= $lt['postdate'];
				$anonymous  = $lt['anonymous'];
				$lastposter = $anonymous ? $db_anonymousname : $lt['author'];
			}else{
				$lastpost	= $postdate;
				$lastposter = $anonymous ? $db_anonymousname : $author;
			}
			!$subject && $subject=addslashes($tpcdb['subject']);
			$replies --;
			$db->update("REPLACE INTO pw_threads (tid,fid,icon,author,authorid,subject,ifcheck,type,postdate,lastpost,lastposter,hits,replies,topped,digest,ifupload,anonymous) VALUES ('$tid','$fid','0','$author','$authorid','$subject','$ifcheck','0','$postdate','$lastpost','$lastposter','0','$replies','0','0','0','$anonymous')");
			$db->update("REPLACE INTO pw_tmsgs(tid,aid,userip,ifsign,buy,ipfrom,ifconvert,content) VALUES('$tid','$aid','$userip','$ifsign', '', '$ipfrom', '$ifconvert','$content')");
		}
		updateforum($fid);
		if($tpcdb['topped'] && $deltpc){
			updatetop();
		}
		if($ftp){
			$ftp->close();unset($ftp);
		}
		if($foruminfo['allowhtm']){
			if($foruminfo['cms']){
				require_once(R_P.'require/c_buildhtml.php');
				BuildTopicHtml($tid,$foruminfo);
			} else {
				require_once(R_P.'require/template.php');
			}
			refreshto("thread.php?fid=$fid",'enter_thread');
		} else{
			refreshto("thread.php?fid=$fid",'enter_thread');
		}
	}
}
?>