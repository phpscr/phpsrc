<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=customcredit";

require_once(R_P."require/forum.php");
require_once(R_P."require/credit.php");
include_once(D_P."data/bbscache/creditdb.php");

if(!$action){
	if (!is_numeric($page) || $page<1){
		$page = 1;
	}
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_membercredit WHERE value!=0");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$query = $db->query("SELECT m.uid,m.username,mc.cid,mc.value FROM pw_membercredit mc LEFT JOIN pw_members m USING(uid) WHERE value!=0 ORDER BY cid, value DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['name'] = $_CREDITDB[$rt['cid']][0];
		$creditdb[] = $rt;
	}
	include PrintEot('customcredit');exit;
} elseif($action == 'edit'){
	if(!$_POST['step']){
		if(is_numeric($uid)){
			$sqladd = "uid='$uid'";
		} else{
			$sqladd = "username='$username'";
		}
		$rt = $db->get_one("SELECT uid,username FROM pw_members WHERE $sqladd");
		!$rt && adminmsg('user_not_exists');
		$credit = GetCredit($rt['uid']);
		include PrintEot('customcredit');exit;
	} else{
		!is_numeric($uid) && adminmsg('operate_error');
		foreach($creditdb as $key => $value){
			if(is_numeric($key) && is_numeric($value)){
				$db->pw_update(
					"SELECT uid FROM pw_membercredit WHERE uid='$uid' AND cid='$key'",
					"UPDATE pw_membercredit SET value='$value' WHERE uid='$uid' AND cid='$key'",
					"INSERT INTO pw_membercredit SET uid='$uid',cid='$key',value='$value'"
				);
			}
		}
		adminmsg('operate_success');
	}
}
?>