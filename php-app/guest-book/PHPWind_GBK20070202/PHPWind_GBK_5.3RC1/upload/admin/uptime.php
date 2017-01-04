<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=uptime";

if(!$action){
	require_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";

	$query=$db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE (gptype='system' OR gptype='special') AND gid NOT IN(6,7)");
	$grouplist='<option></option>';
	while($rt=$db->fetch_array($query)){
		$grouplist.="<option value=\"$rt[gid]\">$rt[grouptitle]</option>";
	}

	$sql="";
	$pageurl=$basename;
	if($gid){
		$sql .="WHERE e.gid='$gid'";
		$pageurl.="&gid=$gid";
		$grouplist=str_replace("<option value=\"$gid\">","<option value=\"$gid\" selected>",$grouplist);
	}
	if($username){
		$sql .= $sql ? " AND m.username='$username'" : "WHERE m.username='$username'";
		$pages = '';
	}else{
		extract($db->get_one("SELECT COUNT(*) AS count FROM pw_extragroups e $sql"));
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"$pageurl&");
	}

	$memberdb=array();
	$updatecache_fd=0;
	$query=$db->query("SELECT e.*,m.username,m.groupid,groups FROM pw_extragroups e LEFT JOIN pw_members m USING(uid) $sql ORDER BY groupid,gid $limit");
	while($rt=$db->fetch_array($query)){
		if($timestamp>$rt['startdate']+$rt['days']*86400){
			if($rt['gid']==$rt['groupid']){
				$newgid=($rt['togid'] && strpos($rt['groups'],",$rt[togid],")!==false) ? $rt['togid'] : '-1';
				$newgroups=str_replace(','.$newgid.',',',',$rt['groups']);
			}else{
				$newgid=$rt['groupid'];
				$newgroups=str_replace(','.$rt['gid'].',',',',$rt['groups']);
			}
			if($rt['gid']=='5'){
				$query1=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE forumadmin!=''");
				while($forum=$db->fetch_array($query1)){
					if($forum['forumadmin'] && strpos($forum['forumadmin'],",$rt[username],")!==false){
						$newadmin = str_replace(",$rt[username],",',',$forum['forumadmin']);
						$newadmin == ',' && $newadmin = '';
						$db->update("UPDATE pw_forums SET forumadmin='$newadmin' WHERE fid='$forum[fid]'");
					}
				}
				$updatecache_fd=1;
			}
			$newgroups==',' && $newgroups='';
			$db->update("UPDATE pw_members SET groupid='$newgid',groups='$newgroups' WHERE uid='$rt[uid]'");
			$db->update("DELETE FROM pw_extragroups WHERE uid='$rt[uid]' AND gid='$rt[gid]'");
			continue;
		}
		if($rt['gid']!=$rt['groupid'] && strpos($rt['groups'],",".$rt['gid'].",")===false){
			$db->update("DELETE FROM pw_extragroups WHERE uid='$rt[uid]' AND gid='$rt[gid]'");
			continue;
		}
		$rt['startdate']=get_date($rt['startdate']);
		$rt['slevel']=$ltitle[$rt['gid']];
		$rt['tolevel']=$ltitle[$rt['togid']];
		$memberdb[]=$rt;
	}
	$updatecache_fd && updatecache_fd();

	include PrintEot('uptime');exit;
}elseif($action=='setlevel'){
	if(!$step){
		include PrintEot('uptime');exit;
	}elseif($_POST['step']==1){
		PostCheck($verify);
		!$username && adminmsg('operate_error');
		$rt=$db->get_one("SELECT uid,username,groupid,groups FROM pw_members WHERE username='$username'");
		if(!$rt){
			$errorname=$username;
			adminmsg('user_not_exists');
		}elseif($rt['username']==$manager && !If_manager){
			adminmsg('manager_right');
		}elseif($rt['groupid']==3 && !If_manager){
			adminmsg('manager_right');
		}elseif(!$rt['groups'] && $rt['groupid']=='-1'){
			adminmsg('groups_empty');
		}
		$groupstitle="<option value=\"$rt[groupid]\">".$ltitle[$rt['groupid']]."</option>";
		if($rt['groups']){
			$groups=explode(',',$rt['groups']);
			foreach($groups as $key=>$gid){
				$gid>2 && $groupstitle .="<option value=\"$gid\">$ltitle[$gid]</option>";
			}
		}
		include PrintEot('uptime');exit;
	}elseif($_POST['step']==2){
		PostCheck($verify);
		(!$uid || !$gid) && adminmsg("operate_error");
		$gid==3 && !If_manager && adminmsg('manager_right');
		$gid==$togid && adminmsg('gid_same');
		$rt=$db->get_one("SELECT * FROM pw_extragroups WHERE uid='$uid' AND gid='$gid'");
		$rt && adminmsg('uptime_has');
		$days =(int) $days;
		$days<1 && $days=30;
		$db->update("INSERT INTO pw_extragroups (uid,gid,togid,startdate,days) VALUES ('$uid','$gid','$togid','$timestamp','$days')");
		adminmsg('operate_success');
	}
}elseif($action=='edit'){
	if(!$step){
		$men=$db->get_one("SELECT e.*,m.username,m.groupid,m.groups FROM pw_extragroups e LEFT JOIN pw_members m USING(uid) WHERE e.uid='$uid' AND e.gid='$gid'");
		!$men && adminmsg('operate_error');
		$groupstitle="<option value=\"$men[groupid]\">".$ltitle[$men['groupid']]."</option>";
		if($men['groups']){
			$groups=explode(',',$men['groups']);
			foreach($groups as $key=>$val){
				$val>2 && $groupstitle .="<option value=\"$val\">$ltitle[$val]</option>";
			}
		}
		$grouplist = str_replace("<option value=\"$gid\">","<option value=\"$gid\" selected>",$groupstitle);
		$togrouplist=str_replace("<option value=\"$men[togid]\">","<option value=\"$men[togid]\" selected>",$groupstitle);
		include PrintEot('uptime');exit;
	}elseif($_POST['step']==3){
		PostCheck($verify);
		$gid==3 && !If_manager && adminmsg('manager_right');
		$gid==$togid && adminmsg('gid_same');
		$rt=$db->get_one("SELECT * FROM pw_extragroups WHERE uid='$uid' AND gid='$gid'");
		$days =(int) $days;
		$days<1 && $days=30;
		if($rt){
			$sql = $treset ? ",startdate='$timestamp'" : '';
			$db->update("UPDATE pw_extragroups SET days='$days',togid='$togid' $sql WHERE uid='$uid' AND gid='$gid'");
		}else{
			$db->update("INSERT INTO pw_extragroups (uid,gid,togid,startdate,days) VALUES ('$uid','$gid','$togid','$timestamp','$days')");
		}
		adminmsg('operate_success');
	}
}elseif($_POST['action']=='del'){
	PostCheck($verify);
	(!$selid || !is_array($selid)) && adminmsg('operate_error');
	foreach($selid as $gid=>$value){
		if($uids=checkselid($value)){
			$db->update("DELETE FROM pw_extragroups WHERE gid='$gid' AND uid IN($uids)");
		}
	}
	adminmsg('operate_success');
}
?>