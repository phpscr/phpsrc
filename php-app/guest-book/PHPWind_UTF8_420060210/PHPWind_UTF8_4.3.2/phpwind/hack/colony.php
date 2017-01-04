<?php
require_once(R_P."require/forum.php");
require_once(D_P."data/bbscache/cn_config.php");
$db_perpage = 15;

$basename = "hack.php?H_name=colony";
$cy_name  = $db_hackdb['colony'][0];
if($groupid != 3 && !$cn_open){
	Showmsg('colony_close');
}
!$winduid && Showmsg('not_login');
$cyid   = (int)$cyid;
$cynum  = 0;
$cydb   = $mycydb = array();
$query  = $db->query("SELECT cm.*,cy.* FROM pw_cmembers cm LEFT JOIN pw_colonys cy ON cy.id=cm.colonyid WHERE cm.uid='$winduid'");
while($rt = $db->fetch_array($query)){
	$cynum ++;
	$mycydb[$rt['id']] = $rt;
}
if($cyid && $mycydb[$cyid]){
	$cydb = $mycydb[$cyid];
}
if (!$job) {
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_colonys");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$colonydb = array();
	$query = $db->query("SELECT c.*,cl.cname AS classname FROM pw_colonys c LEFT JOIN pw_cnclass cl ON cl.cid=c.classid ORDER BY createtime DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['createtime'] = get_date($rt['createtime']);
		$colonydb[] = $rt;
	}
	require_once PrintEot('colony');footer();
}elseif ($job=='creat'){
	!$cn_newcolony && Showmsg('colony_reglimit');
	if (strpos($cn_groups,",$groupid,") === false){
		Showmsg('colony_groupright');
	}
	$winddb['currency'] < $cn_createmoney && Showmsg('colony_creatfailed');
	if (!$step){
		$query=$db->query("SELECT * FROM pw_cnclass");
		$classdb=array();
		while ($rt=$db->fetch_array($query)){
			$classdb[]=$rt;
		}
		require_once PrintEot('colony');footer();
	}elseif($step=='2'){
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_colonys WHERE admin='$windid'");
		if ($cn_allowcreate && $rt['sum'] >= $cn_allowcreate){
			Showmsg('colony_numlimit');
		}
		!$cn_class && Showmsg('colony_class');
		!$cnname && Showmsg('colony_emptyname');
		$rt = $db->get_one("SELECT id FROM pw_colonys WHERE cname='$cnname'");
		if ($rt['id']){
			Showmsg('colony_samename');
		}
		$cnname = Char_cv($cnname);
		$db->update("UPDATE pw_memberdata SET currency=currency-'$cn_createmoney' WHERE uid='$winduid'");
		$db->update("INSERT INTO pw_colonys(cname,classid,admin,members,ifcheck,cmoney,createtime,intomoney) VALUES('$cnname','$cn_class','$windid','1','1','$cn_createmoney','$timestamp','$cn_joinmoney')");
		$cid = $db->insert_id();
		$db->update("INSERT INTO pw_cmembers(uid,username,ifadmin,colonyid) VALUES('$winduid','$windid','1','$cid')");
		
		require_once(R_P.'require/tool.php');
		$logdata=array(
			'type'		=>	'colony',
			'nums'		=>	0,
			'money'		=>	0,
			'descrip'	=>	'colony_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'currency'	=>	$cn_createmoney,
			'cname'	=>	$cnname,
		);
		writetoollog($logdata);

		refreshto("$basename&cyid=$cid&job=set",'colony_regsuccess');
	}
}elseif ($job=='join'){
	if($cn_allowjoin && $cynum >=  $cn_allowjoin){
		Showmsg('colony_joinlimit');
	}
	$cydb = $db->get_one("SELECT cname,members,ifcheck,intomoney FROM pw_colonys WHERE id='$id'");
	if ($cn_memberfull && $cydb['members'] >= $cn_memberfull){
		Showmsg('colony_memberlimit');
	}
	!$cydb['ifcheck'] && Showmsg('colony_joinrefuse');
	$cydb['intomoney'] = (int)$cydb['intomoney'];
	$cydb['intomoney'] < 0 && Showmsg('numerics_checkfailed');
	if($cn_joinmoney && $cydb['intomoney'] < $cn_joinmoney){
		$cydb['intomoney'] = $cn_joinmoney;		
	}
	if($cydb['intomoney'] && $winddb['currency'] < $cydb['intomoney']){
		Showmsg('colony_joinfail');
	}

	$rt = $db->get_one("SELECT id,ifadmin FROM pw_cmembers WHERE uid='$winduid' AND colonyid='$id'");
	if ($rt['id']){
		Showmsg('colony_alreadyjoin');
	}
	if(!$step){
		require_once PrintEot('colony');footer();
	} elseif ($step == 2){
		if(!$realname){
			Showmsg('colony_realname');
		}
		$realname  = Char_cv($realname);
		$tel       = Char_cv($tel);
		$email     = Char_cv($email);
		$introduce = Char_cv($introduce);
		if(strlen($realname) > 20){
			Showmsg('realname_limit');
		}
		if(strlen($tel) > 15){
			Showmsg('tel_limit');
		}
		if(strlen($introduce) > 255){
			Showmsg('intro_limit');
		}
		$rt = $db->get_one("SELECT id FROM pw_cmembers WHERE realname='$realname' AND colonyid='$id'");
		if($rt['id']){
			Showmsg('colony_samerealname');
		}
		$db->update("INSERT INTO pw_cmembers SET uid='$winduid',username='$windid', realname='$realname',ifadmin='-1',gender='$gender',tel='$tel',email='$email',introduce='$introduce',colonyid='$id'");
		$db->update("UPDATE pw_colonys SET members=members+1 WHERE id='$id'");
		refreshto("$basename&cyid=$id&job=view&id=$id",'colony_joinsuccess');
	}
} elseif ($job=='view'){
	if($mycydb[$id]){
		$cndb = $mycydb[$id];
	} else{
		$cndb = $db->get_one("SELECT * FROM pw_colonys WHERE id='$id'");
	}
	!$cndb && ObHeader($basename);
	if($cn_joinmoney && $cndb['intomoney'] < $cn_joinmoney){
		$cndb['intomoney'] = $cn_joinmoney;
	}
	$cndb['createtime'] = get_date($cndb['createtime']);
	$cndb['descrip']    = str_replace("\n","<br>",$cndb['descrip']);
	$cndb['annouce']    = str_replace("\n","<br>",$cndb['annouce']);
	require_once PrintEot('colony');footer();
} elseif ($job=='card'){
	if(!$cydb && $windid != $manager){
		Showmsg('colony_cardright');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$pages = numofpage($cydb['members'],$page,ceil($cydb['members']/$db_perpage),"$basename&cyid=$cyid&job=card&");

	$query = $db->query("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' $limit");
	while($rt = $db->fetch_array($query)){
		!$rt['realname'] && $rt['realname'] = $rt['username'];
		$memberdb[] = $rt;
	}
	require_once PrintEot('colony');footer();
} elseif ($job=='editcard'){
	if(!$cydb){
		Showmsg('colony_editcard');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	if (!$step){
		$rt = $db->get_one("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$winduid'");
		!$rt && Showmsg('colony_nocard');
		$gender_0=$gender_1=$gender_2='';
		${'gender_'.$rt['gender']} = "selected";
		require_once PrintEot('colony');footer();
	}elseif ($step == '2'){
		if(!$realname){
			Showmsg('colony_realname');
		}
		$realname  = Char_cv($realname);
		$tel       = Char_cv($tel);
		$email     = Char_cv($email);
		$introduce = Char_cv($introduce);
		$gender    = (int)$gender;
		if($realname != $cydb['realname']){
			$rt = $db->get_one("SELECT id FROM pw_cmembers WHERE realname='$realname' AND colonyid='$cyid'");
			if($rt['id']){
				Showmsg('colony_samerealname');
			}
		}
		$db->update("UPDATE pw_cmembers SET realname='$realname',gender='$gender',tel='$tel',email='$email',introduce='$introduce' WHERE colonyid='$cyid' AND uid='$winduid'");
		refreshto("$basename&cyid=$cyid&job=seecard&uid=$winduid",'colony_cardsuccess');
	}
} elseif ($job=='seecard'){
	if(!$cydb && $windid != $manager){
		Showmsg('colony_editcard');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	$rt = $db->get_one("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$uid'");
	if(!$rt){
		Showmsg('colony_noseecard');
	}
	!$rt['realname'] && $rt['realname'] = $rt['username'];
	require_once PrintEot('colony');footer();
} elseif ($job == 'board'){

	if(!$cydb && $windid != $manager){
		Showmsg('colony_boardright');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_argument WHERE gid='$cyid' AND tpcid=0");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=board&");

	$tids	= '';
	$argudb = array();
	$query  = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND tpcid=0 ORDER BY lastpost DESC $limit");
	while ($rt = $db->fetch_array($query)){
		$rt['lastpost'] = get_date($rt['lastpost']);
		$argudb[] = $rt;

		$tids .= $tids ? ','.$rt['tid'] : $rt['tid'];
	}
	$rpdb=array();
	$query  = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND tpcid!=0 ORDER BY postdate");
	while ($rt = $db->fetch_array($query)){
		$rt['postdate'] = get_date($rt['postdate']);
		$rpdb[$rt['tpcid']][]=$rt;
	}
	require_once PrintEot('colony');footer();
} elseif($job == 'read'){
	require_once(R_P.'require/bbscode.php');
	!$tid && Showmsg('data_error');

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_argument WHERE gid='$cyid' AND (tid='$tid' OR tpcid='$tid')");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=read&tid=$tid&");

	$subject= '';
	$agdb	= array();
	$query = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND (tid='$tid' OR tpcid='$tid') ORDER BY tid $limit");
	while ($rt = $db->fetch_array($query)){
		if($rt['tpcid'] == '0'){
			$subject = $rt['subject'];
		}
		$rt['content']	= str_replace("\n","<br>",$rt['content']);
		$rt['content']	= convert($rt['content'],$db_windpost);
		$rt['postdate'] = get_date($rt['postdate']);
		$agdb[] = $rt;
	}
	require_once PrintEot('colony');footer();
} elseif($job == 'post'){
	require_once(R_P.'require/postfunc.php');
	if(!$cydb){
		Showmsg('colony_boardright');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	$tid=(int)$tid;
	if($tid){
		$rt = $db->get_one("SELECT tid FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
		if($rt){
			$db->update("UPDATE pw_argument SET lastpost='$timestamp' WHERE tid='$tid' AND gid='$cyid' AND tpcid='0'");
			$lastpost='0';
		}else{
			Showmsg('undefined_action');
		}
	}else{
		$lastpost=$timestamp;
	}
	$cydb = $db->get_one("SELECT realname,username FROM pw_cmembers WHERE colonyid='$cyid' and uid='$winduid'");
	if(!$cydb['realname']){
		$cydb['realname'] = $cydb['username'];
	}
	if(!$subject || !$content){
		Showmsg('colony_posterror');
	}
	$subject = Char_cv($subject);
	$content = Char_cv($content);
	$content = autourl($content);
	
	$db->update("INSERT INTO pw_argument(tpcid,gid,author,authorid,postdate,lastpost,subject,content) values('$tid','$cyid','$cydb[realname]','$winduid','$timestamp','$lastpost','$subject','$content')");
	!$tid && $tid=$db->insert_id();
	refreshto("$basename&job=read&cyid=$cyid&tid=$tid",'colony_postsuccess');
}elseif($job == 'editpost'){
	require_once(R_P.'require/postfunc.php');
	if(!$cydb){
		Showmsg('colony_boardright');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	$argu = $db->get_one("SELECT tid,tpcid,gid,subject,content,author,authorid FROM pw_argument WHERE tid='$tid'");
	if(!$argu || $argu['gid'] != $cyid){
		Showmsg('data_error');
	}
	if($argu['authorid'] != $cydb['uid'] && $cydb['ifadmin'] != '1'){
		Showmsg('colony_editright');
	}
	if (!$step){
		$argu['content'] = trim($argu['content']);
		require_once PrintEot('colony');footer();
	} elseif ($step == '2'){
		if(!$subject || !$content){
			Showmsg('colony_posterror');
		}
		$subject = Char_cv($subject);
		$content = Char_cv($content);
		$content = autourl($content);
		$db->update("UPDATE pw_argument SET subject='$subject',content='$content' WHERE tid='$tid'");
		$argu['tpcid']!='0' && $tid = $argu['tpcid'];
		refreshto("$basename&cyid=$argu[gid]&job=read&tid=$tid",'colony_editsuccess');
	}
}elseif($job == 'delpost'){
	if(!$cydb){
		Showmsg('colony_boardright');
	}
	if($cydb['ifadmin']=='-1'){
		Showmsg('colony_nocheck');
	}
	$rt = $db->get_one("SELECT tid,tpcid,authorid FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
	if(!$rt){
		Showmsg('data_error');
	}
	if($rt['authorid'] != $cydb['uid'] && $cydb['ifadmin'] != '1'){
		Showmsg('colony_delright');
	}
	if($rt['tpcid']=='0'){
		$db->update("DELETE FROM pw_argument WHERE tpcid='$tid' AND gid='$cyid'");
	}
	$db->update("DELETE FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
	refreshto("$basename&cyid=$cyid&job=board&",'colony_delsuccess');
}elseif ($job=='donate'){
	if(!$cydb){
		Showmsg('colony_donateright');
	}
	if (!$step){
		require_once PrintEot('colony');footer();
	} elseif ($step == 2){
		if (!is_numeric($sendmoney) || $sendmoney <= '0'){
			Showmsg('colony_donateerror');
		}
		if($sendmoney > $winddb['currency']){
			Showmsg('colony_donatefail');
		}
		$db->update("UPDATE pw_memberdata SET currency=currency-'$sendmoney' WHERE uid='$winduid'");
		$db->update("UPDATE pw_colonys    SET cmoney=cmoney+'$sendmoney' WHERE id='$cyid'");

		require_once(R_P.'require/tool.php');
		$logdata=array(
			'type'		=>	'donate',
			'nums'		=>	0,
			'money'		=>	0,
			'descrip'	=>	'donate_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'currency'	=>	$sendmoney,
			'cname'		=>	$cydb['cname'],
		);
		writetoollog($logdata);

		$log = array(
			'type'      => 'cy_donate',
			'username1' => $windid,
			'username2' => '',
			'field1'    => $sendmoney,
			'field2'    => $cyid,
			'field3'    => $cydb['cname'],
			'descrip'   => 'donate_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'cname'		=> $cydb['cname'],
		);
		colonylog($log);

		require_once(R_P.'require/msg.php');
		$message=array(
			$cydb['admin'],
			$winduid,
			'donate_title',
			$timestamp,
			'donate_content',
			'',
			$windid
		);
		writenewmsg($message,1);

		Showmsg('colony_donatesuccess');
	}
}elseif ($job=='donatelog'){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE field2='$cyid' AND type='cy_donate'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=donatelog&");

	$logdb=array();
	$query = $db->query("SELECT * FROM pw_forumlog WHERE field2='$cyid' AND type='cy_donate' ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$logdb[] = $rt;
	}
	require_once PrintEot('colony');footer();
}elseif ($job=='quit'){
	if ($cydb['admin'] == $windid){
		Showmsg('colony_quitfail');
	}else{
		if (!$cydb){
			Showmsg('undefined_action');
		}
		$db->update("UPDATE pw_colonys SET members=members-1 WHERE id='$cyid'");
		$db->update("DELETE FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$winduid'");
		refreshto($basename,'colony_quitsuccess');
	}
}elseif ($job=='set'){
	if($cydb['ifadmin'] != 1){
		Showmsg('colony_adminright');
	}
	if (!$step){
		if ($cydb['ifcheck']=='1'){
			$ifcheck_1='checked';
			$ifcheck_0='';
		}else{
			$ifcheck_0='checked';
			$ifcheck_1='';
		}
		if ($cydb['annoucesee']=='1'){
			$annoucesee_1='checked';
			$annoucesee_0='';
		}else{
			$annoucesee_0='checked';
			$annoucesee_1='';
		}
		if($cn_joinmoney && $cydb['intomoney'] < $cn_joinmoney){
			$cydb['intomoney'] = $cn_joinmoney;
		}
		$classdb = array();
		$query = $db->query("SELECT * FROM pw_cnclass");
		while ($rt = $db->fetch_array($query)){
			$classdb[] = $rt;
		}
		require_once PrintEot('colony');footer();
	}elseif ($step == '2'){
		!$cname && Showmsg('colony_emptyname');
		if($cydb['cname'] != $cname){
			$rt = $db->get_one("SELECT id FROM pw_colonys WHERE cname='$cname'");
			if ($rt['id']){
				Showmsg('colony_samename');
			}
		}
		$cname		= Char_cv($cname);
		$descrip	= Char_cv($descrip);
		$annouce	= Char_cv($annouce);
		if(strlen($cname) > 20){
			Showmsg('colony_cnamelimit');
		}
		if(strlen($descrip) > 255){
			Showmsg('colony_descriplimit');
		}
		if(strlen($annouce) > 255){
			Showmsg('colony_annoucelimit');
		}
		if($_FILES['attachment']){
			$attachment=$_FILES['attachment'];
			if (is_array($attachment)){
				$attachment_name = $attachment['name'];
				$attachment_size = $attachment['size'];
				$attachment      = $attachment['tmp_name'];
			}
			$attach_ext = substr(strrchr($attachment_name,'.'),1);
			$filename = "$imgdir/cn_img/colony_$cyid.$attach_ext";
			!$cn_imgsize && $cn_imgsize=1024000;
			if ($attachment_size > $cn_imgsize){
				Showmsg('colony_sizelimit');
			}
			if(function_exists("move_uploaded_file") && @move_uploaded_file($attachment, $filename)){
				chmod($filename,0777);
				$cnimg = ",cnimg='colony_$cyid.$attach_ext'";
			}elseif(@copy($attachment, $filename)){
				chmod($filename,0777);
				$cnimg = ",cnimg='colony_$cyid.$attach_ext'";
			}elseif(is_readable($attachment) && writeover($filename,readover($attachment)) && file_exists($filename)){
				chmod($filename,0777);
				$cnimg = ",cnimg='colony_$cyid.$attach_ext'";
			} else {
				$attachment && Showmsg('colony_uploadfail');
			}
		} else{
			$cnimg = '';
		}
		if (function_exists('getimagesize') && $cnimg && !(list($width,$height)=getimagesize($filename))){
			P_unlink($filename);
			$db->update("UPDATE pw_colonys SET cnimg='' WHERE id='$cyid'");
			Showmsg('pro_loadimg_error');
		}
		if ($cn_imgwidth && $cn_imgheight && ($width > $cn_imgwidth || $height > $cn_imgheight)){
			P_unlink($filename);
			$db->update("UPDATE pw_colonys SET cnimg='' WHERE id='$cyid'");
			Showmsg('colony_uploadfail');
		}
		unset($width,$height);
	
		$intomoney = (int)$intomoney;
		if($cn_joinmoney && $intomoney < $cn_joinmoney){
			$intomoney = $cn_joinmoney;
		}
		if($intomoney < 0){
			Showmsg('numerics_checkfailed');
		}
		$db->update("UPDATE pw_colonys SET cname='$cname',classid='$classid',ifcheck='$ifcheck' $cnimg,intomoney='$intomoney',annouce='$annouce',annoucesee='$annoucesee',descrip='$descrip' WHERE id='$cyid'");
		refreshto("$basename&cyid=$cyid&job=view&id=$cyid",'colony_setsuccess');
	}
} elseif ($job=='member'){
	if($cydb['ifadmin'] != 1){
		Showmsg('colony_adminright');
	}
	if(!$action){
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$pages = numofpage($cydb['members'],$page,ceil($cydb['members']/$db_perpage),"$basename&cyid=$cyid&job=member&");

		$query = $db->query("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' $limit");
		while($rt = $db->fetch_array($query)){
			!$rt['realname'] && $rt['realname'] = $rt['username'];
			$memberdb[] = $rt;
		}
		require_once PrintEot('colony');footer();
	} else{
		$selids = 0;
		foreach($selid as $key => $value){
			!is_numeric($value) && Showmsg('numerics_checkfailed');
			$selids .= ','.$value;
		}
		if($action == 'addadmin'){
			$windid != $cydb['admin'] && Showmsg('colony_manager');
			$db->update("UPDATE pw_cmembers SET ifadmin=1 WHERE uid IN($selids) AND colonyid='$cyid'");
			Showmsg('colony_addamin');
		} elseif($action == 'deladmin'){
			$windid != $cydb['admin'] && Showmsg('colony_manager');
			$query = $db->query("SELECT * FROM pw_cmembers WHERE uid IN($selids) AND colonyid='$cyid'");
			while($rt = $db->fetch_array($query)){
				if($rt['username'] == $cydb['admin']){
					Showmsg('colony_delladminfail');
				}
			}
			$db->update("UPDATE pw_cmembers SET ifadmin=0 WHERE uid IN($selids) AND colonyid='$cyid'");
			Showmsg('colony_deladmin');
		} elseif($action == 'pass'){
			require_once(R_P.'require/tool.php');
			$query = $db->query("SELECT c.uid,c.username,c.ifadmin,md.currency,md.onlineip FROM pw_cmembers c LEFT JOIN pw_memberdata md USING(uid) WHERE c.uid IN($selids)");
			while ($rt = $db->fetch_array($query)){
				if($rt['currency'] < $cydb['intomoney']){
					Showmsg('colony_passfail');
				}elseif($rt[ifadmin]=='-1'){
					$db->update("UPDATE pw_cmembers SET ifadmin=0 WHERE uid='$rt[uid]' AND colonyid='$cyid'");
					$db->update("UPDATE pw_memberdata SET currency=currency-'$cydb[intomoney]' WHERE uid='$rt[uid]'");
					$db->update("UPDATE pw_colonys SET cmoney=cmoney+'$cydb[intomoney]' WHERE id='$cyid'");

					if(strpos($rt['onlineip'],'|')!==false){
						$rt['onlineip'] = substr($rt['onlineip'],0,strpos($rt['onlineip'],'|'));
					}
					$logdata=array(
						'type'		=>	'join',
						'nums'		=>	0,
						'money'		=>	0,
						'descrip'	=>	'join_descrip',
						'uid'		=>	$rt['uid'],
						'username'	=>	$rt['username'],
						'ip'		=>	$rt['onlineip'],
						'time'		=>	$timestamp,
						'currency'	=>	$cydb['intomoney'],
						'cname'  	=>	$cydb['cname'],
					);
					writetoollog($logdata);

					$log = array(
						'type'      => 'cy_join',
						'username1' => $rt['username'],
						'username2' => $windid,
						'field1'    => $cydb['intomoney'],
						'field2'    => $cyid,
						'field3'    => $cydb['cname'],
						'descrip'   => 'join_descrip',
						'timestamp' => $timestamp,
						'ip'        => $onlineip,
					);
					colonylog($log);

					require_once(R_P.'require/msg.php');
					$message=array(
						$rt['username'],
						$winduid,
						'join_title',
						$timestamp,
						'join_content',
						'',
						$windid
					);
					writenewmsg($message,1);
				}
			}
			Showmsg('colony_pass');
		} elseif($action == 'del'){
			$query = $db->query("SELECT * FROM pw_cmembers WHERE uid IN($selids) AND colonyid='$cyid'");
			while($rt = $db->fetch_array($query)){
				if($rt['username'] == $cydb['admin']){
					Showmsg('colony_delfail');
				}elseif($windid != $cydb['admin'] && $rt['ifadmin'] == '1'){
					Showmsg('colony_manager');
				}
			}
			$count = count($selids);
			$db->update("DELETE FROM pw_cmembers WHERE uid IN($selids) AND colonyid='$cyid'");
			$db->update("UPDATE pw_colonys SET members=members-'$count' WHERE id='$cyid'");
			Showmsg('colony_del');
		}
	}
} elseif($job == 'currency'){
	$windid != $cydb['admin'] && Showmsg('colony_currency_right');
	!$cn_virement && Showmsg('colony_currency');

	if(!$_POST['step']){
		require_once PrintEot('colony');footer();
	}elseif($_POST['step']==2){
		$rt		= $db->get_one("SELECT uid FROM pw_cmembers WHERE username='$pwuser' AND colonyid='$cyid'");
		if(!$rt){
			Showmsg('no_colony_member');
		}
		$touid	= $rt['uid'];
		if(!is_numeric($currency) || $currency < 0){
			Showmsg('illegal_nums');
		}
		if(!$pwpwd){
			Showmsg('empty_password');
		}
		$rt = $db->get_one("SELECT uid FROM pw_members WHERE uid='$winduid' AND password='".md5($pwpwd)."'");
		if(!$rt){
			Showmsg('password_error');
		}
		$tax = round($currency * $cn_rate/100);
		$needcurrency = $currency + $tax;
		if($cydb['cmoney'] < $needcurrency){
			Showmsg('colony_noenough_currency');
		}
		$db->update("UPDATE pw_colonys SET cmoney=cmoney-'$currency' WHERE id='$cyid'");
		$db->update("UPDATE pw_memberdata SET currency=currency+'$currency' WHERE uid='$touid'");
		require_once(R_P.'require/tool.php');
		$logdata=array(
			'type'		=>	'vire',
			'nums'		=>	0,
			'money'		=>	0,
			'descrip'	=>	'cyvire_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toname'	=>	$pwuser,
			'currency'	=>	$currency,
			'tax'		=>	$tax
		);
		writetoollog($logdata);

		$log = array(
			'type'      => 'cy_vire',
			'username1' => $pwuser,
			'username2' => $windid,
			'field1'    => $currency,
			'field2'    => $cyid,
			'field3'    => $cydb['cname'],
			'descrip'   => 'cy_vire_descrip',
			'timestamp' => $timestamp,
			'ip'        => $onlineip,
			'cname'		=> $cydb['cname'],
			'tax'		=> $tax
		);
		colonylog($log);

		require_once(R_P.'require/msg.php');
		$message=array(
			$pwuser,
			$winduid,
			'cyvire_title',
			$timestamp,
			'cyvire_content',
			'',
			$windid
		);
		writenewmsg($message,1);

		Showmsg('virement_success');
	}
}elseif($job=='currencylog'){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE field2='$cyid' AND type='cy_vire'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=currencylog&");

	$logdb=array();
	$query = $db->query("SELECT * FROM pw_forumlog WHERE field2='$cyid' AND type='cy_vire' ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$logdb[] = $rt;
	}
	require_once PrintEot('colony');footer();
}elseif($job=='joinlog'){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE field2='$cyid' AND type='cy_join'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=joinlog&");

	$logdb=array();
	$query = $db->query("SELECT * FROM pw_forumlog WHERE field2='$cyid' AND type='cy_join' ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$logdb[] = $rt;
	}
	require_once PrintEot('colony');footer();
}elseif ($job=='cancel'){
	if($cydb['admin']!=$windid){
		Showmsg('colony_cancel');
	}
	!$cn_remove && Showmsg('colony_cancelclose');
	P_unlink("$imgdir/cn_img/$cydb[cnimg]");
	$db->update("DELETE FROM pw_cmembers WHERE colonyid='$cyid'");
	$db->update("DELETE FROM pw_colonys WHERE id='$cyid'");
	refreshto($basename,'colony_cancelsuccess');
} else {
	Showmsg('undefined_action');
}

function colonylog($log){
	global $db;
	require GetLang('log');
	$log['descrip'] = $lang[$log['descrip']];
	$db->update("INSERT INTO pw_forumlog (type,username1,username2,field1,field2,field3,descrip,timestamp,ip) VALUES('$log[type]','$log[username1]','$log[username2]','$log[field1]','$log[field2]','$log[field3]','$log[descrip]','$log[timestamp]','$log[ip]')");
}
?>