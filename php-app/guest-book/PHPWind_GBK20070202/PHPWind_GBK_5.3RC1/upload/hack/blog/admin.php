<?php
!function_exists('adminmsg') && exit('Forbidden');
include_once(D_P."data/bbscache/bg_config.php");

if (!$action){
	ifcheck($bg_ifopen,'ifopen');
	foreach($ltitle as $key=>$value){
		if($key != 1 && $key != 2){
			$checked = '';
			if(strpos($bg_groups,','.$key.',') !== false){
				$checked = 'checked';
			}
			$num++;
			$htm_tr = $num%4 == 0 ?  '</tr><tr>' : '';
			$usergroup .=" <td width='20%'><input type='checkbox' name='groups[]' value='$key' $checked>$value</td>$htm_tr";
		}
	}
	include PrintHack('admin');exit;
}else{
	if (is_array($groups)){
		$config['bg_groups'] = ','.implode(',',$groups).',';
	} else {
		$config['bg_groups'] = '';
	}
	foreach($config as $key => $value){
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name='$key'",
			"UPDATE pw_hack SET hk_value='$value' WHERE hk_name='$key'",
			"INSERT INTO pw_hack(hk_name,hk_value) VALUES ('$key','$value')"
		);
	}
	updatecache_bg();
	adminmsg("operate_success");
}

function updatecache_bg(){
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'bg_%'");
	$blogdb = "<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		$blogdb .= "\$$hk_name='$hk_value';\r\n";
	}
	$blogdb .= "\n?>";
	writeover(D_P.'data/bbscache/bg_config.php', $blogdb);
}
?>