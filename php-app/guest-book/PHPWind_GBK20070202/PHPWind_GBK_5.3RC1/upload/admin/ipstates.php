<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=ipstates";

if ($action!='submit'){
	ifcheck($db_ipstates,'ipstates');
	include PrintEot('ipstates');
}elseif ($_POST['action']=="submit"){
	$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_ipstates'");
	if($rt['db_name']){
		$db->update("UPDATE pw_config SET db_value='$ipstates' WHERE db_name='db_ipstates'");
	}else{
		$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_ipstates','$ipstates')");
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>