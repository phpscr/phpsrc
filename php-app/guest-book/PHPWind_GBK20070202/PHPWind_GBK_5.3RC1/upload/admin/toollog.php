<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=toollog";

require_once(R_P."require/forum.php");
require_once(R_P.'require/bbscode.php');

if(!$action || $action == 'search'){
	if($action == 'search' && $keyword){
		$sqladd = "WHERE  descrip LIKE '%$keyword%'";
	} else{
		$sqladd = '';
	}

	if (!is_numeric($page) || $page<1){
		$page = 1;
	}
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_toollog $sqladd");
	$sum   = $rt['sum'];
	$total = ceil($sum/$db_perpage);
	$pages = numofpage($sum,$page,$total,"$basename&action=search&keyword=".rawurlencode($keyword)."&");

	$logdb=array();
	$query = $db->query("SELECT * FROM pw_toollog $sqladd ORDER BY time DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['time']   = get_date($rt['time']);
		$rt['descrip']= convert($rt['descrip'],array());
		$logdb[]      = $rt;
	}
	include PrintEot('toollog');
	exit;
} elseif($_POST['action'] == 'del'){
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');	
	}

	$db->update("DELETE FROM pw_toollog WHERE id IN($selid)");
	adminmsg('operate_success');
}
?>