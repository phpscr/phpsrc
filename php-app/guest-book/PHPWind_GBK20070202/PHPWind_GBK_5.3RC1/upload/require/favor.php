<?php
!function_exists('readover') && exit('Forbidden');

if(!$job){
	include_once(D_P.'data/bbscache/forum_cache.php');
	require_once(R_P.'require/forum.php');
	$imgtype=$styletype=array();
	list($imgtype['tid'],$styletype['tid'])=GetDeploy('tid');
	$favordb=array();
	$favor=$db->get_one("SELECT tids,type FROM pw_favors WHERE uid='$winddb[uid]'");
	Add_S($favor);
	$favor['tids'] && $tiddb=getfavor($favor['tids']);
	if($favor['type']){
		$typeid=explode(',',$favor['type']);
	}
	$tids='';
	!isset($type) && $type='all';
	if($favor['tids'] && $type=='all'){
		$tid_a = explode('|',$favor['tids']);
		foreach($tid_a as $key=>$val){
			if($val){
				$tids .= $tids ? ','.$val : $val;
			}
		}
	}elseif($favor['tids']){
		$tid_a=explode('|',$favor['tids']);
		$tids=$tid_a[$type];
	}
	if($tids){
		$tidarray=array();
		$query=$db->query("SELECT fid,tid,subject,postdate,author,authorid,replies,hits FROM pw_threads WHERE tid IN($tids) ORDER BY postdate DESC");
		while($rt=$db->fetch_array($query)){
			$rt['subject'] = substrs($rt['subject'],50);
			$rt['postdate']=get_date($rt['postdate']);
			$keyvalue=get_key($rt['tid'],$tiddb);
			$type=='all' && $tidarray[$keyvalue][$rt['tid']]=$rt['tid'];
			$rt['forum']=$forum[$rt['fid']]['name'];
			$rt['sel']=$typeid[$keyvalue-1];
			$favordb[]=$rt;
		}
		if($tidarray && $type=='all'){
			$newtids=makefavor($tidarray);
			$newtids!=$favor['tids'] && $db->update("UPDATE pw_favors SET tids='$newtids' WHERE uid='$winduid'");
		}
	}
	require_once PrintEot('profile');footer();
}elseif($job=='add'){
	!is_numeric($tid) && Showmsg('illegal_tid');

	$rs=$db->get_one("SELECT uid,tids FROM pw_favors WHERE uid='$winddb[uid]'");
	if($rs['uid']){
		$count=0;
		$tiddb=getfavor($rs['tids']);
		foreach($tiddb as $key=>$t){
			if(is_array($t))$count+=count($t);
		}
		if($count>$_G['maxfavor']){
			Showmsg('job_favor_full');
		}

		foreach($tiddb as $key=>$t){
			if(in_array($tid,$t)){
				Showmsg('tid_favor_error');
			}
		}
		$tiddb[0][]=$tid;
		$newtids=makefavor($tiddb);
		$db->update("UPDATE pw_favors SET tids='$newtids' WHERE uid='$winddb[uid]'");
	}else{
		$db->update("INSERT INTO pw_favors(uid,tids) VALUES('$winddb[uid]','$tid')");
	}
	refreshto("profile.php?action=favor",'operate_success');
}elseif($job=='clear'){
	!$delid && Showmsg('sel_error');

	$rs=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
	if($rs){
		$tiddb=getfavor($rs['tids']);
		foreach($delid as $key=>$tid){
			foreach($tiddb as $k=>$v){
				if(in_array($tid,$v)){
					unset($tiddb[$k][$tid]);
				}
			}
		}
		foreach($tiddb as $key=>$val){
			if(empty($val)){
				unset($tiddb[$key]);
			}
		}
		$newtids=makefavor($tiddb);
		$db->update("UPDATE pw_favors SET tids='$newtids' WHERE uid='$winddb[uid]'");
		refreshto("profile.php?action=favor",'operate_success');
	}else{
		Showmsg('job_favor_del');
	}
}elseif($job=='change'){
	!$delid && Showmsg('sel_error');

	$rs=$db->get_one("SELECT tids FROM pw_favors WHERE uid='$winddb[uid]'");
	if($rs){
		$tiddb=getfavor($rs['tids']);
		foreach($delid as $key=>$tid){
			if(!is_numeric($tid))continue; 
			foreach($tiddb as $k=>$v){
				if(in_array($tid,$v)){
					unset($tiddb[$k][$tid]);
				}
			}
			$tiddb[$type][$tid]=$tid;
		}
		foreach($tiddb as $key=>$val){
			if(empty($val)){
				unset($tiddb[$key]);
			}
		}
		$newtids=makefavor($tiddb);
		$db->update("UPDATE pw_favors SET tids='$newtids' WHERE uid='$winduid'");
	}
	refreshto("profile.php?action=favor",'operate_success');
}elseif($job=='addtype'){
	(!$type || strlen($type)>20) && Showmsg('favor_cate_error');
	strpos($type,',')!==false && Showmsg('favor_cate_limit');
	$favor=$db->get_one("SELECT type FROM pw_favors WHERE uid='$winduid'");
	$newtype = $favor['type'];
	$newtype .= $newtype ? ",".stripslashes($type) : stripslashes($type);
	$newtype = addslashes(Char_cv($newtype));
	if($favor){
		$db->update("UPDATE pw_favors SET type='$newtype' WHERE uid='$winduid'");
	}else{
		$db->update("INSERT INTO pw_favors(uid,type) VALUES('$winduid','$newtype')");
	}
	refreshto("profile.php?action=favor",'operate_success');
}elseif($job='deltype'){
	(!is_numeric($type) || $type<1) && Showmsg('type_error');
	$tnum=$type-1;
		
	$rs=$db->get_one("SELECT tids,type FROM pw_favors WHERE uid='$winddb[uid]'");
	$tiddb=getfavor($rs['tids']);
	$typedb=explode(',',$rs['type']);
	Add_S($typedb);
	unset($typedb[$tnum]);
	if($tiddb[$type]){
		foreach($tiddb[$type] as $key=>$val){
			$tiddb['0'][$val]=$val;
		}
	}
	unset($tiddb[$type]);
	$newtids=makefavor($tiddb);
	$newtype=Char_cv(implode(',',$typedb));
	$db->update("UPDATE pw_favors SET tids='$newtids',type='$newtype' WHERE uid='$winduid'");
	refreshto("profile.php?action=favor",'operate_success');
}
function getfavor($tids){
	$tids=explode('|',$tids);
	$tiddb=array();
	foreach($tids as $key=>$t){
		if($t){
			$v=explode(',',$t);
			foreach($v as $k=>$v1){
				$tiddb[$key][$v1]=$v1;
			}
		}
	}
	return $tiddb;
}
function makefavor($tiddb){
	$newtids=$ex='';
	$k=0;
	ksort($tiddb);
	foreach($tiddb as $key=>$val){
		$new_tids='';
		rsort($val);
		if($key!=$k){
			$s=$key-$k;
			for($i=0;$i<$s;$i++){
				$newtids .='|';
			}
		}
		foreach($val as $k=>$v){
			is_numeric($v) && $new_tids .= $new_tids ? ','.$v : $v;
		}
		//$new_tids =implode(",",$val);
		$newtids .= $ex.$new_tids;
		$k=$key+1;
		$ex='|';
	}
	return $newtids;
}
function get_key($tid,$tiddb){
	foreach($tiddb as $key=>$value){
		if(in_array($tid,$value)){
			return $key;
		}
	}
}
function GetDeploy($name){
	global $_COOKIE;
	if(strpos($_COOKIE['deploy'],"\t".$name."\t")===false){
		$type='fold';
	}else{
		$type='open';
		$style='display:none;';
	}
	return array($type,$style);
}
?>