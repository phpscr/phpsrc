<?php
!function_exists("cn_credit") && die("Forbidden");

if(!$job){
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	
	$mycyids = array();
	$mycy=$db->query("select colonyid from pw_cmembers where uid='$winduid'");
	if($mycy){
		while($my=$db->fetch_array($mycy)){
			$mycyids[]=$my['colonyid'];
		}
	}
	
	$sql='';
	if($classid){
		!is_numeric($classid) && Showmsg('data_error');
		$sql="WHERE classid='$classid'";
	}
	if ($action=="my"){
		$and=$sql ? "AND" : "WHERE";
		if($mycyids){
			$ids=implode(",",$mycyids);
		}else {
			$ids=0;
		}
		empty($ids) && Showmsg('colony_unjoin');
		$sql.=" $and id IN($ids)";
	}elseif ($action=="search") {
		$and=$sql ? "AND" : "WHERE";
		if($searchtype=="num"){
			$sql.=" $and cid='$keyword'";
		}elseif($searchtype=="name"){
			$sql.=" $and cname LIKE('%$keyword%')";
		}else {
			showmsg("undefine_action");
		}
	}elseif ($action=="moneytop"){
		$order="ORDER BY cmoney DESC";
		$limit="LIMIT 10";	
	}elseif ($action=="membertop"){
		$order="ORDER BY members DESC";
		$limit="LIMIT 10";
	}
	
	if($action!="moneytop" && $action!="membertop"){
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_colonys $sql");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");
		$order = "ORDER BY level DESC,createtime DESC";
	}
	
	$colonydb = array();
	$rs = $db->query("SELECT * FROM pw_colonys $sql $order $limit");
	while($cn = $db->fetch_array($rs)){
		$cn['createtime'] = get_date($cn['createtime'],"Y-m-d");
		if(in_array($cn['id'],$mycyids)){
			$cn['stat']="<a href='$basename&cyid=$cn[id]&job=quit'>QUIT</a>";
		}elseif($cn['ifcheck']){
			$cn['stat']="<a href='$basename&cyid=$cn[id]&job=join'>JOIN</a>";
		}else{
			$cn['stat']="CLOSE";
		}
		$colonydb[] = $cn;
	}
}elseif ($job=="creat"){
	!$cn_newcolony && Showmsg('colony_reglimit');
	if (strpos($cn_groups,",$groupid,") === false){
		Showmsg('colony_groupright');
	}
	$usermoney=dosql($winduid);
	$usermoney < $cn_createmoney && Showmsg('colony_creatfailed');
	if (!$step){
		$query=$db->query("SELECT * FROM pw_cnclass");
		$classdb=array();
		while ($rt=$db->fetch_array($query)){
			$classdb[]=$rt;
		}
		//require_once PrintHack('default');footer();
	}elseif($step=='2'){
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_colonys WHERE admin='$windid'");
		if ($cn_allowcreate && $rt['sum'] >= $cn_allowcreate){
			Showmsg('colony_numlimit');
		}
		!$cn_class && Showmsg('colony_class');
		!$cnname && Showmsg('colony_emptyname');
		!$descrip && Showmsg('colony_descrip');
		$rt = $db->get_one("SELECT id FROM pw_colonys WHERE cname='$cnname'");
		if ($rt['id']){
			Showmsg('colony_samename');
		}
		$cnname = Char_cv($cnname);
		$descrip = Char_cv($descrip);
		dosql($winduid,"set",-$cn_createmoney);
		$db->update("INSERT INTO pw_colonys(cname,classid,admin,members,ifcheck,cmoney,createtime,intomoney,descrip) VALUES('$cnname','$cn_class','".addslashes($windid)."','1','1','$cn_createmoney','$timestamp','$cn_joinmoney','$descrip')");
		$cid = $db->insert_id();
		$db->update("INSERT INTO pw_cmembers(uid,username,ifadmin,colonyid) VALUES('$winduid','".addslashes($windid)."','1','$cid')");
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
}elseif ($job=="join"){
	if ($mycydb) Showmsg('colony_alreadyjoin');
	
	//$rt=$db->get_one("SELECT ifadmin FROM pw_cmembers WHERE uid='$winduid'");
	
	$rs=$db->get_one("SELECT count(*) as sum FROM pw_cmembers WHERE uid='$winduid'");
	if($cn_allowjoin && $rs['sum'] >=  $cn_allowjoin){
		Showmsg('colony_joinlimit');
	}
	if($cn_memberfull && $cydb['members'] >= $cn_memberfull){
		Showmsg('colony_memberlimit');
	}
	
	!$cydb['ifcheck'] && Showmsg('colony_joinrefuse');
	$cydb['intomoney'] = (int)$cydb['intomoney'];
	$cydb['intomoney'] < 0 && Showmsg('numerics_checkfailed');
	if($cn_joinmoney && !$cydb['intomoney'])$cydb['intomoney'] = $cn_joinmoney;		
	
	$usermoney=dosql($winduid);
	if($cydb['intomoney'] && $usermoney < $cydb['intomoney']){
		Showmsg('colony_joinfail');
	}
	
	if(!$step){
		//require_once PrintHack('default');footer();
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
		$rt = $db->get_one("SELECT id FROM pw_cmembers WHERE realname='$realname' AND colonyid='$cyid'");
		if($rt['id']){
			Showmsg('colony_samerealname');
		}
		$db->update("INSERT INTO pw_cmembers SET uid='$winduid',username='".addslashes($windid)."', realname='$realname',ifadmin='-1',gender='$gender',tel='$tel',email='$email',introduce='$introduce',colonyid='$cyid'");
		$db->update("UPDATE pw_colonys SET members=members+1 WHERE id='$cyid'");
		refreshto("$basename&cyid=$cyid",'colony_joinsuccess');
	}
}
require_once PrintHack('default');footer();
?>