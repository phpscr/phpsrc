<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=viewban";
if (empty($action)){
	include_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$sql = "WHERE 1";
	$url = $basename;
	$count = 0;
	if($banuser){
		$sql .= " AND m.username='$banuser'";
		$count = 1;
	}
	if($bantype){
		$sql .= " AND b.type='$bantype'";
		$url .= "&bantype=$bantype";
	}
	if($adminban){
		$sql .= " AND b.admin='$adminban'";
		$url .= "&adminban=".rawurlencode($adminban);
	}
	if($starttime){
		!is_numeric($starttime) && $starttime = PwStrtoTime($starttime);
		$sql .= " AND b.startdate>'$starttime'";
		$url .= "&starttime=$starttime";
	}
	if($endtime){
		!is_numeric($endtime) && $endtime = PwStrtoTime($endtime);
		$sql .= " AND b.startdate<'$endtime'";
		$url .= "&endtime=$endtime";
	}
	if($count<1){
		extract($db->get_one("SELECT COUNT(*) AS count FROM pw_banuser b $sql"));
	}
	$pages = numofpage($rt['sum'],$page,ceil($count/$db_perpage),"$url&");

	$bandb=array();
	$query=$db->query("SELECT b.*, m.username FROM pw_banuser b LEFT JOIN pw_members m ON b.uid=m.uid $sql $limit");
	while($rt=$db->fetch_array($query)){
		if($rt['type']==1 && $timestamp-$rt['startdate']>$rt['days']*86400){
			$db->update("DELETE FROM pw_banuser WHERE uid='$rt[uid]'");
			$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$rt[uid]'");
		} else{
			$rt['startdate'] && $rt['date']=get_date($rt['startdate']);
			$bandb[]=$rt;
		}
	}
	include PrintEot('viewban');exit;
} elseif($_POST['action']=='freeban'){
	!$free && adminmsg('operate_error');
	$uids="'".implode("','",$free)."'";
	$db->update("UPDATE pw_members SET groupid='-1' WHERE uid IN($uids)");
	$db->update("DELETE FROM pw_banuser WHERE uid IN($uids)");
	adminmsg('operate_success');
}