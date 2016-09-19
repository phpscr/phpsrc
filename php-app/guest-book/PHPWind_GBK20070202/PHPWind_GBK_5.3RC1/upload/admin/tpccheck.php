<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=tpccheck";
include_once(D_P.'data/bbscache/forumcache.php');
include_once(R_P.'require/forum.php');

if ($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = "fid IN($allowfid)";
} else {
	include(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sql = '1';
	} else{
		$sql = "fid NOT IN($hidefid)";
	}
}

if(!$_POST['step']){
	$sql .= " AND ifcheck='0'";
	is_numeric($fid) && $sql .= " AND fid='$fid'";
	if($username){
		$rt  = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$uid = $rt['uid'];
	}
	is_numeric($uid) && $sql .= "AND authorid='$uid'";
	$sql .= " ORDER BY postdate DESC";

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_threads WHERE $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&fid=$fid&uid=$uid&");

	$checkdb=array();
	$query = $db->query("SELECT tid,fid,subject,author,authorid,postdate FROM pw_threads WHERE $sql $limit");
	while($rt = $db->fetch_array($query)){
		$rt['subject']  = substrs($rt['subject'],35);
		$rt['name']     = $forum[$rt['fid']]['name'];
		$rt['postdate'] = get_date($rt['postdate']);
		$checkdb[]      = $rt;
	}
	include PrintEot('tpccheck');exit;
} elseif($_POST['step']==2){
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');	
	}
	if($type == 'pass'){
		$fids  = array();
		$query = $db->query("SELECT fid FROM pw_threads WHERE $sql AND tid IN($selid)");
		while($rt=$db->fetch_array($query)){
			$fids[$rt['fid']] ++;
		}
		foreach($fids as $key => $value){
			$rt = $db->get_one("SELECT tid,author,postdate,subject FROM pw_threads WHERE fid='$key' ORDER BY postdate DESC LIMIT 1");
			$lastpost = $rt['subject']."\t".$rt['author']."\t".$rt['postdate']."\t"."read.php?tid=$rt[tid]&page=e#a";
			$db->update("UPDATE pw_forumdata SET topic=topic+'$value',article=article+'$value',tpost=tpost+'$value',lastpost='$lastpost' WHERE fid='$key'");
		}
		$db->update("UPDATE pw_threads SET ifcheck='1' WHERE $sql AND tid IN($selid)");
	} else{
		$db->update("DELETE FROM pw_threads WHERE $sql AND tid IN($selid)");
	}
	adminmsg('operate_success');
}
?>