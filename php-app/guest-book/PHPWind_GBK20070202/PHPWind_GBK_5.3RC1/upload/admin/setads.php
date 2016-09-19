<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setads";
if ($action!='submit'){
	${'ads_'.$db_ads}='checked';
	include PrintEot('setads');
}elseif ($_POST['action']=="submit"){
	$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_ads'");
	if($rt['db_name']){
		$db->update("UPDATE pw_config SET db_value='$ads' WHERE db_name='db_ads'");
	}else{
		$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_ads','$ads')");
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>