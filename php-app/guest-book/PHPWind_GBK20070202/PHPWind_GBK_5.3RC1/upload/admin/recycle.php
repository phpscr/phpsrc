<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=recycle";

include_once(R_P.'require/forum.php');
require_once(R_P.'require/updateforum.php');

if ($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = "WHERE r.fid IN($allowfid)";
} else {
	include(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sql = "WHERE 1";
	} else{
		$sql = "WHERE r.fid NOT IN($hidefid)";
	}
}

if(!$action){
	is_numeric($fid) && $sql .= " AND r.fid='$fid'";
	$admin && $sql .= " AND r.admin='$admin'";
	if($username){
		$rt  = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$uid = $rt['uid'];
	}
	if(is_numeric($uid)){
		$sql  .= " AND t.authorid='$uid'";
		$J_sql ="LEFT JOIN pw_threads t USING(tid)";
	}else{
		$J_sql = '';
	}

	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r $J_sql $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&fid=$fid&uid=$uid&admin=$admin&");

	$query=$db->query("SELECT r.*,t.subject,t.author,t.authorid FROM pw_recycle r LEFT JOIN pw_threads t USING(tid) $sql ORDER BY fid,deltime DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['deltime'] = get_date($rt['deltime']);
		$rt['subject'] = substrs($rt['subject'],50);
		$rt['fname']   = $forum[$rt['fid']]['name'];
		$recycledb[]   = $rt;
	}
	include PrintEot('recycle');exit;
} elseif($_POST['action'] == 'revert' || $_POST['action'] == 'del'){
	$selids = '';
	$fids  = array();
	foreach($selid as $key => $value){
		if(is_numeric($value)){
			$selids .= $key.',';
			if($action == 'revert'){
				$fids[]=$value;
				$db->update("UPDATE pw_threads SET fid='$value' WHERE tid='$key'");
			}
		}
	}
	if($action == 'revert'){
		$fids = array_unique($fids);
		foreach($fids as $key => $value){
			updateforum($value);
		}
		updateforum($db_recycle);
	}
	if($selids){
		$selids = substr($selids,0,-1);
		$db->update("DELETE FROM pw_recycle WHERE tid IN ($selids)");
	}
	adminmsg('operate_success');
}
?>