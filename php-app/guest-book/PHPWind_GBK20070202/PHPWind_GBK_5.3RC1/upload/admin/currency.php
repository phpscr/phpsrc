<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=currency";
require_once(R_P."require/forum.php");

if(!$action){
	if (!is_numeric($page) || $page<1){
		$page = 1;
	}
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_memberdata WHERE currency!='0'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$currencydb=array();
	$query = $db->query("SELECT m.uid,m.username,md.currency FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE currency!='0' ORDER BY currency DESC $limit");
	while($rt = $db->fetch_array($query)){
		$currencydb[] = $rt;
	}
	include PrintEot('currency');exit;
} elseif($action == 'edit'){
	if(!$_POST['step']){
		if(is_numeric($uid)){
			$sqladd = "m.uid='$uid'";
		} else{
			$sqladd = "m.username='$username'";
		}
		$rt=$db->get_one("SELECT m.uid,m.username,md.currency FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE $sqladd");
		!$rt && adminmsg('user_not_exists');
		include PrintEot('currency');exit;
	} else{
		$db->update("UPDATE pw_memberdata SET currency='$currency' WHERE uid='$uid'");
		adminmsg('operate_success');
	}
}
?>