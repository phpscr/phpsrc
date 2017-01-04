<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=banuser";
if (empty($action)){
	$select[$db_banby]='selected';
	$db_banlimit=(int)$db_banlimit;
	$db_autoban ? $autoban_Y='checked' : $autoban_N='checked';
	$db_bantype==2 ? $bantype_2='checked' : $bantype_1='checked';
	include PrintEot('banuser');exit;
}  elseif($_POST['action']=='banuser'){
	$userdb=$db->get_one("SELECT uid,groupid FROM pw_members WHERE username='$username'");
	if(!$userdb){
		$errorname=$username;
		adminmsg('user_not_exists');
	}
	if($userdb['groupid']=='-1'){
		if($type==1 && !$limit){
			adminmsg('ban_limit');
		}
		$db->update("UPDATE pw_members SET groupid='6' WHERE uid='$userdb[uid]'");
		$db->update("REPLACE INTO pw_banuser VALUES('$userdb[uid]','$type','$timestamp','".(int)$limit."','".addslashes($admin_name)."','')");
		adminmsg('operate_success');
	} elseif($userdb['groupid']=='6'){
		adminmsg('have_banned');
	} else{
		adminmsg('ban_error');
	}
} elseif($_POST['action']=='freeuser'){
	$userdb=$db->get_one("SELECT uid,groupid FROM pw_members WHERE username='$username'");
	if(!$userdb){
		$errorname=$username;
		adminmsg('user_not_exists');
	}
	if($userdb['groupid']=='6'){
		$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$userdb[uid]'");
		$db->update("DELETE FROM pw_banuser WHERE uid='$userdb[uid]'");
		adminmsg('operate_success');
	} else{
		adminmsg('not_banned');
	}
} elseif($_POST['action']=='autoban'){
	foreach($ban as $key=>$value){
		if(${'db_'.$key}!=$value){
			$rt=$db->get_one("SELECT * FROM pw_config WHERE db_name='db_$key'");
			if($rt){
				$db->update("UPDATE pw_config SET db_value='$value' WHERE db_name='db_$key'");
			}else{
				$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_$key','$value')");
			}
		}
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>