<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=currencyset";

if(!$action){
	include_once(D_P."data/bbscache/creditdb.php");
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name LIKE 'cy\_%'");
	while($rt = $db->fetch_array($query)){
		$$rt['db_name'] = $rt['db_value'];
	}
	ifcheck($cy_virement,'virement');

	$query = $db->query("SELECT hk_name,hk_value FROM pw_hack WHERE hk_name IN('currrate1','currrate2')");
	while($rt = $db->fetch_array($query)){
		$$rt['hk_name'] = unserialize($rt['hk_value']);
	}
	if(is_array($currrate1)){
		foreach($currrate1 as $key => $value){
			$key == 'rvrc' && $value /= 10;
			$currrate1[$key] = (int)$value;
		}
	}
	if(is_array($currrate2)){
		foreach($currrate2 as $key => $value){
			$key == 'rvrc' && $value /= 10;
			$currrate2[$key] = (int)$value;
		}
	}
	include PrintEot('currencyset');exit;
} elseif($action == 'currrate'){
	foreach($currrate1 as $key => $value){
		$key == 'rvrc' && $value *= 10;
		$currrate1[$key] = (int)$value;
	}
	foreach($currrate2 as $key => $value){
		$key == 'rvrc' && $value *= 10;
		if($currrate1[$key] && $value && $value<$currrate1[$key]){
			adminmsg('currrate_error');
		}
		$currrate2[$key] = (int)$value;
	}
	if (array_sum($currrate1) == 0){
		$currrate1 = '';
	} else {
		$currrate1 = serialize($currrate1);
	}
	if (array_sum($currrate2) == 0){
		$currrate2 = '';
	} else {
		$currrate2 = serialize($currrate2);
	}
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='currrate1'",
		"UPDATE pw_hack SET hk_value='$currrate1' WHERE hk_name='currrate1'",
		"INSERT INTO pw_hack SET hk_value='$currrate1',hk_name='currrate1'"
	);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='currrate2'",
		"UPDATE pw_hack SET hk_value='$currrate2' WHERE hk_name='currrate2'",
		"INSERT INTO pw_hack SET hk_value='$currrate2',hk_name='currrate2'"
	);
	adminmsg('operate_success');
} elseif($action == 'virement'){

	foreach($config as $key=>$value){
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='$key'",
			"UPDATE pw_config SET db_value='$value' WHERE db_name='$key'",
			"INSERT INTO pw_config SET db_name='$key',db_value='$value'"
		);
	}
	adminmsg('operate_success');
}
?>