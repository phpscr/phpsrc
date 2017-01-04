<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=orderlist";

if (!$action){
	if($state == 1){
		$sqladd = "WHERE state=0 OR state=1";
	} elseif($state == 2){
		$sqladd = "WHERE state=2";
	} else {
		$sqladd = '';
	}

	include_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_clientorder $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&state=$state&");

	$orderdb=array();
	$query = $db->query("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) $sqladd ORDER BY date DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['date']   = get_date($rt['date']);
		$orderdb[] = $rt;
	}
	include PrintEot('orderlist');exit;
}elseif($_POST['action'] == 'del'){
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');	
	}
	$db->update("DELETE FROM pw_clientorder WHERE id IN($selid)");
	adminmsg('operate_success');
}
?>