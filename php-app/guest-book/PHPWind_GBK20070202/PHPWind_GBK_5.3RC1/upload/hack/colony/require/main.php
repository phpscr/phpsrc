<?php
!function_exists("cn_credit") && die("Forbidden");

if($job=="album"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	if(!$mycydb && !$cydb['albumopen'] && $groupid!=3) Showmsg('colony_opentocn');
	
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_cnalbum WHERE cyid='$cyid'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=album&");
	
	$albumdb = array();
	$query = $db->query("SELECT * FROM pw_cnalbum WHERE cyid='$cyid' ORDER BY crtime DESC $limit");
	while($rt = $db->fetch_array($query)){
		if(!$rt['lastphoto'] ){
			$rt['lastphoto'] = "$hkimg/nophoto.gif";
		}else{
			$rt['lastphoto']=getsmallurl($rt['lastphoto']);
		}
		
		$rt['crtime']=get_date($rt['crtime'],"Y.m.d");
		$albumdb[] = $rt;
	}
}elseif($job=="board"){
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_argument WHERE gid='$cyid' AND tpcid=0");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=board&");
	
	$argudb = array();
	$query  = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND tpcid=0 ORDER BY lastpost DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['lastpost'] = get_date($rt['lastpost']);
		$argudb[] = $rt;
	}
}elseif($job=="cancel"){
	!$cn_remove && Showmsg('colony_cancelclose');
	if($cydb['admin']!=$windid){
		Showmsg('colony_cancel');
	}else{
		$cydb['members']>1 && Showmsg('colony_del_members');
		$rs=$db->get_one("SELECT count(*) as sum FROM pw_cnalbum WHERE cyid='$cyid'");
		$rs['sum']>0 && Showmsg('colony_del_photo');
	}
	P_unlink("$imgdir/cn_img/$cydb[cnimg]");
	$db->update("DELETE FROM pw_cmembers WHERE colonyid='$cyid'");
	$db->update("DELETE FROM pw_colonys WHERE id='$cyid'");
	refreshto($basename,'colony_cancelsuccess');	
}elseif($job=="card"){
	!$mycydb && $groupid!=3 && Showmsg('colony_viewcard');
	
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$pages = numofpage($cydb['members'],$page,ceil($cydb['members']/$db_perpage),"$basename&cyid=$cyid&job=card&");
	
	$colonyadmins="";
	$query = $db->query("SELECT c.*,m.thisvisit FROM pw_cmembers c LEFT JOIN pw_memberdata m USING(uid) WHERE c.colonyid='$cyid' $limit");
	while($rt = $db->fetch_array($query)){
		!$rt['realname'] && $rt['realname'] = $rt['username'];
		$rt['ifadmin'] && $colonyadmins.=" <a href=profile.php?action=show&uid=$rt[uid]>".$rt['username']."</a> ";
		$memberdb[] = $rt;
	}
}elseif($job=="creatalbum"){
	!$cn_phopen && Showmsg("colony_phopen");
	
	!$mycydb && Showmsg('colony_creatalbum');
	
	if(!$step){
		$cydb['ifadmin']!=1 && $ifdisable="disabled";
	}elseif($step==2){
		$atype=(int)$atype;
		!$aname && Showmsg('colony_aname_empty');
	
		if($atype==1){
			$cydb['ifadmin']!=1 && Showmsg('colony_pubalbum');
			$cydb['cmoney']<$cn_camoney && Showmsg('colony_moneylimit');
			$rs=$db->get_one("SELECT albumnum FROM pw_colonys WHERE id='$cyid'");
			$rs['albumnum']>=$cn_albumnum && Showmsg('colony_album_num');
		}else{
			$rs=$db->get_one("SELECT count(*) AS sum FROM pw_cnalbum WHERE uid='$winduid' AND atype>1");
			$rs['sum']>=$cn_albumnum2 && Showmsg('colony_album_num2');
			$usermoney=dosql($winduid);
			if($usermoney<$cn_camoney){
				showmsg('colony_moneylimit2');
			}
			dosql($winduid,"set",-$cn_camoney); //扣掉创建所需积分
		}
		$aname=Char_cv($aname);
		$aintro=Char_cv($aintro);
		$db->update("INSERT INTO pw_cnalbum(aname,aintro,atype,cyid,uid,username,crtime) VALUES('$aname','$aintro','$atype','$cyid','$winduid','$windid','$timestamp')");
		$db->update("UPDATE pw_colonys SET albumnum=albumnum+1 WHERE id='$cyid'");
		refreshto("$basename&job=album&cyid=$cyid",'operate_success');	
	}
}elseif($job=="currency"){
	$windid != $cydb['admin']  && Showmsg('colony_currency_right');
	!$cn_virement && Showmsg('colony_currency');
	
	if(!$step){
		//require_once PrintHack('main');footer();
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
		dosql($touid,"set",$currency);

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
}elseif($job=="currencylog"){
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
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}
}elseif($job=="delalbum"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	$albumdb=$db->get_one("SELECT photonum FROM pw_cnalbum WHERE aid='$aid' AND cyid='$cyid'");
	!$albumdb && Showmsg("data_error");
	
	$cydb['ifadmin']!=1 && $winduid!=$albumdb['uid'] && $groupid!=3 && Showmsg("mawhole_right");
	
	$albumdb['photonum']>0 && Showmsg('colony_photonum');
	$db->update("DELETE FROM pw_cnalbum WHERE aid='$aid'");
	$db->update("UPDATE pw_colonys SET albumnum=albumnum-1 WHERE id='$cyid'");
	refreshto("$basename&cyid=$cyid&job=album",'operate_success');
}elseif($job=="delphoto"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	$photodb="";
	$photodb=$db->get_one("SELECT p.uploader,p.aid,p.path FROM pw_cnphoto p LEFT JOIN pw_cnalbum a USING(aid) WHERE p.pid='$pid' AND a.cyid='$cyid'");
	!$photodb && Showmsg("undefined_action");
	
	!($mycydb['ifadmin']==1 || $photodb['uploader']==$winduid || $groupid==3) && Showmsg("mawhole_right");
	
	$db->update("DELETE FROM pw_cnphoto WHERE pid='$pid'");
	$db->update("UPDATE pw_cnalbum SET photonum=photonum-1 WHERE aid='$photodb[aid]' AND cyid='$cyid'");
	$lastphoto = $db->get_one("SELECT path FROM pw_cnphoto WHERE aid='$photodb[aid]' ORDER BY uptime DESC LIMIT 1");
	$db->update("UPDATE pw_cnalbum SET lastphoto='$lastphoto[path]' WHERE aid='$photodb[aid]'");
	unlink_photo($photodb['path']);
	
	refreshto("$basename&job=viewalbum&cyid=$cyid&aid=$photodb[aid]",'operate_success');
}elseif($job=="delpost"){
	$rt = $db->get_one("SELECT tid,tpcid,authorid FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
	if(!$rt){
		Showmsg('data_error');
	}
	if($rt['authorid'] != $winduid && $cydb['ifadmin']!=1 && $groupid!=3){
		Showmsg('colony_delright');
	}
	if($rt['tpcid']=='0'){
		$db->update("DELETE FROM pw_argument WHERE tpcid='$tid' AND gid='$cyid'");
	}
	$db->update("DELETE FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
	refreshto("$basename&cyid=$cyid&job=board&",'colony_delsuccess');
}elseif($job=="donate"){
	if(!$mycydb){
		Showmsg('colony_donateright');
	}
	if (!$step){
		$usermoney	=	dosql($winduid);
		//require_once PrintHack('main');footer();
	} elseif ($step == 2){
		if (!is_numeric($sendmoney) || $sendmoney <= 0){
			Showmsg('colony_donateerror');
		}
		$usermoney	=	dosql($winduid);
		if($sendmoney > $usermoney){
			Showmsg('colony_donatefail');
		}
		dosql($winduid,"set",-$sendmoney);

		$db->update("UPDATE pw_colonys SET cmoney=cmoney+'$sendmoney' WHERE id='$cyid'");
		$db->update("UPDATE pw_cmembers SET honor=honor+'$sendmoney' WHERE uid='$winduid' AND colonyid='$cyid'");
	
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
}elseif($job=="donatelog"){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');
	
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE field2='$cyid' AND type='cy_donate'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=donatelog&");
	
	$logdb=array();
	$query = $db->query("SELECT * FROM pw_forumlog WHERE field2='$cyid' AND type='cy_donate' ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']    = get_date($rt['timestamp'],"y.m.d H:i");
		$rt['descrip'] = str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}
}elseif($job=="editalbum"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	$albumdb=$db->get_one("SELECT * FROM pw_cnalbum WHERE aid='$aid' AND cyid='$cyid'");
	!$albumdb && Showmsg("data_error");
	if($albumdb['atype']==1){
		$cydb['ifadmin']!=1 && $groupid!=3 && Showmsg("mawhole_right");
	}else{
		$cydb['ifadmin']!=1 && $groupid!=3 && $winduid!=$albumdb['uid'] && Showmsg("mawhole_right");
	}
	if(!$step){
		if($cydb['ifadmin']!=1) $ifdisable="disabled";
		if($albumdb['atype']==1){
			$atype_1="checked=\"checked\"";
		}elseif ($albumdb['atype']==2){
			$atype_2="checked=\"checked\"";
		}else{
			$atype_3="checked=\"checked\"";
		}
	}elseif($step==2){
		$aname=Char_cv($aname);
		$aintro=Char_cv($aintro);
		if($atype==1){
			$cydb['ifadmin']!=1 && Showmsg('colony_pubalbum');
		}
		$db->update("UPDATE pw_cnalbum SET aname='$aname',aintro='$aintro',atype='$atype' WHERE aid='$aid'");
		refreshto("$basename&cyid=$cyid&job=viewalbum&aid=$aid",'operate_success');
	}
}elseif ($job=="editcard"){
	!$mycydb && Showmsg('colony_cnmenber');
	
	if(!$step){
		$rt = $db->get_one("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$winduid'");
		!$rt && Showmsg('colony_nocard');
		$gender_0=$gender_1=$gender_2='';
		${'gender_'.$rt['gender']} = "selected";
		//require_once PrintHack('main');footer();
	}elseif($step == '2'){
		if(!$realname){
			Showmsg('colony_realname');
		}
		$realname  = Char_cv($realname);
		$tel       = Char_cv($tel);
		$email     = Char_cv($email);
		$address   = Char_cv($address);
		$introduce = Char_cv($introduce);
		$gender    = (int)$gender;
		if($realname != $cydb['realname']){
			$rt = $db->get_one("SELECT id FROM pw_cmembers WHERE realname='$realname' AND colonyid='$cyid'");
			if($rt['id']){
				Showmsg('colony_samerealname');
			}
		}
		$db->update("UPDATE pw_cmembers SET realname='$realname',gender='$gender',tel='$tel',email='$email',address='$address',introduce='$introduce' WHERE colonyid='$cyid' AND uid='$winduid'");
		refreshto("$basename&cyid=$cyid&job=seecard&uid=$winduid",'colony_cardsuccess');
	}
}elseif($job=="editphoto"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	$photodb=$db->get_one("SELECT * FROM pw_cnphoto WHERE pid='$pid'");
	!$photodb && Showmsg("data_error");
	
	$photodb['uploader']!=$windid && Showmsg('colony_editphoto');
	
	if(!$step) {
		$rs=$db->query("SELECT aid,aname FROM pw_cnalbum WHERE cyid='$cyid' AND (atype<>'3' OR atype='3' AND uid='$winduid')");
		$albumselect="";
		while ($albumdb=$db->fetch_array($rs)) {
			$albumselect.="<option value=\"$albumdb[aid]\">$albumdb[aname]</option>";
		}
		
		$photodb['aid'] && $albumselect=str_replace("value=\"$photodb[aid]\"","value=\"$photodb[aid]\" selected",$albumselect);
		//require PrintHack("album");footer();
	}elseif($step==2){
		!$pname && Showmsg('colony_pname_empty');
		!$aid && Showmsg('colony_albumclass'); 
		if($aid != $photodb['aid']){ //相册分类发生变动
			$rs=$db->get_one("SELECT photonum,uid,cyid,atype FROM pw_cnalbum WHERE aid='$aid' AND cyid='$cyid'");
			if(!$rs){
				Showmsg("undefined_action");
			}elseif($rs['atype']!=1){
				$winduid!=$rs['uid'] && Showmsg('colony_phototype');
			}
			$db->update("UPDATE pw_cnalbum SET photonum=photonum-1 WHERE aid='$photodb[aid]'");
			$db->update("UPDATE pw_cnalbum SET photonum=photonum+1 WHERE aid='$aid'");
		}
	
		$pname=Char_cv($pname);
		$pintro=Char_cv($pintro);
		$db->update("UPDATE pw_cnphoto SET pname='$pname',pintro='$pintro',aid='$aid' WHERE pid='$pid'");
		refreshto("$basename&cyid=$cyid&job=viewalbum&aid=$aid",'operate_success');
	}
}elseif($job=="editpost"){
	require_once(R_P.'require/postfunc.php');
	$argu = $db->get_one("SELECT tid,tpcid,gid,subject,content,author,authorid FROM pw_argument WHERE tid='$tid' AND gid='$cyid'");
	if(!$argu){
		Showmsg('data_error');
	}
	if($argu['authorid'] != $winduid && $cydb['ifadmin'] != 1 && $groupid!=3){
		Showmsg('colony_editright');
	}
	if(!$step){
		$argu['content'] = trim($argu['content']);
		require_once PrintHack('main');footer();
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
}elseif($job=="honor"){
	$rs=$db->query("SELECT honor,username,uid,ifadmin FROM pw_cmembers WHERE colonyid='$cyid' ORDER BY honor DESC LIMIT 10");
	$memberdb=array();
	while ($member = $db->fetch_array($rs)) {
		$member['ifadmin']==1 && $member['ifadmin']="ADMIN";
		$memberdb[]=$member;
	}
}elseif($job=="joinlog"){
	if($cydb['ifadmin'] != 1 && $groupid!=3){
		Showmsg('colony_adminright');
	}
	
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
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}
}elseif($job=="member"){
	if($cydb['ifadmin'] != 1 && $groupid!=3){
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
		require_once PrintHack('main');footer();
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
			$query = $db->query("SELECT c.uid,c.username,c.ifadmin,md.onlineip FROM pw_cmembers c LEFT JOIN pw_memberdata md USING(uid) WHERE c.uid IN($selids)");
			while($rt = $db->fetch_array($query)){
				$usermoney=dosql($rt['uid']);
				if($usermoney < $cydb['intomoney']){
					Showmsg('colony_passfail');
				}elseif($rt[ifadmin]=='-1'){
					$db->update("UPDATE pw_cmembers SET ifadmin=0 WHERE uid='$rt[uid]' AND colonyid='$cyid'");
					dosql($rt['uid'],'set',-$cydb[intomoney]);
					$db->update("UPDATE pw_colonys SET cmoney=cmoney+'$cydb[intomoney]' WHERE id='$cyid'");
					if($cn_moneytype=='currency'){
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
					}
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
			//$count = count($selid);
			$db->update("DELETE FROM pw_cmembers WHERE uid IN($selids) AND colonyid='$cyid'");
			$count = $db->affected_rows();
			$db->update("UPDATE pw_colonys SET members=members-'$count' WHERE id='$cyid'");
			Showmsg('colony_del');
		}
	}	
}elseif($job=="post"){
	if(!$mycydb){
		Showmsg('colony_cnmenber');
	}
	strlen($subject)>50 && Showmsg('colony_subject');
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
	if(!$mycydb['realname']){
		$mycydb['realname'] = $mycydb['username'];
	}
	if(!$subject || !$content){
		Showmsg('colony_posterror');
	}
	require_once(R_P.'require/postfunc.php');

	$subject = Char_cv($subject);
	$content = Char_cv($content);
	$content = autourl($content);
	
	$db->update("INSERT INTO pw_argument(tpcid,gid,author,authorid,postdate,lastpost,subject,content) VALUES('$tid','$cyid','$mycydb[realname]','$winduid','$timestamp','$lastpost','$subject','$content')");
	!$tid && $tid=$db->insert_id();
	refreshto("$basename&job=read&cyid=$cyid&tid=$tid",'colony_postsuccess');
}elseif($job=="quit"){
	if($cydb['admin'] == $windid){
		Showmsg('colony_quitfail');
	}else{
		if(!$mycydb){
			Showmsg('undefined_action');
		}
		$db->update("UPDATE pw_colonys SET members=members-1 WHERE id='$cyid'");
		$db->update("DELETE FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$winduid'");
		refreshto($basename,'colony_quitsuccess');
	}
}elseif($job=="read"){
	require_once(R_P.'require/bbscode.php');
	!$tid && Showmsg('data_error');
	
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_argument WHERE gid='$cyid' AND (tid='$tid' OR tpcid='$tid')");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&cyid=$cyid&job=read&tid=$tid&");
	$num=($page-1)*$db_perpage;

	$subject= '';
	$agdb	= array();
	$query = $db->query("SELECT * FROM pw_argument WHERE gid='$cyid' AND (tid='$tid' OR tpcid='$tid') ORDER BY tid $limit");
	while($rt = $db->fetch_array($query)){
		if($rt['tpcid'] == '0'){
			$subject = $rt['subject'];
		}
		$rt['content']	= str_replace("\n","<br>",$rt['content']);
		$rt['content']	= convert($rt['content'],$db_windpost);
		$rt['postdate'] = get_date($rt['postdate']);
		$agdb[] = $rt;
	}
}elseif($job=="seecard"){
	if(!$mycydb && $groupid!=3){
		Showmsg('colony_cnmenber');
	}

	$rt = $db->get_one("SELECT * FROM pw_cmembers WHERE colonyid='$cyid' AND uid='$uid'");
	if(!$rt){
		Showmsg('colony_noseecard');
	}
	!$rt['realname'] && $rt['realname'] = $rt['username'];
}elseif($job=="set"){
	if($cydb['ifadmin'] != 1 && $groupid!=3){
		Showmsg('colony_adminright');
	}
	if(!$step){
		if($cydb['ifcheck']=='1'){
			$ifcheck_1='checked';
			$ifcheck_0='';
		}else{
			$ifcheck_0='checked';
			$ifcheck_1='';
		}
		if($cydb['ifopen']=='1'){
			$ifopen_1='checked';
			$ifopen_0='';
		}else{
			$ifopen_0='checked';
			$ifopen_1='';
		}
		if($cydb['albumopen']=='1'){
			$albumopen_1='checked';
			$albumopen_0='';
		}else{
			$albumopen_1='';
			$albumopen_0='checked';		
		}
		if($cn_joinmoney && $cydb['intomoney'] < $cn_joinmoney){
			$cydb['intomoney'] = $cn_joinmoney;
		}
		$classdb = array();
		$query = $db->query("SELECT * FROM pw_cnclass");
		while ($rt = $db->fetch_array($query)){
			$classdb[] = $rt;
		}
		//require_once PrintHack('main');footer();
	}elseif($step == '2'){
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
		if(!$descrip || strlen($descrip) > 255){
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
		}
		if($attachment){
			if(!$attachment || $attachment== 'none'){
				Showmsg('colony_uploadfail');
			} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($attachment) && !is_uploaded_file(str_replace('\\\\', '\\', $attachment))){
				Showmsg('colony_uploadfail');
			} elseif(!($attachment && $attachment['error']!=4)){
				Showmsg('colony_uploadfail');
			}
			$attach_ext = substr(strrchr($attachment_name,'.'),1);
			$img_ext=array("jpg","jpeg","png","gif","bmp");
			!in_array($attach_ext,$img_ext) && Showmsg('colony_uploadfail');
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
		if(function_exists('getimagesize') && $cnimg && !(list($width,$height)=getimagesize($filename))){
			P_unlink($filename);
			$db->update("UPDATE pw_colonys SET cnimg='' WHERE id='$cyid'");
			Showmsg('pro_loadimg_error');
		}
		if($cn_imgwidth && $cn_imgheight && ($width > $cn_imgwidth || $height > $cn_imgheight)){
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
		$db->update("UPDATE pw_colonys SET cname='$cname',classid='$classid',ifcheck='$ifcheck',albumopen='$albumopen' $cnimg,intomoney='$intomoney',annouce='$annouce',ifopen='$ifopen',descrip='$descrip' WHERE id='$cyid'");
		refreshto("$basename&cyid=$cyid&job=view&id=$cyid",'colony_setsuccess');
	}
}elseif($job=="ulphoto"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	!$mycydb && Showmsg('colony_cnmenber'); //非本群成员
	
	if(!$step){
		$rs=$db->query("SELECT aid,aname FROM pw_cnalbum WHERE cyid='$cyid' AND atype<>'3'");
		$albumselect="";
		while ($albumdb=$db->fetch_array($rs)) {
			$albumselect.="<option value=\"$albumdb[aid]\">$albumdb[aname]</option>";
		}
		$aid && $albumselect=str_replace("value=\"$aid\"","value=\"$aid\" selected",$albumselect);
		//require PrintHack("album");footer();
	}elseif($step==2){
		!$aid && Showmsg('colony_albumclass'); 
		$rs=$db->get_one("SELECT photonum,uid,cyid,atype FROM pw_cnalbum WHERE aid='$aid' AND cyid='$cyid'");
		if(!$rs){
			Showmsg("undefined_action");
		}elseif($rs['atype']!=1){
			$winduid!=$rs['uid'] && Showmsg('colony_phototype');
		}
		$rs['photonum'] >= $cn_maxphotonum && Showmsg('colony_photofull');
		$photo=$_FILES['photo'];
		if(is_array($photo)){
			$upload_name=$photo['name'];
			$upload_size=$photo['size'];
			$upload=$photo['tmp_name'];
		}
		require_once(R_P."require/postfunc.php");
		if(if_uploaded_file($upload)){
			$attach_ext = strtolower(substr(strrchr($upload_name,'.'),1));
			$pic_ext=array("jpg",'gif','png');
			!in_array($attach_ext,$pic_ext) && Showmsg('colony_filetype');
			$upload_size>$cn_maxfilesize && Showmsg('colony_filesize');
			$filename=randstr(4).$timestamp.".".$attach_ext;
			$smallfilename="s_".$filename;
	
			switch ($cn_mkdir){
				case 1: $savedir="/Mon_".date('ym');break;
				case 2: $savedir="/Day_".date('ymd');break;
				case 3: $savedir="/Cyid_".$cyid;break;
				default:$savedir="/Mon_".date('ym');break;
			}
			$savedir=$photodir.$savedir;
			if(!is_dir("$attachdir/$savedir") && $db_ifftp=='0') {
				@mkdir("$attachdir/$savedir");
				@chmod("$attachdir/$savedir", 0777);
				@fclose(@fopen("$attachdir/$savedir".'/index.html', 'w'));
				@chmod("$attachdir/$savedir".'/index.html', 0777);
			}
			$fileuplodeurl	=	$savedir.'/'.$filename;
			$smalluplodeurl	=	$attachdir.'/'.$savedir.'/'.$smallfilename;
			$source = $db_ifftp ? $db_ftpweb."/".$fileuplodeurl : $attachdir.'/'.$fileuplodeurl;
			
			if($db_ifftp){
				require_once(R_P."require/ftp.php");
				resize_image($upload,$smalluplodeurl,$small_width,$small_height);
				if(!$ftpsize=$ftp->upload($upload,$fileuplodeurl)){
					Showmsg('upload_error');
				}
			}elseif(!postupload($upload,$source) || !resize_image($source,$smalluplodeurl,$small_width,$small_height)){
				Showmsg('upload_error');
			}
		}else {
			Showmsg("undefined_action");
		}
		!$pname && Showmsg('colony_pname_empty');
		!$photo && Showmsg('colony_uploadnull');
		$pname=Char_cv($pname);
		$pintro=Char_cv($pintro);
		$db->update("INSERT INTO pw_cnphoto(aid,pname,pintro,path,uploader,uptime) VALUES('$aid','$pname','$pintro','$fileuplodeurl','$windid','$timestamp')");
		$db->update("UPDATE pw_cnalbum SET lastphoto='$fileuplodeurl',photonum=photonum+1 WHERE aid='$aid'");
		refreshto("$basename&cyid=$cyid&aid=$aid&job=viewalbum",'operate_success');	
	}
}elseif($job=="update"){
	$cydb['ifadmin']!=1 && Showmsg('colony_ifadmin');

	if(!$step){
		$cydb['level']>0 && Showmsg('colony_update');
	}elseif ($step==2){
		$cydb[cmoney] < $cn_updatemoney && Showmsg('colony_updatemoney');
		$db->update("UPDATE pw_colonys SET level=1,cmoney=cmoney-'$cn_updatemoney' WHERE id='$cyid'");
		refreshto("$basename&job=view&cyid=$cyid",'operate_success');
	}
}elseif($job=="viewalbum"){
	!$cn_phopen && Showmsg('colony_phopen');
	
	$albumdb=array();
	$albumdb=$db->get_one("SELECT * FROM pw_cnalbum WHERE aid='$aid' AND cyid='$cyid'");
	!$albumdb && Showmsg("data_error"); 
	
	if($albumdb['atype']==1){//为群相册
		!$mycydb && !$cydb['albumopen'] && $groupid!=3 && Showmsg('colony_opentocn');
	}elseif($albumdb['atype']==3){
		$winduid!=$albumdb['uid'] && $groupid!=3 && Showmsg('colony_opentome');
	}
	$albumdb['crtime']=get_date($albumdb['crtime'],"y.m.d");
	
	$db_perpage=20;
	
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$pages = numofpage($albumdb['photonum'],$page,ceil($albumdb['photonum']/$db_perpage),"$basename&cyid=$cyid&job=viewalbum&aid=$aid&");
	
	$rs=$db->query("SELECT * FROM pw_cnphoto WHERE  aid='$aid' ORDER BY uptime DESC $limit");
	$photos=array();
	while ($photodb=$db->fetch_array($rs)) {
		$photodb['pname']=substrs($photodb['pname'],15);
		$photodb['path']=getsmallurl($photodb['path']);
		$photodb['uptime']=get_date($photodb['uptime'],"y.m.d");
		$photos[]=$photodb;
	}
} else{
	Showmsg("undefined_action");
}

require PrintHack("main");footer();
?>