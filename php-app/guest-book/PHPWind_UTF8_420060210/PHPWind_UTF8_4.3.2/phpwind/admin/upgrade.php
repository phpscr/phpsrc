<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=upgrade";
if (empty($action)){
	$select[$db_upgrade]='selected';
	include PrintEot('upgrade');exit;
}else{
	if($upgrade!=1 && $upgrade!=2 && $upgrade!=3 && $upgrade!=4 && $upgrade!=5){
		$upgrade=1;
	}
	$rt=$db->get_one("SELECT * FROM pw_config WHERE db_name='db_upgrade'");
	if($rt){
		$db->update("UPDATE pw_config SET db_value='$upgrade' WHERE db_name='db_upgrade'");
	}else{
		$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_upgrade','$upgrade')");
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>