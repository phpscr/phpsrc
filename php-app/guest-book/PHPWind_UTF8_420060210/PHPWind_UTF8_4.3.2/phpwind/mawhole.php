<?php
require_once('global.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/updateforum.php');
require_once(R_P.'require/msg.php');
require_once(R_P.'require/writelog.php');
include_once(D_P.'data/bbscache/forum_cache.php');

!$windid && Showmsg('not_login');
if(!$tidarray && is_numeric($seltid)){
	$tidarray = array($seltid);
}
if (empty($fid) || empty($tidarray) || !in_array($action,array('type','check','del','move','copy','headtopic','digest','lock','pushtopic','edit'))){
	Showmsg('undefined_action');
}
$foruminfo = $db->get_one("SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid' AND f.type<>'category'");
if(!$foruminfo){
	Showmsg('data_error');
}
wind_forumcheck($foruminfo);

list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

if($windid == $manager){
	$admincheck = 1;
} elseif($foruminfo['forumadmin'] && strpos($foruminfo['forumadmin'],','.$windid.',') !== false){
	$admincheck = 1;
} else{
	$admincheck = 0;
}
if(!$admincheck && $groupid != 5){
	if($action == "type" && $SYSTEM['tpctype']){
		$admincheck = 1;
	} elseif($action == "check" && $SYSTEM['check']){
		$admincheck = 1;
	} elseif($action == "del" && $SYSTEM['delatc']){
		$admincheck = 1;
	} elseif($action == "move" && $SYSTEM['moveatc']){
		$admincheck = 1;
	} elseif($action == "copy" && $SYSTEM['copyatc']){
		$admincheck = 1;
	} elseif($action == "headtopic" && $SYSTEM['topped']){
		 $admincheck=1;
	} elseif($action == "banuser" && $SYSTEM['banuser']){
		$admincheck=1;
	}	elseif(($action=="digest" || $action=="lock" || $action=="pushtopic" || $action=="edit") && $SYSTEM['typeadmin']){
		$admincheck=1;
	}
}
!$admincheck && Showmsg('mawhole_right');

require_once(R_P.'require/header.php');
$tids = '';
$threaddb = array();
if (empty($_POST['step']) && is_array($tidarray)){
	$reason_sel='';
	$reason_a=explode("\n",$db_adminreason);
	foreach($reason_a as $k=>$v){
		if($v=trim($v)){
			$reason_sel .= "<option value=\"$v\">$v</option>";
		}else{
			$reason_sel .= "<option value=\"\">-------</option>";
		}
	}
	foreach($tidarray as $k=>$v){
		if(is_numeric($v)){
			$tids .= $tids ? ','.$v :$v;
		}
	}
	if($tids){
		$query = $db->query("SELECT fid,tid,author,authorid,postdate,subject FROM pw_threads WHERE tid IN($tids)");
		while($rt = $db->fetch_array($query)){
			if($rt['fid'] != $fid && $groupid == 5){
				Showmsg('admin_forum_right');
			}
			if($groupid != 3 && $groupid != 4){
				$authordb = $db->get_one("SELECT groupid FROM pw_members WHERE uid='$rt[authorid]'");
				if($authordb['groupid'] == 3 || $authordb['groupid'] == 4){
					Showmsg('modify_admin');
				}
			}
			$rt['date'] = get_date($rt['postdate']);
			$threaddb[] = $rt;
		}
	}
}
if($_POST['step']=='2'){
	if($db_enterreason && !$atc_content){
		Showmsg('enterreason');
	}
	$atc_content = Char_cv($atc_content);
}
if ($action == "type"){
	if (empty($_POST['step'])){
		$typesel   = '';
		$t_typedb  = array();
		$t_db      = $foruminfo['t_type'];
		if(trim($t_db)){
			$t_typedb = explode("\t",$t_db);
			$t_typedb = array_unique ($t_typedb);
			unset($t_typedb[0]);
		} else{
			Showmsg('mawhole_notype');
		}
		require_once PrintEot('mawhole');
		footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids = '';
		if(is_array($tidarray)){
			foreach($tidarray as $key => $value){
				if(is_numeric($value)){
					$tids .= $tids ? ','.$value : $value;
				}
			}
		}
		!$tids && Showmsg('mawhole_nodata');
		$db->update("UPDATE pw_threads SET type='$type' WHERE tid IN($tids)");
		refreshto("thread.php?fid=$fid",'operate_success');
	}
}  elseif($_POST['action'] == "check"){
	if (empty($_POST['step'])){
		require_once PrintEot('mawhole');
		footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$tids  = '';
		$count = 0;
		if(is_array($tidarray)){
			foreach($tidarray as $key => $value){
				if(is_numeric($value)){
					$tids .= $tids ? ','.$value : $value;
					$count++;
				}
			}
		}
		!$tids && Showmsg('mawhole_nodata');
		$db->update("UPDATE pw_threads SET ifcheck='1' WHERE tid IN($tids)");
		$rt = $db->get_one("SELECT tid,author,postdate,subject,lastpost,lastposter FROM pw_threads WHERE fid='$fid' ORDER BY lastpost DESC LIMIT 0,1");
		if($rt['postdate'] == $rt['lastpost']){
			$subject = substrs($rt['subject'],21);
			$author  = $rt['author'];
		}else{
			$subject = 'Re:'.substrs($rt['subject'],21);
			$author  = $rt['lastposter'];
		}
		$new_url  = "read.php?tid=$rt[tid]&page=e#a";
		$lastpost = addslashes($subject)."\t".addslashes($author)."\t".$rt['lastpost']."\t".$new_url;
		$db->update("UPDATE pw_forumdata SET lastpost='$lastpost',tpost=tpost+'$count',article=article+'$count',topic=topic+'$count' WHERE fid='$fid'");
		P_unlink(D_P.'data/bbscache/c_cache.php');
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action == "del"){
	if (empty($_POST['step'])){
		require_once PrintEot('mawhole');
		footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$delaids = $pollids = '';
		$delids = 0;
		foreach($tidarray as $key => $value){
			is_numeric($value) && $delids .= ','.$value;
		}
		!$delids && Showmsg('mawhole_nodata');

		$creditset    = get_creditset($foruminfo['creditset'],$db_creditset);
		$msg_delrvrc  = floor($creditset['rvrc']['Delete']/10);
		$msg_delmoney = $creditset['money']['Delete'];

		$updatetop = 0;
		$query = $db->query("SELECT t.tid,t.fid as tfid,t.postdate,tm.aid,t.author,t.authorid,t.subject,t.replies,t.topped,t.ifupload,t.pollid FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid IN($delids)");
		while(@extract($db->fetch_array($query))){
			$tfid != $fid && Showmsg('admin_forum_right');
			$topped > 0 && $updatetop = 1;
			if($aid){
				$attachs = unserialize(stripslashes($aid));
				foreach($attachs as $key => $value){
					is_numeric($key) && $delaids .= $key.',';
					if(!$db_recycle || $fid == $db_recycle){
						P_unlink("$attachdir/$value[attachurl]");
					}
				}
			}
			is_numeric($pollid) && $pollids .= $pollid.',';
			if($ifupload){
				$query2 = $db->query("SELECT aid FROM pw_posts WHERE tid='$tid' AND aid!=''");
				while(@extract($db->fetch_array($query2))){
					if($aid){
						$attachs = unserialize(stripslashes($aid));
						foreach($attachs as $key => $value){
							is_numeric($key) && $delaids .= $key.',';
							if(!$db_recycle || $fid == $db_recycle){
								P_unlink("$attachdir/$value[attachurl]");
							}
						}
					}
				}
			}
			if($ifmsg){
				$msgdb[] = array(
					$author,
					$winduid,
					'del_title',
					$timestamp,
					'del_content',
					'N',
					$windid,
					'fid'		=> $fid,
					'tid'		=> $tid,
					'subject'	=> $subject,
					'postdate'	=> get_date($postdate),
					'forum'		=> $forum[$fid]['name'],
					'affect'    => "{$db_rvrcname}£º-{$msg_delrvrc}£¬{$db_moneyname}£º-{$msg_delmoney}",
					'admindate'	=> get_date($timestamp),
					'reason'	=> $atc_content
				);
			}
			$logdb[] = array(
				'type'      => 'delete',
				'username1' => $author,
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'del_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'affect'    => "{$db_rvrcname}£º-{$msg_delrvrc}£¬{$db_moneyname}£º-{$msg_delmoney}",
				'tid'		=> $tid,
				'subject'	=> substrs($subject,28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> $atc_content
			);
			if($ifdel){
				dtchange($authorid,-$creditset['rvrc']['Delete'],-1,-$creditset['money']['Delete']);
				customcredit($authorid,$creditset,'Delete');
			}
			// É¾³ý¾²Ì¬Ä£°æ
			$htmurl = R_P.$htmdir.'/'.$fid.'/'.date('ym',$postdate).'/'.$tid.'.html';
			if(file_exists($htmurl)){
				P_unlink($htmurl);
			}
			if($db_recycle && $fid != $db_recycle){
				$db->update("INSERT INTO pw_recycle VALUES('$tid','$fid','$timestamp','$windid')");
			}
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		if(!$db_recycle || $fid == $db_recycle){
			if($pollids){
				$pollids = substr($pollids,0,-1);
				$db->update("DELETE FROM pw_polls WHERE pollid IN($pollids)");
			}
			if($delaids){
				$delaids  = substr($delaids,0,-1);
				$db->update("DELETE FROM pw_attachs WHERE aid IN($delaids)");
			}
			$db->update("DELETE FROM pw_threads	WHERE tid IN($delids)");
			$db->update("DELETE FROM pw_posts	WHERE tid IN($delids)");
			$db->update("DELETE FROM pw_tmsgs	WHERE tid IN($delids)");
		} else{
			if($delaids){
				$delaids  = substr($delaids,0,-1);
				$db->update("UPDATE pw_attachs SET fid='$db_recycle' WHERE aid IN($delaids)");
			}
			$db->update("UPDATE pw_threads SET fid='$db_recycle',topped=0 WHERE tid IN($delids)");
			$db->update("UPDATE pw_posts   SET fid='$db_recycle' WHERE tid IN($delids)");
			updateforum($db_recycle);
		}
		updateforum($fid);
		if($updatetop){
			updatetop();
		}
		P_unlink(D_P.'data/bbscache/c_cache.php');
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action == "move"){
	if (empty($_POST['step'])){
		$forumadd='';
		$query=$db->query("SELECT fid,name,allowvisit FROM pw_forums WHERE f_type='hidden'");
		if($query){
			while($rt = $db->fetch_array($query)){
				if(strpos($rt['allowvisit'],','.$groupid.',') !== false){
					$forumadd.="<option value='$rt[fid]'> &nbsp;|- $rt[name]</option>";
				}
			}
		}
		@include_once(D_P.'data/bbscache/forumcache.php');
		require_once PrintEot('mawhole');
		footer();
	} else {
		if($forum[$to_id]['type'] == 'category'){
			Showmsg('mawhole_error');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$mids = 0;
		foreach($tidarray as $key => $value){
			is_numeric($value) && $mids .= ','.$value;
		}
		!$mids && Showmsg('mawhole_nodata');

		$updatetop = 0;
		$query     = $db->query("SELECT tid,fid as tfid,author,postdate,subject,replies,topped,pollid FROM pw_threads WHERE tid IN($mids)");
		while($rt=$db->fetch_array($query)){
			Add_S($rt);
			@extract($rt);
			$tfid != $fid && Showmsg('admin_forum_right');
			$topped > 0 && $updatetop = 1;
			// ¾²Ì¬Ä£°æ¸üÐÂ
			if($foruminfo['allowhtm'] == 1){
				$date=date('ym',$postdate);
				$htmurldel=R_P.$htmdir.'/'.$fid.'/'.$date.'/'.$tid.'.html';
				P_unlink($htmurldel);
			}
			$toname = addslashes($forum[$to_id]['name']);
			if($ifmsg){
				$msgdb[] =array(
					$author,
					$winduid,
					'move_title',
					$timestamp,
					'move_content',
					'N',
					$windid,
					'fid'		=> $fid,
					'tid'		=> $tid,
					'tofid'		=> $to_id,
					'subject'	=> $subject,
					'postdate'	=> get_date($postdate),
					'forum'		=> $forum[$fid]['name'],
					'toforum'	=> $toname,
					'admindate'	=> get_date($timestamp),
					'reason'	=> $atc_content
				);
			}
			$logdb[] = array(
				'type'      => 'move',
				'username1' => $author,
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'move_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'subject'	=> substrs($subject,28),
				'tofid'		=> $to_id,
				'toforum'	=> $toname,
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> $atc_content
			);
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$db->update("UPDATE pw_threads SET fid='$to_id' WHERE tid IN($mids)");
		$db->update("UPDATE pw_posts   SET fid='$to_id' WHERE tid IN($mids)");
		updateforum($fid);
		updateforum($to_id);
		if($updatetop){
			updatetop();
		}
		P_unlink(D_P.'data/bbscache/c_cache.php');
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif ($action == "copy"){
	if (empty($_POST['step'])){
		$forumadd  = '';
		$query = $db->query("SELECT fid,name,allowvisit FROM pw_forums WHERE f_type='hidden'");
		if ($query){
			while ($rt = $db->fetch_array($query)){
				if (strpos($rt['allowvisit'],','.$groupid.',') !== false){
					$forumadd .= "<option value='$rt[fid]'> &nbsp;|- $rt[name]</option>";
				}
			}
		}
		@include_once(D_P.'data/bbscache/forumcache.php');
		require_once PrintEot('mawhole');
		footer();
	} else {
		if($forum[$to_id]['type'] == 'category'){
			Showmsg('mawhole_error');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');
		$updatetop	= 0;
		$ufid		= $fid;

		$query = $db->query("SELECT * FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			Add_S($rt);
			@extract($rt);
			$ufid != $fid && Showmsg('admin_forum_right');
			$topped > 0 && $updatetop = 1;
			$toname = addslashes($forum[$to_id]['name']);
			if($ifmsg){
				$msgdb[] =array(
					$author,
					$winduid,
					'copy_title',
					$timestamp,
					'copy_content',
					'N',
					$windid,
					'fid'		=> $fid,
					'tid'		=> $tid,
					'tofid'		=> $to_id,
					'subject'	=> $subject,
					'postdate'	=> get_date($postdate),
					'forum'		=> $forum[$fid]['name'],
					'toforum'	=> $toname,
					'admindate'	=> get_date($timestamp),
					'reason'	=> $atc_content
				);
			}
			$logdb[] = array(
				'type'      => 'copy',
				'username1' => $author,
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'copy_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $tid,
				'subject'	=> substrs($subject,28),
				'tofid'		=> $to_id,
				'toforum'	=> $toname,
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> $atc_content
			);
			if($pollid){
				$rs        = $db->get_one("SELECT voteopts FROM pw_polls WHERE pollid='$pollid'");
				$voteopts  = $rs['voteopts'];
				$db->update("INSERT INTO pw_polls (voteopts) VALUES('$voteopts')");
				$newpollid = $db->insert_id();
			} else{
				$newpollid = 0;
			}
			$db->update("INSERT INTO pw_threads (fid,icon,titlefont,author,authorid,subject,ifcheck, postdate,lastpost,lastposter,hits,replies,topped,locked,digest,ifupload,pollid,ifmail) VALUES('$to_id','$icon','$titlefont','$author','$authorid','$subject','$ifcheck','$postdate','$lastpost','$lastposter','$hits','$replies','$topped','$locked','$digest','$ifupload','$newpollid','$ifmail')");
			$newtid = $db->insert_id();
			$aid    = str_replace("'","\'",$aid);
			$db->update("INSERT INTO pw_tmsgs (tid,aid,userip,ifsign,buy,ipfrom,ifconvert,content) VALUES('$newtid','$aid','$userip','$ifsign','$buy','$ipfrom','$ifconvert','$content')");
			$query2 = $db->query("SELECT * FROM pw_posts WHERE tid='$tid'");
			while($rt=$db->fetch_array($query2)){
				Add_S($rt);
				@extract($rt);
				$db->update("INSERT INTO pw_posts (fid,tid,aid,author,authorid,icon,postdate,subject,userip,ifsign,alterinfo,ipfrom,ifconvert,ifcheck,content) VALUES ('$to_id','$newtid','$aid','$author','$authorid','$icon','$postdate','$subject','$userip','$ifsign','$alterinfo','$ipfrom','$ifconvert','$ifcheck','$content')");
			}
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		updateforum($to_id);
		if($updatetop){
			updatetop();
		}
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action=="headtopic"){
	if(empty($_POST['step'])){
		if(is_numeric($seltid)){
			$rt = $db->get_one("SELECT fid,topped FROM pw_threads WHERE tid='$seltid'");
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			${'topped_'.$rt['topped']} = 'checked';
		}
		require_once PrintEot('mawhole');footer();
	} else{
		if($topped > 1 && ($groupid == 5 || $SYSTEM['topped'] < $topped)){
			Showmsg('masigle_top');
		}
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');

		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,topped FROM pw_threads WHERE tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			if($rt['topped'] > 1 && ($groupid == 5 || $SYSTEM['topped'] < $rt['topped'])){
				Showmsg('masigle_top');
			}
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			if($topped && $topped!=$rt['topped']){
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'top_title',
						$timestamp,
						'top_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				$logdb[] = array(
					'type'      => 'topped',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'topped_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'topped'	=> $topped,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			}elseif($rt['topped'] && !$topped){
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'untop_title',
						$timestamp,
						'untop_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				$logdb[] = array(
					'type'      => 'topped',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'untopped_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			}
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$db->update("UPDATE pw_threads SET topped='$topped' WHERE tid IN($selids)");
		updatetop();
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action=="digest"){
	if(empty($_POST['step'])){
		if(is_numeric($seltid)){
			$rt = $db->get_one("SELECT fid,digest FROM pw_threads WHERE tid='$seltid'");
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			${'digest_'.$rt['digest']} = 'checked';
		}
		require_once PrintEot('mawhole');footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');

		include_once(D_P.'data/bbscache/creditdb.php');
		$creditset = get_creditset($foruminfo['creditset'],$db_creditset);
		$add_rvrc  = floor($creditset['rvrc']['Digest']/10);
		$add_money = $creditset['money']['Digest'];
		$del_rvrc  = floor($creditset['rvrc']['Undigest']/10);
		$del_money = $creditset['money']['Undigest'];

		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,digest FROM pw_threads WHERE tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			if(!$rt['digest'] && $digest){
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'digest_title',
						$timestamp,
						'digest_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'affect'    => "{$db_rvrcname}£º+{$add_rvrc}£¬{$db_moneyname}£º+{$add_money}",
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				dtchange($rt['authorid'],$creditset['rvrc']['Digest'],0,$creditset['money']['Digest']);
				$db->update("UPDATE pw_memberdata SET digests=digests+1 WHERE uid='$rt[authorid]'");
				customcredit($rt['authorid'],$creditset,'Digest');
				$logdb[] = array(
					'type'      => 'digest',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'digest_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'digest'	=> $digest,
					'affect'    => "{$db_rvrcname}£º+{$add_rvrc}£¬{$db_moneyname}£º+{$add_money}",
					'tid'		=> $rt['tid'],
					'digest'	=> $digest,
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			} elseif($rt['digest'] && !$digest){
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'undigest_title',
						$timestamp,
						'undigest_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'affect'    => "{$db_rvrcname}£º-{$del_rvrc}£¬{$db_moneyname}£º-{$del_money}",
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				dtchange($rt['authorid'],-$creditset['rvrc']['Undigest'],0,-$creditset['money']['Undigest']);
				$db->update("UPDATE pw_memberdata SET digests=digests-1 WHERE uid='$rt[authorid]'");
				customcredit($rt['authorid'],$creditset,'Undigest');
				$logdb[] = array(
					'type'      => 'digest',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'undigest_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'affect'    => "{$db_rvrcname}£º-{$del_rvrc}£¬{$db_moneyname}£º-{$del_money}",
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			}
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$db->update("UPDATE pw_threads SET digest='$digest' WHERE tid IN($selids)");
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action=="lock"){
	if(empty($_POST['step'])){
		if(is_numeric($seltid)){
			$rt = $db->get_one("SELECT fid,locked FROM pw_threads WHERE tid='$seltid'");
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			${'lock_'.$rt['locked']} = 'checked';
		}
		require_once PrintEot('mawhole');footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,locked FROM pw_threads WHERE tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			if(!$rt['locked'] && $locked){
				if($locked==2){
					P_unlink(R_P."$htmdir/$fid/".date('ym',$rt['postdate'])."/$tid.html");
				}
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'lock_title',
						$timestamp,
						'lock_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				$logdb[] = array(
					'type'      => 'locked',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'lock_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			} elseif($rt['locked'] && !$locked){
				if($ifmsg){
					$msgdb[] = array(
						$rt['author'],
						$winduid,
						'unlock_title',
						$timestamp,
						'unlock_content',
						'',
						$windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> $forum[$fid]['name'],
						'admindate'	=> get_date($timestamp),
						'reason'	=> $atc_content
					);
				}
				$logdb[] = array(
					'type'      => 'locked',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'unlock_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> $atc_content
				);
			}
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$db->update("UPDATE pw_threads SET locked='$locked' WHERE tid IN($selids)");
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action=="pushtopic"){
	if(empty($_POST['step'])){
		require_once PrintEot('mawhole');footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject FROM pw_threads WHERE tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			if($ifmsg){
				$msgdb[] = array(
					$rt['author'],
					$winduid,
					'push_title',
					$timestamp,
					'push_content',
					'',
					$windid,
					'fid'		=> $fid,
					'tid'		=> $rt['tid'],
					'subject'	=> $rt['subject'],
					'postdate'	=> get_date($rt['postdate']),
					'forum'		=> $forum[$fid]['name'],
					'admindate'	=> get_date($timestamp),
					'reason'	=> $atc_content
				);
			}
			$logdb[] = array(
				'type'      => 'push',
				'username1' => $rt['author'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'push_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $rt['tid'],
				'subject'	=> substrs($rt['subject'],28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> $atc_content
			);
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$db->update("UPDATE pw_threads SET lastpost='$timestamp' WHERE tid IN($selids)");
		refreshto("thread.php?fid=$fid",'operate_success');
	}
} elseif($action=="edit"){
	if(empty($_POST['step'])){
		if(is_numeric($seltid)){
			$rt=$db->get_one("SELECT fid,titlefont,author FROM pw_threads WHERE tid='$seltid'");
			if($fid!=$rt['fid']){
				Showmsg('admin_forum_right');
			}
			$titledetail=explode("~",$rt['titlefont']);
			$titlecolor=$titledetail[0];
			$titlecolor ? $ifchecked[$titlecolor]='checked' : $ifchecked['none']='checked';
			if($titledetail[1]=='1')$ifchecked[1]='checked';
			if($titledetail[2]=='1')$ifchecked[2]='checked';
			if($titledetail[3]=='1')$ifchecked[3]='checked';
		}
		require_once PrintEot('mawhole');footer();
	} else{
		count($tidarray) > 500 && Showmsg('mawhole_count');
		$selids = '';
		foreach($tidarray as $k => $v){
			if(is_numeric($v)){
				$selids .= $selids ? ','.$v : $v;
			}
		}
		!$selids && Showmsg('mawhole_nodata');
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject FROM pw_threads WHERE tid IN($selids)");
		while($rt=$db->fetch_array($query)){
			if($fid != $rt['fid']){
				Showmsg('admin_forum_right');
			}
			if($ifmsg){
				$msgdb[] = array(
					$rt['author'],
					$winduid,
					'highlight_title',
					$timestamp,
					'highlight_content',
					'',
					$windid,
					'fid'		=> $fid,
					'tid'		=> $rt['tid'],
					'subject'	=> $rt['subject'],
					'postdate'	=> get_date($rt['postdate']),
					'forum'		=> $forum[$fid]['name'],
					'admindate'	=> get_date($timestamp),
					'reason'	=> $atc_content
				);
			}
			$logdb[] = array(
				'type'      => 'highlight',
				'username1' => $rt['author'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'highlight_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $rt['tid'],
				'subject'	=> substrs($rt['subject'],28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> $atc_content
			);
		}
		foreach($msgdb as $key=>$val){
			writenewmsg($val,1);
		}
		foreach($logdb as $key=>$val){
			writelog($val);
		}
		$titlefont = "$title1~$title2~$title3~$title4~$title5~$title6~";
		$db->update("UPDATE pw_threads SET titlefont='$titlefont' WHERE tid IN($selids)");
		refreshto("thread.php?fid=$fid",'operate_success');
	}
}
?>