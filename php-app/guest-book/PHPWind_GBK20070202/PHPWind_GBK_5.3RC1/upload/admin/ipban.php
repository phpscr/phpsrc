<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=ipban";
if ($action!="unsubmit"){
	$ipban=$db->get_one("SELECT db_value FROM pw_config WHERE db_name='db_ipban'");
	$baninfo=str_replace(",","\n",$ipban['db_value']);
	include PrintEot('ipban');exit;
} elseif($_POST['action']=="unsubmit") {
	$baninfo=str_replace("\r","",$baninfo);
	$baninfo=str_replace("\n",",",$baninfo);
	$rt=$db->get_one("SELECT * FROM pw_config WHERE db_name='db_ipban'");
	if($rt){
		$db->update("UPDATE pw_config SET db_value='$baninfo' WHERE db_name='db_ipban'");
	}else{
		$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_ipban','$baninfo')");
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>