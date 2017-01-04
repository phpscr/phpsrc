<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=report";
include_once(R_P.'require/forum.php');

if ($admin_gid == 5){
	list($allowfid,) = GetAllowForum($admin_name);
	$sql = "fid IN($allowfid)";
} else {
	if($admin_gid == 3){
		$sql = '1';
	} else{
		list($hidefid,) = GetHiddenForum();
		$sql = "fid NOT IN($hidefid)";
	}
}

if(empty($_POST['action'])){
	$type=$type==1 ? 1 : 0;
	(!is_numeric($page) || $page < 1) && $page=1;
	$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$sql .= " AND r.type='$type'";

	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_report r LEFT JOIN pw_threads t ON t.tid=r.tid WHERE $sql");
	$sum=$rt['count'];
	$numofpage=ceil($sum/$db_perpage);
	$pages=numofpage($sum,$page,$numofpage,"$basename&type=$type&");

	$query=$db->query("SELECT r.*,m.username,t.fid FROM pw_report r LEFT JOIN pw_members m ON m.uid=r.uid LEFT JOIN pw_threads t ON t.tid=r.tid WHERE $sql ORDER BY id $limit");
	while($rt=$db->fetch_array($query)){
		$rt['fname']=$forum[$rt['fid']]['name'];
		$reportdb[]=$rt;
	}
	include PrintEot('report');exit;
} elseif($_POST['action']=='del'){
	$delids='';
	foreach($_POST['selid'] as $value){
		is_numeric($value) && $delids.=$value.',';
	}
	if($delids){
		$delids=substr($delids,0,-1);
		$db->update("DELETE FROM pw_report WHERE id IN ($delids)");
	}
	adminmsg('operate_success');
}
?>