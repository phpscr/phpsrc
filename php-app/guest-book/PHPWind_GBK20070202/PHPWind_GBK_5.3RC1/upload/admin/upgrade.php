<?php
!function_exists('adminmsg') && exit('Forbidden');
include_once(D_P.'data/bbscache/creditdb.php');
$basename="$admin_file?adminjob=upgrade";

if (empty($_POST['action'])){
	$credit=$db->get_one("SELECT sum(postnum) as postnum,sum(digests) as digests,sum(rvrc)/10 as rvrc, sum(money) as money, sum(credit) as credit,sum(onlinetime)/3600 as onlinetime  FROM pw_memberdata");
	foreach($_CREDITDB as $key=>$val){
		$rt = $db->get_one("SELECT sum(value) as value FROM pw_membercredit WHERE cid='$key'");
		$credit[$key] = $rt['value'];
	}
	$rt=$db->get_one("SELECT totalmember FROM pw_bbsinfo WHERE id=1");
	foreach($credit as $key=>$val){
		$credit[$key]		= (int)$val;
		$creditrate[$key]	= number_format($val/$rt['totalmember'],2);
	}
	$db_upgrade = unserialize($db_upgrade);
	include PrintEot('upgrade');exit;
}else{
	foreach($upgrade as $key=>$val){
		if(is_numeric($val)){
			$upgrade[$key]=$val;
		}else{
			$upgrade[$key]=0;
		}
	}
	$upgrade=serialize($upgrade);
	$db->pw_update(
		"SELECT db_name FROM pw_config WHERE db_name='db_upgrade'",
		"UPDATE pw_config SET db_value='$upgrade' WHERE db_name='db_upgrade'",
		"INSERT INTO pw_config(db_name,db_value) VALUES ('db_upgrade','$upgrade')"
	);
	updatecache_c();
	adminmsg('operate_success');
}
?>