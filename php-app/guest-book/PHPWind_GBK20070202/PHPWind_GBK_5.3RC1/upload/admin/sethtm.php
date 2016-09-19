<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=sethtm";
if (!$_POST['step']) {
	ifcheck($db_htmifopen,'htmifopen');
	!$db_dir && $db_dir='.php?';
	!$db_ext && $db_ext='.html';
	include PrintEot('sethtm');exit;
}elseif ($_POST['step']==2){
	foreach($config as $key=>$value){
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_$key'",
			"UPDATE pw_config SET db_value='$value' WHERE db_name='db_$key'",
			"INSERT INTO pw_config(db_name,db_value) VALUES ('db_$key','$value')"
		);
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>