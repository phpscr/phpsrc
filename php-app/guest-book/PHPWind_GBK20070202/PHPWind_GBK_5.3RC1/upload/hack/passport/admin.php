<?php
!function_exists('adminmsg') && exit('Forbidden');

if (!$action){
	ifcheck($passport_ifopen,'ifopen');
	${'type_'.$passport_type}='checked';
	if($passport_type=='server'){
		$style_server="";
		$style_client="display:none;";
	}else{
		$style_server="display:none;";
		$style_client="";
	}

	$credit=array(
		'rvrc'=>$db_rvrcname,
		'money'=>$db_moneyname,
		'credit'=>$db_creditname,
		'currency'=>$db_currencyname,
	);

	include PrintHack('admin');exit;
}else{
	$config['passport_credit'] = implode(',',$ppt_credit);
	foreach($config as $key => $value){
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='$key'",
			"UPDATE pw_config SET db_value='$value' WHERE db_name='$key'",
			"INSERT INTO pw_config(db_name,db_value) VALUES ('$key','$value')"
		);
	}
	updatecache_c();
	adminmsg("operate_success");
}
?>