<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P.'require/updateforum.php');

$t_typedb = array();
$t_per    = $t_exits = 0;
$t_db     = $foruminfo['t_type'];

if($t_db){
	$t_typedb = explode("\t",$t_db);
	$t_typedb = array_unique ($t_typedb);
	$t_per	  = $t_typedb[0];
	unset($t_typedb[0]);
	foreach ($t_typedb as $value){
		$value && $t_exits = 1;
	}
}
$db_forcetype = $t_exits && $t_per=='2' && $article==0 && !$admincheck ? 1 : 0; // 是否需要强制主题分类

if($article==0){
	$tpcdb=$db->get_one("SELECT t.tid,t.fid AS tfid,t.author,t.authorid,t.icon,t.locked,t.postdate,t.lastpost,t.subject,t.type,t.ifcheck,t.special,t.anonymous,t.ptable,tm.content,tm.aid,tm.ifsign FROM pw_threads t LEFT JOIN pw_tmsgs tm USING(tid) WHERE t.tid='$tid'");
	!$tpcdb['tid'] && Showmsg('illegal_tid');
	@extract($tpcdb);
	$pw_posts = GetPtable($ptable);
} else{
	!is_numeric($pid) && Showmsg('illegal_tid');
	$pw_posts = GetPtable('N',$tid);
	$atcdb=$db->get_one("SELECT aid,ifsign,tid,fid AS tfid,author,authorid,icon,postdate,subject,content,anonymous FROM $pw_posts WHERE pid='$pid'");
	!$atcdb['tid'] && Showmsg('illegal_tid');
	@extract($atcdb);
	$ifcheck=1;
}
$tfid != $fid && Showmsg('illegal_tid');
$page = floor($article/$db_readperpage)+1;

if(!$admincheck && (!$SYSTEM['deltpcs'] || $groupid == 5)){
	if($groupid == 'guest' || $authorid != $winduid){
		Showmsg('modify_noper');
	} elseif ($locked > 0){
		Showmsg('modify_locked');
	}
}
if($winduid != $authorid && $groupid != 3 && $groupid != 4){
	$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid='$authorid'");
	if(($authordb['groupid'] == 3 || $authordb['groupid'] == 4)){
		Showmsg('modify_admin');
	}
}
if($gp_edittime && ($timestamp - $postdate) > $gp_edittime * 60){
	Showmsg('modify_timelimit');
}
if($article==0 && $special==1){
	$sqladd = $_POST['step'] ? "" : ",state,modifiable,previewable,timelimit";
	@extract($db->get_one("SELECT voteopts $sqladd FROM pw_polls WHERE tid='$tid'"));
	$votearray = unserialize($voteopts);
} elseif(!($article==0 && $special==2 && $forumset['allowactive'])){
	$special = 0;
}
$hideemail = "disabled";

if(!$_POST['step']){
	$attach = array();
	if($aid){
		$attachs = unserialize(stripslashes($aid));
		if (is_array($attachs)){
			$attach = $attachs;
		}
	}
	if($article == 0){
		if($foruminfo['cms']){
			include_once(R_P.'require/c_search.php');
			list($tids,$kname) = search_tid($tid);
		}
		$t_checked[$type] = 'selected';
		if($special==1){
			$votearray['multiple'][0] && $multi = 'checked';
			$modifiable && $ifmodify = 'checked';
			$previewable && $ifpreview = 'checked' ;
			$vote_close = ($state || ($timelimit && $timestamp-$postdate>$timelimit*86400)) ? 1 : 0;
			$voteable = ($_G['modifyvote'] && $vote_close==0) ? "" : "disabled";
			$mostnum = $votearray['multiple'][1];
		}elseif($special==2){
			$act = $db->get_one("SELECT * FROM pw_activity WHERE tid='$tid'");	
			$act['starttime'] = get_date($act['starttime']);
			$act['endtime']   = get_date($act['endtime']);
			$act['deadline']  = get_date($act['deadline']);
			${'sel_'.$act['sexneed']} = 'checked';
		}
	}
	empty($subject) && $subject=' ';

	$htmcheck    = $ifsign < 2 ? '' : 'checked';
	!$ifanonymous && $anonymous && $ifanonymous='checked';
	$atc_title   = $subject;
	$atc_content = $content;

	if (strpos($atc_content,$db_bbsurl) !== false){
		$atc_content = str_replace('p_w_picpath',$picpath,$atc_content);
		$atc_content = str_replace('p_w_upload',$attachname,$atc_content);
	}
	$atc_content = str_replace("<","&lt;",$atc_content);
	$atc_content = str_replace(">","&gt;",$atc_content);

	require_once(R_P.'require/header.php');
	$guidename = forumindex($foruminfo['fup']);
	if(trim($subject)){
		$guidename[$fid]=array($subject,"read.php?tid=$tid");
	}
	list($msg_guide,$forumlist) = headguide($guidename);
	require_once PrintEot('post');footer();
} elseif($_POST['step'] == 1){
	if(!$admincheck && $gp_allowdelatc == 0){
		Showmsg('modify_group_right');
	}
	$rt = $db->get_one("SELECT COUNT(*) AS count FROM $pw_posts WHERE tid='$tid' AND ifcheck='1'");
	$count = $rt['count'] + 1;
	if($article == 0 && !$admincheck && $count > 1){
		Showmsg('modify_replied');
	}
	$rs = $db->get_one("SELECT replies,topped FROM pw_threads WHERE tid='$tid'");
	if($rs['replies'] != $rt['count']){
		$db->update("UPDATE pw_threads SET replies='$rt[count]' WHERE tid='$tid'");
	}
	$creditset = get_creditset($creditset,$db_creditset);
	if ($aid){
		$attachs = unserialize(stripslashes($aid));
		foreach ($attachs as $key => $value){
			$a_url=geturl($value['attachurl']);
			if($a_url[1]=='Local'){
				P_unlink("$attachdir/$value[attachurl]");
			}elseif($a_url[1]=='Ftp'){
				require_once(R_P.'require/ftp.php');
				$ftp->delete($value['attachurl']);
			}			
			$db->update("DELETE FROM pw_attachs WHERE aid='$key'");
		}
		if($ftp){
			$ftp->close();unset($ftp);
		}
	}
	if ($article == 0){
		$deltype = 'deltpc';
		$deltitle= substrs($subject,28);
		if ($count == 1){
			$db->update("DELETE FROM pw_tmsgs WHERE tid='$tid'");
			$db->update("DELETE FROM pw_threads WHERE tid='$tid'");
			P_unlink(R_P."$htmdir/$fid/".date('ym',$postdate)."/$tid.html");
		} else {
			$rt=$db->get_one("SELECT * FROM $pw_posts WHERE tid='$tid' ORDER BY postdate LIMIT 1");
			Add_S($rt);
			@extract($rt);
			if($count == 2){
				$lastpost	= $postdate;
				$lastposter	= $author;
			}else{
				$lt=$db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid='$tid' ORDER BY postdate DESC LIMIT 1");
				$lastpost	= $lt['postdate'];
				$lastposter	= $lt['author'];
			}
			$count -= 2;
			$db->update("DELETE FROM $pw_posts WHERE pid='$pid'");
			$subject && $subject="subject='$subject',";
			$db->update("UPDATE pw_threads SET icon='$icon',$subject author='$author',authorid='$authorid',postdate='$postdate',lastpost='$lastpost',lastposter='$lastposter',replies='$count' WHERE tid='$tid'");
			$db->update("UPDATE pw_tmsgs SET aid='$aid',userip='$userip',ifsign='$ifsign',ipfrom='$ipfrom' ,alterinfo='$alterinfo',ifconvert='$ifconvert',content='$content' WHERE tid='$tid'");
		}
		$msg_delrvrc  = $creditset['rvrc']['Delete'];
		$msg_delmoney = $creditset['money']['Delete'];
		dtchange($authorid,-$creditset['rvrc']['Delete'],-1,-$creditset['money']['Delete']);
		customcredit($authorid,$creditset,'Delete');
	} else {
		$deltype = 'delrp';
		$deltitle= $subject ? substrs($subject,28) : substrs($content,28);
		$db->update("DELETE FROM $pw_posts WHERE pid='$pid'");
		$db->update("UPDATE pw_threads SET replies=replies-1 WHERE tid='$tid'");
		$msg_delrvrc  = $creditset['rvrc']['Deleterp'];
		$msg_delmoney = $creditset['money']['Deleterp'];
		dtchange($authorid,-$creditset['rvrc']['Deleterp'],-1,-$creditset['money']['Deleterp']);
		customcredit($authorid,$creditset,'Deleterp');
	}
	P_unlink(D_P.'data/bbscache/c_cache.php');
	updateforum($fid);
	if ($rs['topped']){
		updatetop();
	}
	$msg_delrvrc=floor($msg_delrvrc/10);
	require_once(R_P.'require/writelog.php');
	$log = array(
		'type'      => 'delete',
		'username1' => $author,
		'username2' => $windid,
		'field1'    => $fid,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => $deltype.'_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
		'tid'		=> $tid,
		'forum'		=> $foruminfo['name'],
		'subject'	=> $deltitle,
		'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
		'reason'	=> 'edit delete article!'
	);
	writelog($log);
	if($foruminfo['allowhtm'] && $article<=$db_readperpage ){
		if ($foruminfo['cms']){
			require_once(R_P.'require/c_buildhtml.php');
			BuildTopicHtml($tid,$foruminfo);
		} else {
			include_once(R_P.'require/template.php');
		}
	}
	if($deltype == 'delrp'){
		refreshto("read.php?tid=$tid",'enter_thread');
	}else{
		refreshto("thread.php?fid=$fid",'enter_thread');
	}
} elseif($_POST['step'] == 2){
	list($atc_title,$atc_content,$ifconvert)=check_data($action);
	if($special==1){
		if($_G['modifyvote'] && is_array($votearray) && is_array($vt_selarray)){
			foreach ($vt_selarray as $key => $value){
				$value = trim(Char_cv($value));
				if ($value){
					$newvotearray['options'][$key] = array($value,$votearray['options'][$key][1],$votearray['options'][$key][2]);
				}
			}
			if ($mostvotes && is_numeric($mostvotes)){
				$mostvotes > count($vt_selarray) ? $mostvotes = count($vt_selarray) : '';
			} else {
				$mostvotes = count($vt_selarray);
			}
			$newvotearray['multiple'] = array($multiplevote,$mostvotes);
			$voteopts = addslashes(serialize($newvotearray));
			!$modifiable && $modifiable = 0;
			!$previewable && $previewable = 0;
			$timelimit < 0 && $timelimit = 0;
			$db->update("UPDATE pw_polls SET voteopts='$voteopts',state='$vote_close',modifiable='$modifiable',previewable='$previewable',timelimit='$timelimit' WHERE tid='$tid'");
		}elseif($vote_close){
			$db->update("UPDATE pw_polls SET state='1' WHERE tid='$tid'");
		}
	} elseif($special==2){
		!($act_subject && $act_starttime && $act_deadline) && Showmsg('active_data_empty');
		$act_starttime= PwStrtoTime($act_starttime);
		$act_endtime  = PwStrtoTime($act_endtime);
		$act_deadline = PwStrtoTime($act_deadline);
		$act_deadline < $timestamp && Showmsg('deadline_limit');
		$act_deadline > $act_starttime && Showmsg('starttime_limit');
		$act_endtime && $act_starttime>$act_endtime && Showmsg('endtime_limit');
		$act_subject  = Char_cv($act_subject);
		$act_location = Char_cv($act_location);
		(!is_numeric($act_num) || $act_num<0) && $act_num=0;
		(!is_numeric($act_costs) || $act_costs<0) && $act_costs=0;
		$db->update("UPDATE pw_activity SET subject='$act_subject',starttime='$act_starttime',endtime='$act_endtime',location='$act_location',num='$act_num',sexneed='$act_sex',costs='$act_costs',deadline='$act_deadline' WHERE tid='$tid'");
	}
	/**
	* 附件修改
	*/
	$oldattach=$replacedb=array();
	if ($aid){
		$oldattach=unserialize(stripslashes($aid));
		foreach ($oldattach as $key=>$value){
			if (!@in_array($key,$keep)){
				$a_url=geturl($value['attachurl']);
				if($a_url[1]=='Local'){
					P_unlink("$attachdir/$value[attachurl]");
				}elseif($a_url[1]=='Ftp'){
					require_once(R_P.'require/ftp.php');
					$ftp->delete($value['attachurl']);
				}			
				$db->update("DELETE FROM pw_attachs WHERE aid='$key'");
				unset($oldattach[$key]);
			} else {
				if(!is_array($downrvrc) || !is_array($attdesc)){
					Showmsg('undefined_action');
				}
				$attdesc[$key]				 = Char_cv($attdesc[$key]);
				$oldattach[$key]['desc']     = str_replace('\\','',$attdesc[$key]);
				$oldattach[$key]['needrvrc'] = (int)$downrvrc[$key];
				if(array_key_exists('replace'.$key,$_FILES)){
					$db_attachnum++;
					$replacedb[$db_attachnum]=$oldattach[$key];
					$_FILES['atc_attachment'.$db_attachnum]=$_FILES['replace'.$key];
					unset($_FILES['replace'.$key]);
				}else{
					$db->update("UPDATE pw_attachs SET needrvrc='{$downrvrc[$key]}',descrip='{$attdesc[$key]}' WHERE aid='$key'");
				}
			}
		}
	}
	require_once(R_P.'require/postupload.php');
	if ($attachs){
		$attachs=unserialize(stripslashes($attachs));
		foreach ($attachs as $key=>$value){
			$oldattach[$key]=$value;
		}
		$winddb['money'] -= $gp_uploadmoney;
		$db->update("UPDATE pw_memberdata SET money='$winddb[money]',uploadtime='$winddb[uploadtime]',uploadnum='$winddb[uploadnum]' WHERE uid='$winduid'");
	}
	if ($oldattach){
		$oldattach = addslashes(serialize($oldattach));
	} else {
		$oldattach = '';
	}
	$atc_iconid = is_numeric($atc_iconid) ? $atc_iconid : $icon;
	$timeofedit = get_date($timestamp);
	$anonymous  = ($forumset['anonymous'] && $_G['anonymous'] && $atc_anonymous) ? 1 : 0;

	if ($groupid != 3 && $postdate + 300 < $timestamp){
		$altername = $anonymous && $windid==$author ? $db_anonymousname : $windid;
		require_once GetLang('post');
		$alterinfo = $lang['edit_post'];
	} else{
		$alterinfo = '';
	}
	if ($winduid != $authorid){
		/**
		* 管理员编辑帖子的安全日记
		*/
		require_once(R_P.'require/writelog.php');
		$log = array(
			'type'      => 'edit',
			'username1' => $author,
			'username2' => $windid,
			'field1'    => $fid,
			'field2'    => '',
			'field3'    => '',
			'descrip'   => 'edit_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'tid'		=> $tid,
			'forum'		=> $foruminfo['name'],
			'subject'	=> substrs($subject,28),
			'reason'	=> 'edit article'
		);
		writelog($log);
	}

	$atc_content = trim($atc_content);
	if ($authorid == $winduid){
		$ipdata="userip='$onlineip',ipfrom='$ipfrom',";
	} else {
		$ipdata='';
	}
	$sqladd = '';
	if ($article == 0){
		if ($foruminfo['cms']){
			$db->update("DELETE FROM pw_ks WHERE tid='$tid'");
			include_once(R_P.'require/c_search.php');
			insert_key($tid,$keyword);
		}
		$db->update("UPDATE pw_tmsgs SET aid='$oldattach',$ipdata ifsign='$atc_usesign',alterinfo='$alterinfo',ifconvert='$ifconvert',content='$atc_content' WHERE tid='$tid'");
		if ($aids){
			$db->update("UPDATE pw_attachs SET tid='$tid' WHERE aid IN($aids)");
		}
		if($anonymous != $tpcdb['anonymous'] && $postdate==$lastpost){
			$lastposter = $anonymous ? $db_anonymousname : $author;
			$sqladd = ",lastposter='$lastposter'";
		}
	} else {
		$db->update("UPDATE $pw_posts SET aid='$oldattach',$ipdata icon='$atc_iconid',subject='$atc_title',ifsign='$atc_usesign',alterinfo='$alterinfo',ifconvert='$ifconvert',content='$atc_content',anonymous='$anonymous' WHERE pid='$pid'");
		if ($aids){
			$db->update("UPDATE pw_attachs SET tid='$tid',pid='$pid' WHERE aid IN($aids)");
		}
		if($anonymous != $atcdb['anonymous']){
			$lt=$db->get_one("SELECT pid FROM $pw_posts WHERE tid='$tid' ORDER BY postdate DESC LIMIT 1");
			if($pid==$lt['pid']){
				$lastposter = $anonymous ? $db_anonymousname : $author;
				$sqladd = ",lastposter='$lastposter'";
			}
		}
	}

	$ifupload=getattachtype($tid);
	if ($article == 0){
		##主题分类
		//强制分类
		if (!$p_type || empty($t_typedb[$p_type]) || ($t_per == 0 && !$admincheck)){
			$w_type=0;
		} else {
			$w_type=$p_type;
		}
		$db_forcetype && $w_type=='0' && Showmsg('force_tid_select');
		
		$db->update("UPDATE pw_threads SET icon='$atc_iconid',subject='$atc_title',type='$w_type',ifupload='$ifupload',anonymous='$anonymous' $sqladd WHERE tid='$tid'");
	} else {
		$db->update("UPDATE pw_threads SET ifupload='$ifupload' $sqladd WHERE tid='$tid'");
	}
	if ($foruminfo['allowhtm'] && !$foruminfo['cms'] && $article < $db_readperpage){
		include_once (R_P.'require/template.php');
	}
	$rt=$db->get_one("SELECT lastpost FROM pw_forumdata WHERE fid='$fid'");
	$lastpost=explode("\t",$rt['lastpost']);
	if ($lastpost[2] == $postdate){
		lastinfo($fid,$foruminfo['allowhtm'],'',$foruminfo['cms'].'B');
	}
	if ($modify){
		ObHeader("post.php?action=modify&fid=$fid&tid=$tid&pid=$pid&article=$article");
	} else {
		refreshto("read.php?tid=$tid&page=$page&toread=1",'enter_thread');
	}
}
?>